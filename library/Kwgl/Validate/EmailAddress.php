<?php

/**
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Validate
 */
class Kwgl_Validate_EmailAddress extends Zend_Validate_EmailAddress {

	public function getMessages() {

		// Fetch the Actual Error Messages triggered by Zend_Validate_EmailAddress
		$aMessages = parent::getMessages();

		// Change the Error Message to a custom one
		$aCustomMessages = array();
		foreach ($aMessages as $sKey => $sMessage) {
			// Do not return multiple error messages
			if (!empty($aCustomMessages)) {
				break;
			}

			$aCustomMessages[$sKey] = "The Email Address provided is not valid.";
		}

		return $aCustomMessages;

	}

}