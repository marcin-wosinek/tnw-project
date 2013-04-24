<?php
/**
 * Wrapper for Exceptions
 * Also retrieves information helpful for debugging such as SQL, IP, Browser, etc
 * Additionally, sends out notification mails about the Exception based on the configured receipients
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Exception
 * @uses Kwgl_Config Used to retrieve configuration information
 * @uses Kwgl_LogXml Used to write the Error Logs (xml)
 * @uses Kwgl_Mail Used to send out mail
 */
class Kwgl_Exception extends Exception {

	/**
	 * SQL Query that was being executed when the exception occurred
	 * @var string
	 */
	protected $_sSql = '';

	/**
	 * Relative URL Path that was being accessed whtn the exception occurred
	 * @var string
	 */
	protected $_sUrl = '';

	/**
	 * IP from which the request orignated when the exception occurred
	 * @var string
	 */
	protected $_sIp = '';

	/**
	 * Browser (if any) that was used to send the request which caused the exception
	 * @var string
	 */
	protected $_sUserAgent = '';

	/**
	 *
	 * @param string $sMessage
	 * @param integer $iCode
	 * @param Exception $oPrevious
	 */
	public function __construct ($sMessage, $iCode, Exception $oPrevious) {
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($sMessage, (int)$iCode);
            $this->_previous = $oPrevious;
        } else {
            parent::__construct($sMessage, (int)$iCode, $oPrevious);
        }

		$this->_checkForSql($oPrevious);
		$this->_checkUrl();
		$this->_checkIp();
		$this->_checkUserAgent();

		$sTrace = $this->getTraceAsString();

