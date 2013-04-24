<?php
/**
 * Writes Log entries into XML files
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_LogXml
 */
class Kwgl_LogXml {

	/**
	 *
	 */
	const TYPE_APP = 'app';
	const TYPE_DB = 'db';

	/**
	 *
	 * @var string
	 */
	protected $_sType = '';

	/**
	 *
	 * @var string
	 */
	protected $_sFilePath = '';

	/**
	 *
	 * @var object
	 */
	protected $_oXml = null;

	/**
	 *
	 * @param string $sFilePath
	 * @param string $sType
	 */
	public function __construct ($sFilePath, $sType = self::TYPE_APP) {

		if (is_null($sFilePath)) {
			throw new Exception("Path of File to be for Logging Errors has not been provided.");
		}
		$this->_sFilePath = $sFilePath;

		if (is_null($sType)) {
			throw new Exception("Type of Error Logging to be done has not been provided.");
		}
		$this->_sType = $sType;

		// Check for file
		if (file_exists($this->_sFilePath)) {
			if (filesize($this->_sFilePath) == 0) {
				// File is empty, make it an well-formed XML Document
				$this->_create();
			} else {
				// Do nothing
			}

		} else {
			// File does not exist, create it and make it an well-formed XML Document
			$this->_create();
		}

		// Load the File
		$oXml = new DOMDocument('1.0', 'utf-8');
		$mStatus = $oXml->load($this->_sFilePath);
		if ($mStatus === FALSE) {
			throw new Exception("Error Loading File referenced for Logging Errors.");
		}
		$this->_oXml = $oXml;
	}

	/**
	 *
	 */
	protected function _create () {
		$oXml = new DOMDocument('1.0', 'utf-8');
		switch ($this->_sType) {
			case self::TYPE_APP:
				$oPi = $oXml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="../../../style/Application.xsl"');
				$oXml->appendChild($oPi);
				break;
			case self::TYPE_DB:
				$oPi = $oXml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="../../../style/Database.xsl"');
				$oXml->appendChild($oPi);
				break;
		}

		$oRoot = $oXml->createElement('errors');
		$oXml->appendChild($oRoot);

		$oDate = $oXml->createAttribute('date');
		$oRoot->appendChild($oDate);
		$sTemporaryText = $oXml->createTextNode(date('Y-m-d'));
		$oDate->appendChild($sTemporaryText);

		$oXml->save($this->_sFilePath);
	}

	/**
	 *
	 * @param array $aContent
	 */
	public function write ($aContent) {
		switch ($this->_sType) {
			case self::TYPE_APP :
				$this->_writeForApp($aContent);
				break;
			case self::TYPE_DB :
				$this->_writeForDb($aContent);
				break;
		}
	}

	/**
	 *
	 * @param array $aContent
	 */
	protected function _writeForApp ($aContent) {
		$oXPath = new DOMXPath($this->_oXml);
		$oResults = $oXPath->query('/errors');
		if (!$oResults) {
			throw new Exception("Error Using File referenced for Logging Errors.");
		}

		$oRoot = $oResults->item(0);
		$oError = $this->_oXml->createElement('error');
		$oRoot->appendChild($oError);

		$oTime = $this->_oXml->createAttribute('time');
		$oError->appendChild($oTime);
		$sTemporaryText = $this->_oXml->createTextNode($aContent['time']);
		$oTime->appendChild($sTemporaryText);

		$oMessage = $this->_oXml->createElement('message');
		$oError->appendChild($oMessage);
		$oTemporaryText = $this->_oXml->createCDATASection($aContent['message']);
		$oMessage->appendChild($oTemporaryText);

		$oUrl = $this->_oXml->createElement('url');
		$oError->appendChild($oUrl);
		$oTemporaryText = $this->_oXml->createCDATASection($aContent['url']);
		$oUrl->appendChild($oTemporaryText);

		if (isset($aContent['reference'])) {
			$oReference = $this->_oXml->createElement('reference');
			$oError->appendChild($oReference);
			$oTemporaryText = $this->_oXml->createCDATASection($aContent['reference']);
			$oReference->appendChild($oTemporaryText);
		}

		if (isset($aContent['trace'])) {
			$oTrace = $this->_oXml->createElement('trace');
			$oError->appendChild($oTrace);
			$sTrace = $aContent['trace'];
			$aTrace = explode('#', $sTrace);
			foreach ($aTrace as $sLine) {
				$sLine = trim($sLine);
				if ($sLine != '') {
					$oLine = $this->_oXml->createElement('line');
					$oTrace->appendChild($oLine);
					$oTemporaryText = $this->_oXml->createCDATASection('#' . $sLine);
					$oLine->appendChild($oTemporaryText);
				}
			}
		}

		$this->_oXml->save($this->_sFilePath);
	}

	/**
	 *
	 * @param array $aContent
	 */
	protected function _writeForDb ($aContent) {

		$oXPath = new DOMXPath($this->_oXml);
		$oResults = $oXPath->query('/errors');
		if (!$oResults) {
			throw new Exception("Error Using File referenced for Logging Errors.");
		}

		$oRoot = $oResults->item(0);
		$oError = $this->_oXml->createElement('error');
		$oRoot->appendChild($oError);

		$oTime = $this->_oXml->createAttribute('time');
		$oError->appendChild($oTime);
		$sTemporaryText = $this->_oXml->createTextNode($aContent['time']);
		$oTime->appendChild($sTemporaryText);

		$oMessage = $this->_oXml->createElement('message');
		$oError->appendChild($oMessage);
		$oTemporaryText = $this->_oXml->createCDATASection($aContent['message']);
		$oMessage->appendChild($oTemporaryText);

		$oReference = $this->_oXml->createElement('reference');
		$oError->appendChild($oReference);
		$oTemporaryText = $this->_oXml->createCDATASection($aContent['reference']);
		$oReference->appendChild($oTemporaryText);

		$oUrl = $this->_oXml->createElement('url');
		$oError->appendChild($oUrl);
		$oTemporaryText = $this->_oXml->createCDATASection($aContent['url']);
		$oUrl->appendChild($oTemporaryText);

		$oTrace = $this->_oXml->createElement('trace');
		$oError->appendChild($oTrace);
		$sTrace = $aContent['trace'];
		$aTrace = explode('#', $sTrace);
		foreach ($aTrace as $sLine) {
			if ($sLine != '') {
				$oLine = $this->_oXml->createElement('line');
				$oTrace->appendChild($oLine);
				$oTemporaryText = $this->_oXml->createCDATASection('#' . $sLine);
				$oLine->appendChild($oTemporaryText);
			}
		}

		$oSql = $this->_oXml->createElement('sql');
		$oError->appendChild($oSql);
		$oTemporaryText = $this->_oXml->createCDATASection($aContent['sql']);
		$oSql->appendChild($oTemporaryText);

		$this->_oXml->save($this->_sFilePath);
	}

}