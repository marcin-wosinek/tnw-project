<?php
/**
 *
 */
class Form_Kwgldev_SysResources extends Form_Kwgldev_Base {

	const CONTEXT_PAGE_CREATE = 'page-create';
	const CONTEXT_PAGE_UPDATE = 'page-update';
	const CONTEXT_PAGE_DELETE = 'page-delete';
	const CONTEXT_MODEL_CREATE = 'model-create';
	const CONTEXT_MODEL_UPDATE = 'model-update';
	const CONTEXT_MODEL_DELETE = 'model-delete';

	public function __construct ($sContext = null, $aAttributes = null, $aParameters = null){
		parent::__construct($sContext, $aAttributes, $aParameters);
	}

	public function init (){

		$oResourceName = new Zend_Form_Element_Text('textResourceName');
		$oResourceName->setLabel('Resource Name');

		$oResourceModuleName = new Zend_Form_Element_Select('selectResourceModuleName');
		$oResourceModuleName->setLabel('Module Name')
				->setMultiOptions($this->_getResourceModules());

		$oResourceControllerName = new Zend_Form_Element_Select('selectResourceControllerName');
		$oResourceControllerName->setLabel('Controller Name')
				->setRegisterInArrayValidator(false);

		$oResourceActionName = new Zend_Form_Element_Select('selectResourceActionName');
		$oResourceActionName->setLabel('Action Name')
				->setRegisterInArrayValidator(false);

		$oResourceParent = new Zend_Form_Element_Select('selectResourceParent');
		$oResourceParent->setLabel('Resource Parent');

		/*
		$oResourceType = new Zend_Form_Element_Select('selectResourceType');
		$oResourceType->setLabel('Resource Type')
				->setRequired()
				->setMultiOptions($this->_getResourceTypes());
		*/

		$oSubmit = new Zend_Form_Element_Submit('submitSubmit');
		$oSubmit->setLabel('Submit')
				->setDecorators(array('ViewHelper'));

		$aPageCreateElements = array($oResourceName, $oResourceModuleName, $oResourceControllerName, $oResourceActionName, $oSubmit);
		$aPageDeleteElements = array($oResourceName, $oSubmit);
		$aModelCreateElements = array($oResourceName, $oResourceParent, $oSubmit);
		$aModelUpdateElements = array($oResourceName, $oResourceParent, $oSubmit);
		$aModelDeleteElements = array($oResourceName, $oResourceParent, $oSubmit);

		switch ($this->_sContext) {
			case self::CONTEXT_PAGE_CREATE:
				$oResourceName->setAttrib('readonly', true);
				$oResourceName->setRequired();
				$oSubmit->setLabel('Create')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aPageCreateElements);
				break;
			case self::CONTEXT_PAGE_DELETE:
				$oResourceName->setAttrib('readonly', true);
				$oResourceName->setAttrib('disable', true);
				$oSubmit->setLabel('Delete')
						->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements($aPageDeleteElements);
				break;
			case self::CONTEXT_MODEL_CREATE:
				$oResourceName->setRequired()
					->addValidator('Db_NoRecordExists', true, array('table' => 'sys_resource', 'field' => 'name'));
				$oResourceParent->setMultiOptions($this->_getResourceParentsForModels());
				$oSubmit->setLabel('Create')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aModelCreateElements);
				break;
			case self::CONTEXT_MODEL_UPDATE:
				$aClause = array('field' => 'id', 'value' => $this->_aParameters['resource_id']);
				$oResourceName->setRequired()
					->addValidator('Db_NoRecordExists', true, array('table' => 'sys_resource', 'field' => 'name', 'exclude' => $aClause));
				$oResourceParent->setMultiOptions($this->_getResourceParentsForModels($this->_aParameters['resource_id']));
				$oSubmit->setLabel('Update')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aModelUpdateElements);
				break;
			case self::CONTEXT_MODEL_DELETE:
				$oResourceName->setAttrib('readonly', true);
				$oResourceName->setAttrib('disable', true);
				$oResourceParent->setAttrib('readonly', true);
				$oResourceParent->setAttrib('disable', true);
				$oSubmit->setLabel('Delete')
						->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements($aModelDeleteElements);
				break;
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => $this->_sViewScript))));

	}

	private function _getResourceParentsForModels ($iIdToExclude = null, $bIncludeBlank = true) {

		$oDaoResource = Kwgl_Db_Table::factory('System_Resource');
		if (is_null($iIdToExclude)) {
			$aResources = $oDaoResource->fetchPairs(array('id', 'name'), array('type = ?' => Dao_System_Resource::TYPE_MODEL), array('name ASC'));
		} else {
			$aResources = $oDaoResource->fetchPairs(array('id', 'name'), array('type = ?' => Dao_System_Resource::TYPE_MODEL, 'id <> ?' => $iIdToExclude), array('name ASC'));
		}


		if ($bIncludeBlank) {
			if (empty($aResources)) {
				$aResources = array('0' => 'No Model Resources found');
			} else {
				$aResources = array('0' => 'Select Parent Model Resource') + $aResources;
			}

		}

		return $aResources;

	}

	/*
	private function _getResourceTypes ($bIncludeBlank = true) {

		$aTypes = array(
			Dao_System_Resource::TYPE_PAGE => 'Page',
			Dao_System_Resource::TYPE_MODEL => 'Model',
		);

		if ($bIncludeBlank) {
			$aTypes = array('' => '') + $aTypes;
		}

		return $aTypes;

	}
	*/

	private function _getResourceModules ($bIncludeBlank = true) {

		$aStructure = Kwgl_Utility_Mvc::getStructure();

		$aModules = array();
		foreach ($aStructure as $sModule => $aControllers) {
			$aModules[strtolower($sModule)] = ucfirst($sModule);
		}

		if ($bIncludeBlank) {
			$aModules = array('' => 'Select Module') + $aModules;
		}

		return $aModules;

	}
}