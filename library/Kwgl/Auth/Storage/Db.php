<?php
/**
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Auth
 * @subpackage Storage
 */
class Kwgl_Auth_Storage_Db implements Zend_Auth_Storage_Interface {

	/**
	 * Store the Authentication Configuration
	 *
	 * @var array
	 */
	private static $aAuthConfig = array();

	/**
	 * Holds the Session Table DAO Object
	 *
	 * @var Kwgl_Db_Table
	 */
	protected static $_oDaoAuthStorage = null;

	/**
	 * Name of DAO Class that Stores the Sessions
	 *
	 * @var string
	 */
	protected static $_sSessionTableClass = null;

	/**
	 * Primary Column Name in the Table storing the Sessions
	 *
	 * @var string
	 */
	protected static $_sSessionTablePrimary = null;

	/**
	 * Reference Column Name in the Table storing the Session that links to the Table storing the Accounts
	 *
	 * @var string
	 */
	protected static $_sSessionTableAccountReferenceColumn = null;

	/**
	 * Name of DAO Class that Stores the Accounts
	 *
	 * @var string
	 */
	protected static $_sAccountTableClass = null;

	/**
	 * Primary Column Name in the Table storing the Accounts
	 *
	 * @var string
	 */
	protected static $_sAccountTablePrimary = null;

	/**
	 * Column Name in the Table strong the Accounts to be used to compare against the Identifier given by Authenticate
	 *
	 * @var string
	 */
	protected static $_sAccountTableIdentifier = null;

	/**
	 * Current Session ID
	 *
	 * @var string
	 */
	protected static $_sSessionID = null;

	/**
	 * Time Duration to Expire the Session after
	 *
	 * @var integer
	 */
	protected static $_iExpiryDuration = null;

	/**
	 * The Starting point used to check if the Session should be Expired
	 *
	 * @var string
	 */
	protected static $_sExpiryMode = 'access';


	/**
	 * Initialisation of Kwgl_AuthStorage
	 *
	 * @return void;
	 */
	public function __construct () {

		$this->initDaoAuthStorage();

		if (!Zend_Session::isStarted()) {
			Zend_Session::start();
		}
		self::$_sSessionID = Zend_Session::getId();

		// Expire the session if necessary. Only carried out once.
		$this->_checkExpiration();

	}

	/**
	 * Returns true if and only if the Stored Session in the Database is empty
	 *
	 * @throws Zend_Auth_Storage_Exception If it is impossible to determine whether storage is empty
	 * @return boolean
	 */
	public function isEmpty() {

		$mWhereClause = null;

		if (isset(self::$_sSessionID)) {
			$mWhereClause[self::$_sSessionTablePrimary . ' = ?'] = self::$_sSessionID;
		} else {
			return true;
		}

		if (!is_null($mWhereClause)) {
			try {
				$aSessionListing = $this->_oDaoAuthStorage->fetchAll($mWhereClause);
				$iCount = $aSessionListing->count();

				if ($iCount == 0) {
					// Session not found
					return true;
				} elseif ($iCount == 1) {
					// Session found
					return false;
				} else {
					// Multiple Sessions found, clear the Session Data
					$this->clear();
					return true;
				}
			} catch (Exception $oException) {
				throw new Zend_Auth_Storage_Exception();
			}
		} else {
			throw new Zend_Auth_Storage_Exception();
		}
	}

	/**
     * Returns the Stored Session Contents from the Database. Behavior is undefined when storage is empty.
     *
     * @throws Zend_Auth_Storage_Exception If reading contents from storage is impossible
     * @return array|mixed
     */
	public function read() {

		$mWhereClause[self::$_sSessionTablePrimary . ' = ?'] = self::$_sSessionID;

		try {
			$aData = array();
			$aData['timestamp_accessed'] = time();
			$iRowsAffected = $this->_oDaoAuthStorage->update($aData, $mWhereClause);

			$aSessionDetail = $this->_oDaoAuthStorage->fetchRow($mWhereClause);
			return $aSessionDetail;
		} catch (Exception $oException) {
			throw new Zend_Auth_Storage_Exception();
		}

	}

