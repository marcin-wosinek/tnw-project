<?php
/**
 * Filter class implementing HTMLPurifier
 * Can be used for prevention against XSS attacks
 * Intended for use with WYSIWYG editors, but can be used
 * with all form elements where HTML is allowed to be entered.
 * For other cases, Zend_Filter_HtmlEntities or Zend_Filter_StripTags
 * should be used instead to filter & get rid of all HTML.
 *
 * @author Darshan Wijekoon <>
 * @category PHP-Kwgl
 * @package Kwgl_Filter
 */
class Kwgl_Filter_HtmlPurifier implements Zend_Filter_Interface {

	private $_oPurifier;

	/**
	 * Creates a new instance of HTMLPurifier using default configuration options
	 *
	 * @param array $aOptions
	 */
	public function __construct($aOptions = array()) {

		require_once 'HTMLPurifier/HTMLPurifier.auto.php';

		HTMLPurifier_Bootstrap::registerAutoload();
		$oConfig = HTMLPurifier_Config::createDefault();
		$this->_oPurifier = new HTMLPurifier($oConfig);

		//
	}

	/**
	 * Removes malicious code & makes HTML standards compliant
	 *
	 * @param string $value
	 * @return string
	 */
	public function filter($value) {

		return $this->_oPurifier->purify($value);
	}

}