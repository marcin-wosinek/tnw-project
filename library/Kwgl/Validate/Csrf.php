<?php

/**
 * Validates CSRF Tokens
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Validate
 */
class Kwgl_Validate_Csrf extends Zend_Validate_Identical {

	protected $_oCsrfElement = null;

	/**
     * Sets validator options
     *
     * @param  mixed $token
     * @return void
     */
	public function __construct($oCsrfElement, $sToken = null) {
		$this->_oCsrfElement = $oCsrfElement;

		parent::__construct($sToken);

	}

	/**
	 *
	 * @param type $value
	 * @param type $context
	 * @return type
	 */
	public function isValid($value, $context = null) {
		$bValid = parent::isValid($value, $context);

		// Destroy the CSRF Session
		$this->_oCsrfElement->expireCsrfSession();

		return $bValid;
	}

	/**
	 *
	 * @return string
	 */
	public function getMessages() {

		// Fetch the Actual Error Messages triggered by Zend_Validate_Identical
		$aMessages = parent::getMessages();

		// Change the Error Message to a custom one
		$aCustomMessages = array();
		foreach ($aMessages as $sKey => $sMessage) {
			// Do not return multiple error messages
			if (!empty($aCustomMessages)) {
				break;
			}

			$aCustomMessages[$sKey] = "Your request has expired.";
		}

		return $aCustomMessages;

	}
}