	/**
     * Writes provided Data to the Database Session Storage.
     *
     * @param  mixed $contents
     * @throws Zend_Auth_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
	public function write($mContent) {
		try {
			if ($mContent instanceof stdClass) {
				$mContent = (array)$mContent;
			}

			if (is_array($mContent)) {
				if (isset($mContent[self::$_sAccountTableIdentifier])) {
					$mContent = $mContent[self::$_sAccountTableIdentifier];
				} else {
					throw new Zend_Auth_Storage_Exception();
				}
			}

			if (isset(self::$_sAccountTableClass)) {
				$sAccountTableClass = self::$_sAccountTableClass;
			} else {
				$sAccountTableClass = 'System_Account';
			}
			$oDaoAccount = Kwgl_Db_Table::factory($sAccountTableClass);

			if (isset(self::$_sAccountTableIdentifier)) {
				$mWhereClause[self::$_sAccountTableIdentifier . ' = ?'] = $mContent;
			} else {
				$mWhereClause['username = ?'] = $mContent;
			}
			$aAccountDetail = $oDaoAccount->fetchRow($mWhereClause);

			if (empty($aAccountDetail)) {
				throw new Zend_Auth_Storage_Exception();
			}

			if ($this->isEmpty()) {
				// Regenerate Session ID
				Zend_Session::regenerateId();
				self::$_sSessionID = Zend_Session::getId();

				// Delete any Rows containing the same Session ID or Account ID
				$this->clear();
				$this->_oDaoAuthStorage->delete(array(self::$_sSessionTableAccountReferenceColumn . ' = ?' => $aAccountDetail[self::$_sAccountTablePrimary]));
				// Insert if Empty
				$aData = array();
				$aData[self::$_sSessionTablePrimary] = self::$_sSessionID;
				$aData[self::$_sSessionTableAccountReferenceColumn] = $aAccountDetail[self::$_sAccountTablePrimary];
				$aData['hostname'] = Kwgl_Utility_Ip::getIp();
				$aData['user_agent_hash'] = md5($_SERVER['HTTP_USER_AGENT']);
				$aData['timestamp_created'] = time();
				$aData['timestamp_accessed'] = time();
				$iInsertID = $this->_oDaoAuthStorage->insert($aData);
			} else {
				// Else Update
				$aData = array();
				$aData['timestamp_accessed'] = time();
				$iRowsAffected = $this->_oDaoAuthStorage->update($aData, array(self::$_sSessionTablePrimary . ' = ?' => self::$_sSessionID));
			}

		} catch (Exception $oException) {
			throw new Zend_Auth_Storage_Exception();
		}
	}

	/**
     * Clears the Data from the Database Session Storage.
     *
     * @throws Zend_Auth_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
	public function clear() {
		$mWhereClause = null;
		$mWhereClause[self::$_sSessionTablePrimary . ' = ?'] = self::$_sSessionID;

		try {
			try {
				$oResult = $this->_oDaoAuthStorage->delete($mWhereClause);
			} catch (Exception $oException) {
				Kwgl_Db::logException($oException);
				return null;
			}
		} catch (Exception $oException) {
			throw new Zend_Auth_Storage_Exception();
		}
	}

	/**
	 * Check if settings indicate if the Session should be expired after a set duration. And if so, removes the Data
	 * from the Database Session Storage.
	 *
	 * @return void
	 */
	private function _checkExpiration () {

		if (self::$_iExpiryDuration == 0) {
			// Session is not to be expired
			return;
		}

		$mWhereClause[self::$_sSessionTablePrimary . ' = ?'] = self::$_sSessionID;

		$iTimestamp = time() - self::$_iExpiryDuration;
		switch (self::$_sExpiryMode) {
			case 'start':
				$sExpiryCheckColumn = 'timestamp_created';
				break;
			case 'access':
			default:
				$sExpiryCheckColumn = 'timestamp_accessed';
				break;
		}
		$mWhereClause[$sExpiryCheckColumn . ' <= ?'] = $iTimestamp;

		try {
			try {
				$oResult = $this->_oDaoAuthStorage->delete($mWhereClause);
			} catch (Exception $oException) {
				Kwgl_Db::logException($oException);
				return null;
			}
		} catch (Exception $oException) {
			throw new Zend_Auth_Storage_Exception();
		}
		return;

	}

	/**
	 * Sets Authentication Configuration from the ini.
	 * Should be called from the Bootstrap.
	 *
	 * @param array|object|string $mConfig
	 * @throws Exception
	 */
	public static function setAuthConfig ($mConfig) {

		if ($mConfig instanceof Zend_Config_Ini) {
			self::$aAuthConfig = $mConfig->toArray();
		} elseif (is_array($mConfig)) {
			self::$aAuthConfig = $mConfig;
		} elseif(is_string($mConfig)) {
			// Try to Load the File
			$sSuffix = strtolower(pathinfo($mConfig, PATHINFO_EXTENSION));
			// Get Object based on File Extension
			switch ($sSuffix) {
				case 'ini':
					$oConfig = new Zend_Config_Ini($mConfig);
					break;
				case 'xml':
					$oConfig = new Zend_Config_Xml($mConfig);
					break;
				case 'json':
					$oConfig = new Zend_Config_Json($mConfig);
					break;
				case 'yaml':
					$oConfig = new Zend_Config_Yaml($mConfig);
					break;
				case 'php':
				case 'inc':
					$oConfig = include $mConfig;
					if (!is_array($oConfig)) {
						throw new Exception('Invalid configuration file provided; PHP file does not return array value');
					}
					return $oConfig;
					break;
				default:
					throw new Exception('Invalid configuration file provided; unknown config type');
			}
			// Save as an array
			self::$aAuthConfig = $oConfig->toArray();
		} else{
			throw new Exception('Invalid database configuration provided');
		}

		self::$_sSessionTableClass = self::$aAuthConfig['session']['table']['class'];
		self::$_sSessionTablePrimary = self::$aAuthConfig['session']['table']['primary'];
		self::$_sSessionTableAccountReferenceColumn = self::$aAuthConfig['session']['table']['reference']['account'];
		self::$_sAccountTableClass = self::$aAuthConfig['account']['table']['class'];
		self::$_sAccountTablePrimary = self::$aAuthConfig['account']['table']['primary'];
		self::$_sAccountTableIdentifier = self::$aAuthConfig['account']['identifier'];
		self::$_iExpiryDuration = abs(self::$aAuthConfig['session']['expiry']['duration']);
		self::$_sExpiryMode = self::$aAuthConfig['session']['expiry']['mode'];

	}

	/**
	 * Initialises the Dao Class to be used to Store the Session
	 */
	private function initDaoAuthStorage () {

		if (isset(self::$aAuthConfig['session']['table']['class'])) {
			$sSessionTableClass = self::$aAuthConfig['session']['table']['class'];
		} else {
			$sSessionTableClass = 'System_Session';
		}
		$this->_oDaoAuthStorage = Kwgl_Db_Table::factory($sSessionTableClass);

	}

}