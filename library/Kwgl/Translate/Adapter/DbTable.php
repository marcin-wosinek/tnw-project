<?php
/**
 * Custom translate adapter for Zend_Translate,
 * Loads translation data from a database table
 *
 * @author Darshan Wijekoon <>
 */
class Kwgl_Translate_Adapter_DbTable extends Zend_Translate_Adapter {

	private $_data = array();

	protected function _loadTranslationData($data, $locale, array $options = array()) {

		$oTblTranslations = Kwgl_Db_Table::factory('System_Translations'); /* @var Kwgl_Dao_System_Translations */

		// get the keyword/translation pair for the current locale
		$aListing = $oTblTranslations->fetchList(array('keyword', $locale));

		// re-arrange listing array
		$aTranslationData = array();
		foreach($aListing as $aTranslation) {
			$aTranslationData[$locale][$aTranslation['keyword']] = $aTranslation[$locale];
		}

		$this->_data = $aTranslationData;

		return $this->_data;
	}

	public function toString() {

		return "DbTable";
   }

}