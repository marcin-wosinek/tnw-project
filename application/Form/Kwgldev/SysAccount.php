<?php
/**
 *
 */
class Form_Kwgldev_SysAccount extends Form_Kwgldev_Base {

	const CONTEXT_DEV_LOGIN = 'dev-login';
	const CONTEXT_CHANGE_PASSWORD = 'admin-change-password';

	public function init () {

		$oUsername = new Zend_Form_Element_Text('textUsername');
		$oUsername->setLabel('Username')
			->setAttrib('class', 'cSpan04');

		$oPassword = new Zend_Form_Element_Password('passwordPassword');
		$oPassword->setLabel('Password')
			->setAttrib('class', 'cSpan04');

		$oPasswordConfirmation = new Zend_Form_Element_Password('passwordPasswordConfirmation');
		$oPasswordConfirmation->setLabel('Confirm Password')
			->setAttrib('class', 'cSpan04');

		$oPasswordCurrent = new Zend_Form_Element_Password('passwordPasswordCurrent');
		$oPasswordCurrent->setLabel('Password')
			->setAttrib('class', 'cSpan04');

		$oEmail = new Zend_Form_Element_Text('textEmail');
		$oEmail->setLabel('Email')
			->setAttrib('class', 'cSpan04');

		$oRole = new Zend_Form_Element_Select('selectRole');
		$oRole->setLabel('Role')
			->setAttrib('class', 'cSpan04 cTextCapitalise');

		$oSubmit = new Zend_Form_Element_Submit('submitSubmit');
		$oSubmit->setLabel('Submit')
				->setDecorators(array('ViewHelper'));

		switch ($this->_sContext) {
			case self::CONTEXT_DEV_LOGIN:
				$oUsername->setRequired();
				$oPassword->setRequired();
				$oSubmit->setLabel('Login to Dev')
						->setAttrib('class', 'cButton cButtonPrimary');
				$this->addElements(array($oUsername, $oPassword, $oSubmit));
				break;
			case self::CONTEXT_CHANGE_PASSWORD:
				$oPassword->setRequired()
					->setLabel('New Password')
					->setDecorators(array('ViewHelper'));
				$oPasswordConfirmation->setRequired()
					->setLabel('Confirm New Password')
					->addValidator('Identical', true, array('token' => 'passwordPassword', 'messages' => array(Zend_Validate_Identical::NOT_SAME => 'The Passwords are not the same', Zend_Validate_Identical::MISSING_TOKEN => 'Password to compare with not provided')))
					->setDecorators(array('ViewHelper'));
				$oPasswordCurrent->setRequired()
					->setLabel('Current Password')
					->addValidator('VerifyCurrentPassword', true, array('table' => Kwgl_Db_Table::name()->System_Account, 'field' => 'password', 'messages' => array(Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND => 'Current Password is incorrect')))
					->setDecorators(array('ViewHelper'));
				$oSubmit->setLabel('Change Password')
						->setAttrib('class', 'cButton cButtonPrimary');
				$this->addElements(array($oPasswordCurrent, $oPassword, $oPasswordConfirmation, $oSubmit));
				break;
			case self::CONTEXT_CREATE:
				$oRole->setMultiOptions($this->_getRoles())
					->setRequired();
				$oEmail->setRequired()
					->addValidator('EmailAddress')
					->addValidator('Db_NoRecordExists', true, array('table' => Kwgl_Db_Table::name()->System_Account, 'field' => 'email'));
				$oUsername->setRequired()
					->addValidator('Db_NoRecordExists', true, array('table' => Kwgl_Db_Table::name()->System_Account, 'field' => 'username'));
				$oPassword->setRequired();
				$oPasswordConfirmation->setRequired()
					->addValidator('Identical', true, array('token' => 'passwordPassword', 'messages' => array(Zend_Validate_Identical::NOT_SAME => 'The Passwords are not the same', Zend_Validate_Identical::MISSING_TOKEN => 'Password to compare with not provided')));
				$oSubmit->setLabel('Create Account')
					->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements(array($oRole, $oEmail, $oUsername, $oPassword, $oPasswordConfirmation, $oSubmit));
				break;
			case self::CONTEXT_UPDATE:
				$aClause = array('field' => 'id', 'value' => $this->_aParameters['account_id']);
				$oRole->setMultiOptions($this->_getRoles())
					->setRequired();
				$oEmail->setRequired()
					->addValidator('EmailAddress')
					->addValidator('Db_NoRecordExists', true, array('table' => Kwgl_Db_Table::name()->System_Account, 'field' => 'email', 'exclude' => $aClause));
				$oUsername->setRequired()
					->addValidator('Db_NoRecordExists', true, array('table' => Kwgl_Db_Table::name()->System_Account, 'field' => 'username', 'exclude' => $aClause));
				$oPasswordConfirmation->addValidator('Identical', true, array('token' => 'passwordPassword', 'messages' => array(Zend_Validate_Identical::NOT_SAME => 'The Passwords are not the same', Zend_Validate_Identical::MISSING_TOKEN => 'Password to compare with not provided')));
				$oSubmit->setLabel('Update Account')
					->setAttrib('class', 'cButton cButtonSuccess');
				$this->addElements(array($oRole, $oEmail, $oUsername, $oPassword, $oPasswordConfirmation, $oSubmit));
				break;
			case self::CONTEXT_DELETE:
				$oRole->setMultiOptions($this->_getRoles())
					->setAttrib('readonly', true);
				$oEmail->setAttrib('readonly', true);
				$oUsername->setAttrib('readonly', true);
				$oSubmit->setLabel('Delete Account')
					->setAttrib('class', 'cButton cButtonDanger');
				$this->addElements(array($oRole, $oEmail, $oUsername, $oSubmit));
				break;
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => $this->_sViewScript))));

	}

	private function _getRoles ($bIncludeBlank = true) {

		$oDaoRoles = Kwgl_Db_Table::factory('System_Role'); /* @var $oDaoRoles Dao_System_Role */
		$aRoles = $oDaoRoles->fetchPairs(array('id', 'name'), null, array('name ASC'));

		if ($bIncludeBlank) {
			$aRoles = array('' => '') + $aRoles;
		}

		return $aRoles;

	}

}