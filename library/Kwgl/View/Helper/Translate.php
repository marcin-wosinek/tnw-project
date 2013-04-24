<?php
/**
 * View Helper for Translating Keywords from View Scripts
 * Uses the Common Translation adapter
 *
 * @author Darshan Wijekoon <darshanchathuranga@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_View
 * @subpackage Helper
 */
class Kwgl_View_Helper_Translate extends Zend_View_Helper_Abstract {

	public function translate($sString) {

		if (strlen($sString) > 0) {
			$oTranslationAdapter = Zend_Registry::get('Zend_Translate');
			return $oTranslationAdapter->_($sString);
		}
	}

}