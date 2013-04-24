<?php
/**
 * Adds multilanguage support
 *
 * @author Darshan Wijekoon <>
 * @category PHP-Kwgl
 * @package Kwgl_Controller
 * @subpackage Plugin
 */
class Kwgl_Controller_Plugin_Multilanguage extends Zend_Controller_Plugin_Abstract {

	private $oLocale;

	public function routeStartup (Zend_Controller_Request_Abstract $oRequest) {

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_Multilanguage - routeStartup- Start');

		$this->oLocale = new Zend_Locale(Kwgl_Language::DEFAULT_LOCALE);

		// sets an application-wide locale
		Zend_Registry::set('Zend_Locale', $this->oLocale);

		$this->initTranslationAdapter();

		$this->initFormValidationMsgTranslator();

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_Multilanguage - routeStartup - End');
	}

	/**
	 * Creates a new translation adapter which is to be treated as the main
	 * adapter for translating all static content including text, form labels, button labels, titles, etc.
	 * It is set as the default translator for the application automatically by setting it in the registry.
	 */
	private function initTranslationAdapter () {

		// Attach Cache if it's enabled for the current environment
		if (Kwgl_Config::get(array('mode', 'cache', 'translations')) == 1) {
			$oCacheManager = Kwgl_Cache::getManager();
			$oTranslationsCache = $oCacheManager->getCache('translations');
			Zend_Translate::setCache($oTranslationsCache);
		}

		try {
			$oTranslate = new Zend_Translate(
				array(
					'adapter' => 'Kwgl_Translate_Adapter_DbTable',
					'content' => 'some content',
				)
			);
		} catch (Exception $e) {
			// General failure
			echo $e->getMessage();
		}

		Zend_Registry::set('Zend_Translate', $oTranslate);
	}

	/**
	 * Attaches a translator for Zend_Form validation.
	 */
	private function initFormValidationMsgTranslator() {

		$sTranslationResourcesRootPath = 'Resources/Zend/languages';

		$sLanguage = $this->oLocale->getLanguage();
		$sTranslationFilePath = $sTranslationResourcesRootPath . '/'. $sLanguage .'/Zend_Validate.php';

		$aTranslations = require_once $sTranslationFilePath;

		$oTranslate = new Zend_Translate(
			array(
				'adapter' => 'array',
				'content' => $aTranslations,
				'locale' => $this->oLocale->toString()
			)
		);

		// attach the validator to Zend_Validate module
		Zend_Validate::setDefaultTranslator($oTranslate);
	}

}