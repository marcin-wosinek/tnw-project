<?php
/**
 *
 */
class Form_Kwgldev_SysRoles extends Form_Kwgldev_Base {

	public function __construct ($sContext = null, $aAttributes = null, $aParameters = null){
		parent::__construct($sContext, $aAttributes, $aParameters);
	}

	public function init (){

		$oRoleName = new Zend_Form_Element_Text('textRoleName');
		$oRoleName->setLabel('Role Name')
				->addValidator('Db_NoRecordExists', true, array('sys_role', 'name'));


		$oRoleParent = new Zend_Form_Element_Select('selectRoleParent');
		$oRoleParent->setLabel('Role Parent')
				->setMultiOptions($this->_getRoleParents());


		$oSubmit = new Zend_Form_Element_Submit('submitSubmit');
		$oSubmit->setLabel('Submit')
				->setDecorators(array('ViewHelper'));

		$aCreateElements = array($oRoleName, $oRoleParent, $oSubmit);
		$aUpdateElements = array($oRoleName, $oRoleParent, $oSubmit);
		$aDeleteElements = array($oRoleName, $oRoleParent, $oSubmit);

		switch ($this->_sContext) {
			case self::CONTEXT_CREATE:
				$oRoleName->setRequired();
				$oSubmit->setLabel('Create')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aCreateElements);
				break;
			case self::CONTEXT_UPDATE:
				$oRoleName->setRequired();
				$oSubmit->setLabel('Update')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aUpdateElements);
				break;
			case self::CONTEXT_DELETE:
				$oRoleName->setAttrib('readonly', true);
				$oRoleName->setAttrib('disable', true);
				$oRoleParent->setAttrib('readonly', true);
				$oRoleParent->setAttrib('disable', true);
				$oSubmit->setLabel('Delete')
						->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements($aDeleteElements);
				break;
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => $this->_sViewScript))));

	}

	private function _getRoleParents ($bIncludeBlank = true) {

		$oDaoRole = Kwgl_Db_Table::factory('System_Role');
		$aRoles = $oDaoRole->fetchPairs(array('id', 'name'), null, array('name ASC'));

		if ($bIncludeBlank) {
			$aRoles = array('0' => '') + $aRoles;
		}

		return $aRoles;

	}
}