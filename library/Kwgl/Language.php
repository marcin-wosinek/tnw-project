<?php
/**
 * Handles locale & translation functionalities
 *
 * @author Darshan <>
 * @category PHP-Kwgl
 * @package Kwgl_Language
 */
class Kwgl_Language {

	/**
	 * The default locale for this application
	 */
	const DEFAULT_LOCALE = 'nl_NL';

	/**
	 * Application-wide locale & translation adapters
	 * @var object
	 */
	public $oTranslate;
	public $oLocale;

	public function __construct() {

		$this->oTranslate = Zend_Registry::get('Zend_Translate');
		$this->oLocale = Zend_Registry::get('Zend_Locale');
	}

	/**
	 * Uses Zend_Translate adapter to translate a given keyword string
	 * @param string $sKeyword
	 * @return string
	 */
	public static function translate($sKeyword) {

		$oTranslator = Zend_Registry::get('Zend_Translate');

		if (strlen($sKeyword) > 0) {
			return $oTranslator->_($sKeyword);
		}

		return '';
	}
}