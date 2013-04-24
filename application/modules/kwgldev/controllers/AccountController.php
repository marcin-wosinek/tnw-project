<?php
/**
 *
 */
class Kwgldev_AccountController extends Kwgl_Controller_Action {

	public function init () {
		parent::init();

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/account.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/min/?g=jsViews');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');

//		$this->view->headScript()->appendFile('/tmpl/pagination.tmpl', 'text/x-jsrender', array('id' => 'iTemplateBasePagination'));
	}

	public function indexAction () {

		$aContent = array();

		$oAccount = new Model_Kwgldev_Account();
		$aContent = $oAccount->listAccounts();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;

		Model_Kwgldev_Layout::requirePaginationTemplate();

	}

	public function createAction () {

		$aContent = array();

		$oAccount = new Model_Kwgldev_Account();
		$aContent = $oAccount->createAccount();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;
	}

	public function updateAction () {

		$aContent = array();

		$oMail = new Model_Kwgldev_Account();
		$aContent = $oMail->updateAccount();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;
	}

	public function deleteAction () {

		$aContent = array();

		$oMail = new Model_Kwgldev_Account();
		$aContent = $oMail->deleteAccount();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;
	}

}