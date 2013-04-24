<?php
/**
 *
 */
class Kwgldev_IndexController extends Kwgl_Controller_Action {

	/**
	 *
	 */
	public function init () {
		parent::init();

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');
	}

	/**
	 *
	 */
	public function indexAction () {

		$aContent = array();

		$sNotice = '';

		// Check if logged in
		$aUser = Kwgl_User::get(null, true);

		$oForm = null;
		if (is_null($aUser)) {
			$oForm = new Form_Kwgldev_SysAccount(Form_Kwgldev_SysAccount::CONTEXT_DEV_LOGIN);

			if ($this->_oRequest->isPost()) {
				if ($oForm->isValid($this->_oRequest->getPost())) {

					$aFormValues = $oForm->getValues();
					$sUsername = $aFormValues['textUsername'];
					$sPassword = $aFormValues['passwordPassword'];

					$bLoginSuccessful = Kwgl_Authenticate::login($sUsername, $sPassword);

					if ($bLoginSuccessful) {
						$this->_redirect('/kwgldev/index/dashboard/');
					} else {
						$sNotice = 'Your login attempt was not successful.';
					}
				}
			}

		} else {
			$this->_redirect('/kwgldev/index/dashboard/');
		}

		$aContent['form'] = $oForm;

		$aContent['notice'] = $sNotice;

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 */
	public function dashboardAction () {

	}

	public function passwordAction () {

		$aContent = array();

		// Check if logged in
		$aUser = Kwgl_User::get(null, true);

		$oForm = null;
		if (!is_null($aUser)) {
			$oForm = new Form_Kwgldev_SysAccount(Form_Kwgldev_SysAccount::CONTEXT_CHANGE_PASSWORD);

			if ($this->_oRequest->isPost()) {
				if ($oForm->isValid($this->_oRequest->getPost())) {

					$iAccountId = $aUser['id'];

					$aFormValues = $oForm->getValues();

					$sPassword = $aFormValues['passwordPassword'];

					$oDaoAccount = Kwgl_Db_Table::factory('System_Account');
					$aUpdateData = array();
					$aUpdateData['password'] = sha1($sPassword, true);
					$oDaoAccount->update($aUpdateData, array('id = ?' => $iAccountId));

					Model_Kwgldev_Response::addResponse(
						'Your password has been successfully changed.',
						Model_Kwgldev_Response::STATUS_SUCCESS);
				}
			}

		} else {
			$this->_redirect('/admin/index/dashboard/');
		}

		$aContent['form'] = $oForm;

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 */
	public function logoutAction () {
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		Kwgl_Authenticate::logout();
		$this->_redirect('/kwgldev/');
	}

}