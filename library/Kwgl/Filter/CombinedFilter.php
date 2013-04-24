<?php
/**
 * Filter that by default trims input and can be configured to additionally strip tags and convert HTML entities in a single filter operation
 * Flags are to be set denoting whether or not to enable strip tags functionality and HTML entities conversion functionality
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Filter
 */
class Kwgl_Filter_CombinedFilter implements Zend_Filter_Interface {

	/**
	 * Indicates whether Strip Tags filter should be used during the filter process
	 *
	 * @var boolean
	 */
	private $_bStripTags = true;

	/**
	 * Indicates whether HTML Entities filter should be used during the filter process
	 *
	 * @var boolean
	 */
	private $_bHTMLEntities = false;

	/**
	 * Sets up options so as to whether Strip Tags filter and HTML Entities filter should be carried out during the filter process
	 *
	 * @param array $aOptions
	 */
	public function __construct ($aOptions = null) {

		if (is_array($aOptions)) {

			if (isset($aOptions['strip_tags'])) {
				$this->_bStripTags = (boolean)$aOptions['strip_tags'];
			}

			if (isset($aOptions['html_entities'])) {
				$this->_bHTMLEntities = (boolean)$aOptions['html_entities'];
			}

		}

	}

	/**
	 * Carries out filtering. Depending on options set, may or may not use Strip Tags filter and HTML Entities filter
	 *
	 * @param mixed|string $mValue
	 * @return mixed|string
	 */
	public function filter ($mValue) {

		$mValueFiltered = $mValue;

		$oStringTrimFilter = new Zend_Filter_StringTrim();
		$mValueFiltered = $oStringTrimFilter->filter($mValueFiltered);

		if ($this->_bStripTags) {
			$oStripTagsFilter = new Zend_Filter_StripTags();
			$mValueFiltered = $oStripTagsFilter->filter($mValueFiltered);
		}

		if ($this->_bHTMLEntities) {
			$oHTMLENtitiesFilter = new Zend_Filter_HtmlEntities();
			$mValueFiltered = $oHTMLENtitiesFilter->filter($mValueFiltered);
		}

		return $mValueFiltered;

	}

}