		// Check if Debug mode is Active
		$bDebug = (bool)Kwgl_Config::get(array('mode', 'debug', 'active'));
		if ($bDebug) {
			// Error/Exception should be shown

			// Do not need to do anything, the Error Controller will handle the display
		} else {
			// Error/Exception should be logged

			// Determine nature of the Exception based on whether or not SQL is involved
			if ($this->getSql() == '') {
				// If SQL is not involved, it is an Application Exception

				// Get Base Application Log File
				$sLogFileForApplication = Kwgl_Config::get(array('logs', 'error', 'app'));
				$sLogFileForApplicationPath = Kwgl_Utility_LogFile::getLogFilePath($sLogFileForApplication);

				$oXmlLog = new Kwgl_LogXml($sLogFileForApplicationPath, Kwgl_LogXml::TYPE_APP);
				$aLogContent = array(
					'message' => $this->getMessage(),
					'url' => $this->getUrl(),
					'time' => date('H:i:s (T - e) [\G\M\T O]'),
					'trace' => $this->getPreviousTraceAsString(),
				);
				$oXmlLog->write($aLogContent);

			} else {
				// If SQL is involved, it is a Database Exception

				// Used as a Reference Key
				$sTimeHash = md5(time());

				// Get Base Database Log File
				$sLogFileForDatabase = Kwgl_Config::get(array('logs', 'error', 'db'));
				$sLogFileForDatabasePath = Kwgl_Utility_LogFile::getLogFilePath($sLogFileForDatabase);

				$oXmlLog = new Kwgl_LogXml($sLogFileForDatabasePath, Kwgl_LogXml::TYPE_DB);
				$aLogContent = array(
					'message' => $this->getMessage(),
					'reference' => $sTimeHash,
					'time' => date('H:i:s (T - e) [\G\M\T O]'),
					'url' => $this->getUrl(),
					'trace' => $this->getPreviousTraceAsString(),
					'sql' => $this->getSql(),
				);
				$oXmlLog->write($aLogContent);

				// Get Base Application Log File
				$sLogFileForApplication = Kwgl_Config::get(array('logs', 'error', 'app'));
				$sLogFileForApplicationPath = Kwgl_Utility_LogFile::getLogFilePath($sLogFileForApplication);

				$oXmlLog = new Kwgl_LogXml($sLogFileForApplicationPath, Kwgl_LogXml::TYPE_APP);
				$aLogContent = array(
					'message' => 'A database error has occurred. Details have been logged in the Database Error Log.',
					'url' => $this->getUrl(),
					'time' => date('H:i:s (T - e) [\G\M\T O]'),
					'reference' => $sTimeHash,
				);
				$oXmlLog->write($aLogContent);

			}

			// Check if Mail Notifications are set to be sent
			$bEmailNotification = (bool)Kwgl_Config::get(array('mode', 'debug', 'mail', 'notification'));
			if ($bEmailNotification) {
				// Sent Notification Mails
				self::sendNotificationMail($this);
			}
		}
	}

	/**
	 * Returns the SQL Statement extracted from the Exception
	 *
	 * @return string
	 */
	public function getSql () {
		return $this->_sSql;
	}

	/**
	 * Sets the SQL Statement extracted from the Exception
	 *
	 * @param string $sSql
	 */
	protected function _setSql ($sSql) {
		$this->_sSql = $sSql;
	}

	/**
	 * Tries to extract an SQL Statement from the Exception (if the Exception is Database related)
	 *
	 * @param Exception $oException
	 */
	protected function _checkForSql (Exception $oException) {
		$sSql = '';

		// Iterate through the Exception Trace
		foreach ($oException->getTrace() as $aDetail) {
			// If SQL hasn't been found yet check for classes which may contain SQL
			if (empty($sSql)) {
				if (isset($aDetail['class'])) {
					$sClass = $aDetail['class'];
					switch ($sClass) {
						case 'Zend_Db_Statement':
						case 'Zend_Db_Statement_Mysqli':
						case 'Zend_Db_Select':
						case 'Zend_Db_Table_Abstract':
						case 'Zend_Db_Table_Select':
						case 'Zend_Db_Adapter_Abstract':
						case 'Zend_Db_Adapter_Mysqli':
						case 'Zend_Db_Adapter_Pdo_Abstract':
							if (!empty($aDetail['args'][0])) {
								$sSql .= (string)$aDetail['args'][0];
							}
							break;
					}
				}
			}
		}

		$this->_setSql($sSql);
	}

	/**
	 * Returns the URL the Exception occurred at
	 * @return string
	 */
	public function getUrl () {
		return $this->_sUrl;
	}

	/**
	 * Sets the URL the Exception occurred at
	 * @param string $sUrl
	 */
	protected function _setUrl ($sUrl) {
		$this->_sUrl = $sUrl;
	}

	/**
	 * Extracts the URL the Exception occurred at
	 */
	protected function _checkUrl () {
		$sUrl = $_SERVER['REQUEST_URI'];
		$this->_setUrl($sUrl);
	}

	/**
	 * Returns the IP of the User who issued the Request
	 * @return string
	 */
	public function getIp () {
		return $this->_sIp;
	}

	/**
	 * Sets the IP of the User who issued the Request
	 * @param string $sIp
	 */
	protected function _setIp ($sIp) {
		$this->_sIp = $sIp;
	}

	/**
	 * Extracts the IP of the User who issued the Request
	 */
	protected function _checkIp () {
		$this->_setIp(Kwgl_Utility_Ip::getIp());
	}

	/**
	 * Returns the Browser of the User who issued the Request
	 *
	 * @return string
	 */
	public function getUserAgent () {
		return $this->_sUserAgent;
	}

	/**
	 * Sets the Browser of the User who issued the Request
	 *
	 * @param string $sUserAgent
	 */
	protected function _setUserAgent ($sUserAgent) {
		$this->_sUserAgent = $sUserAgent;
	}

	/**
	 * Extracts the Browser of the User who issued the Request
	 */
	protected function _checkUserAgent () {
		$this->_setUserAgent($_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * Returns the Trace as a String of the Previous Exception in the chain
	 * @return string
	 */
	public function getPreviousTraceAsString () {
		return $this->getPrevious()->getTraceAsString();
	}

	/**
	 * Sends out a notification mail to configured set of email addresses notifying them of the Exception
	 *
	 * @param Kwgl_Exception $oException
	 * @return boolean
	 */
	public static function sendNotificationMail (Kwgl_Exception $oException) {
		// Retrieve Mailing List for Notifications
		$aMailingList = Kwgl_Config::get(array('mode', 'debug', 'mail', 'list'));

		// If Mailing List is empty, return
		if (empty($aMailingList)) {
			return false;
		}

		// Prepare Data
		$sCode = $oException->getCode();
		$sMessage = $oException->getMessage();
		$sTrace = $oException->getPreviousTraceAsString();

		$sSql = $oException->getSql();
		$sUrl = $oException->getUrl();
		$sIp = $oException->getIp();
		$sUserAgent = $oException->getUserAgent();

		$sDomain = SITE_DOMAIN;
		$sSiteName = Kwgl_Config::get(array('appnamespace'));
		$sSenderEmail = Kwgl_Config::get(array('mail', 'sender', 'address'));
		$sSenderEmail = Kwgl_Config::get(array('mail', 'sender', 'address'));
		$sSenderName = Kwgl_Config::get(array('mail', 'sender', 'name'));

		if ($sSql == '') {
			$sSql = 'N/A';
		}

		$sLink = SITE_DOMAIN . '/logs/' . date('Y') . '/' . date('m') . '/' . date('d') . '/ApplicationErrors.xml';

		// Set the Template for the Email
		// Note: These should not be stored in the Database as we will be unable to send mails when the Database is down
		$sTemplateSubject = "Errors have occurred at ::site-name::.";
		$sTemplateBody = "
			Errors have occurred at ::site-name::.<br>
			<table>
				<tr>
					<td><b>Domain</b></td><td>::domain::</td>
				</tr>
				<tr>
					<td><b>URL</b></td><td>::url::</td>
				</tr>
				<tr>
					<td><b>IP</b></td><td>::ip::</td>
				</tr>
				<tr>
					<td><b>Browser</b></td><td>::user-agent::</td>
				</tr>
			</table>
			<br>
			<table>
				<tr>
					<td><b>Following is the current error</b></td>
				</tr>
				<tr>
					<td><pre>::message::</pre></td>
				</tr>
				<tr>
					<td><b>Trace</b></td>
				</tr>
				<tr>
					<td><pre>::trace::</pre></td>
				</tr>
				<tr>
					<td><b>SQL (if any)</b></td>
				</tr>
				<tr>
					<td><pre>::sql::</pre></td>
				</tr>
			</table>
			<br>
			Click <a href='::link::'>here</a> to view the log.
			<br>
			There maybe further errors. Kindly check the Logs for troubleshooting these issues.<br>
			Thank you.
			";

		$aFillData = array(
			'domain' => $sDomain,
			'url' => $sUrl,
			'ip' => $sIp,
			'user-agent' => $sUserAgent,
			'site-name' => $sSiteName,
			'code' => $sCode,
			'message' => $sMessage,
			'trace' => $sTrace,
			'sql' => $sSql,
			'link' => $sLink,
		);

		// Prepare the Mail
		$oMail = new Kwgl_Mail();
		$oMail->setTemplateSubject($sTemplateSubject);
		$oMail->setTemplateBody($sTemplateBody);
		$oMail->fillTemplate($aFillData);
		$oMail->setFrom($sSenderEmail, $sSenderName);

		foreach ($aMailingList as $sReceipient => $sEmail) {
			$oMail->addTo($sEmail, $sReceipient);
		}

		// Send the Mail
		try {
			$oMail->send();
			return true;
		} catch (Exception $oException) {
			// Could not send out mail
			return false;
		}
	}

}