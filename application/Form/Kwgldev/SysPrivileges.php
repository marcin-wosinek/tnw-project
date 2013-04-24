<?php
/**
 *
 */
class Form_Kwgldev_SysPrivileges extends Form_Kwgldev_Base {

	public function __construct ($sContext = null, $aAttributes = null, $aParameters = null){
		parent::__construct($sContext, $aAttributes, $aParameters);
	}

	public function init (){

		$oResource = new Zend_Form_Element_Select('selectResource');
		$oResource->setLabel('Resource Name')
			->setMultiOptions($this->_getResources());


		$oPrivilegeName = new Zend_Form_Element_Text('textPrivilegeName');
		$oPrivilegeName->setLabel('Privilege Name');


		$oSubmit = new Zend_Form_Element_Submit('submitSubmit');
		$oSubmit->setLabel('Submit')
				->setDecorators(array('ViewHelper'));

		$aCreateElements = array($oResource, $oPrivilegeName, $oSubmit);
		$aUpdateElements = array($oResource, $oPrivilegeName, $oSubmit);
		$aDeleteElements = array($oResource, $oPrivilegeName, $oSubmit);

		switch ($this->_sContext) {
			case self::CONTEXT_CREATE:
				$oResource->setAttrib('readonly', true);
				$oResource->setAttrib('disable', true);
				$oSubmit->setLabel('Create')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aCreateElements);
				break;
			case self::CONTEXT_UPDATE:
				$oResource->setAttrib('readonly', true);
				$oResource->setAttrib('disable', true);
				$oSubmit->setLabel('Update')
						->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements($aUpdateElements);
				break;
			case self::CONTEXT_DELETE:
				$oResource->setAttrib('readonly', true);
				$oResource->setAttrib('disable', true);
				$oPrivilegeName->setAttrib('readonly', true);
				$oPrivilegeName->setAttrib('disable', true);
				$oSubmit->setLabel('Delete')
						->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements($aDeleteElements);
				break;
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => $this->_sViewScript))));

	}

	private function _getResources ($bIncludeBlank = true) {

		$oDaoResource = Kwgl_Db_Table::factory('System_Resource');
		$aResources = $oDaoResource->fetchPairs(array('id', 'name'), null, array('name ASC'));

		if ($bIncludeBlank) {
			$aResources = array('0' => '') + $aResources;
		}

		return $aResources;

	}
}