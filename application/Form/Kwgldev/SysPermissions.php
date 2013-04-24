<?php
/**
 *
 */
class Form_Kwgldev_SysPermissions extends Form_Kwgldev_Base {

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

		$oRole = new Zend_Form_Element_Select('selectRole');
		$oRole->setLabel('Role Name')
			->setMultiOptions($this->_getRoles());

		$oPrivilege = new Zend_Form_Element_Select('selectPrivilege');
		$oPrivilege->setLabel('Resource / Privilege Name');


		$oPermission = new Zend_Form_Element_Select('selectPermission');
		$oPermission->setLabel('Permission')
			->setMultiOptions($this->_getPermissions());

		$oSubmit = new Zend_Form_Element_Submit('submitSubmit');
		$oSubmit->setLabel('Submit')
				->setDecorators(array('ViewHelper'));

		$aPageCreateElements = array($oRole, $oPrivilege, $oPermission, $oSubmit);
		$aPageUpdateElements = array($oRole, $oPrivilege, $oPermission, $oSubmit);
		$aPageDeleteElements = array($oRole, $oPrivilege, $oPermission, $oSubmit);
		$aModelCreateElements = array($oRole, $oPrivilege, $oPermission, $oSubmit);
		$aModelUpdateElements = array($oRole, $oPrivilege, $oPermission, $oSubmit);
		$aModelDeleteElements = array($oRole, $oPrivilege, $oPermission, $oSubmit);

		switch ($this->_sContext) {
			case self::CONTEXT_PAGE_CREATE:
				$oPrivilege->setMultiOptions($this->_getPageResourcePrivileges())
						->setRequired();
				$oRole->setRequired();
				$oPermission->setRequired();
				$oSubmit->setLabel('Create')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aPageCreateElements);
				break;
			case self::CONTEXT_PAGE_UPDATE:
				$oPrivilege->setMultiOptions($this->_getPageResourcePrivileges())
						->setAttrib('readonly', true);
				$oRole->setAttrib('readonly', true);
				$oPermission->setRequired();
				$oSubmit->setLabel('Update')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aPageUpdateElements);
				break;
			case self::CONTEXT_PAGE_DELETE:
				$oPrivilege->setMultiOptions($this->_getPageResourcePrivileges())
						->setAttrib('readonly', true);
				$oRole->setAttrib('readonly', true);
				$oPermission->setAttrib('readonly', true);
				$oSubmit->setLabel('Delete')
						->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements($aPageDeleteElements);
				break;
			case self::CONTEXT_MODEL_CREATE:
				$oPrivilege->setMultiOptions($this->_getModelResourcePrivileges())
						->setRequired();
				$oRole->setRequired();
				$oPermission->setRequired();
				$oSubmit->setLabel('Create')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aModelCreateElements);
				break;
			case self::CONTEXT_MODEL_UPDATE:
				$oPrivilege->setMultiOptions($this->_getModelResourcePrivileges())
						->setAttrib('readonly', true);
				$oRole->setAttrib('readonly', true);
				$oPermission->setRequired();
				$oSubmit->setLabel('Update')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aModelUpdateElements);
				break;
			case self::CONTEXT_MODEL_DELETE:
				$oPrivilege->setMultiOptions($this->_getModelResourcePrivileges())
						->setAttrib('readonly', true);
				$oRole->setAttrib('readonly', true);
				$oPermission->setAttrib('readonly', true);
				$oSubmit->setLabel('Delete')
						->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements($aModelDeleteElements);
				break;
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => $this->_sViewScript))));

	}

	private function _getPageResourcePrivileges ($bIncludeBlank = true) {

		$oDaoPrivileges = Kwgl_Db_Table::factory('System_Resource_Privilege'); /* @var $oDaoPrivileges Dao_System_Resource_Privilege */

		$aPrivileges = $oDaoPrivileges->getPrivilegesForPageResource();

		if ($bIncludeBlank) {
			$aPrivileges = array('' => '') + $aPrivileges;
		}

		return $aPrivileges;

	}

	private function _getModelResourcePrivileges ($bIncludeBlank = true) {

		$oDaoPrivileges = Kwgl_Db_Table::factory('System_Resource_Privilege'); /* @var $oDaoPrivileges Dao_System_Resource_Privilege */

		$aPrivileges = $oDaoPrivileges->getPrivilegesForModelResource();

		if ($bIncludeBlank) {
			$aPrivileges = array('' => '') + $aPrivileges;
		}

		return $aPrivileges;

	}

	private function _getRoles ($bIncludeBlank = true) {

		$oDaoRoles = Kwgl_Db_Table::factory('System_Role'); /* @var $oDaoRoles Dao_System_Role */
		$aRoles = $oDaoRoles->fetchPairs(array('id', 'name'), null, array('name ASC'));

		if ($bIncludeBlank) {
			$aRoles = array('' => '') + $aRoles;
		}

		return $aRoles;

	}

	private function _getPermissions ($bIncludeBlank = true) {

		$aPermissions = array ();
		$aPermissions['allow'] = 'Allow';
		$aPermissions['deny'] = 'Deny';

		if ($bIncludeBlank) {
			$aPermissions = array('' => '') + $aPermissions;
		}

		return $aPermissions;

	}
}