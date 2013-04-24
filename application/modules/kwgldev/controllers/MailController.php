<?php
/**
 *
 */
class Kwgldev_MailController extends Kwgl_Controller_Action {

	public function init () {
		parent::init();

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/mail.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');
	}

	public function indexAction () {

		$aContent = array();

		$oMail = new Model_Kwgldev_Mail();
		$aContent = $oMail->listTemplates();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;

	}

	public function createAction () {

		$aContent = array();

		$oMail = new Model_Kwgldev_Mail();
		$aContent = $oMail->createTemplate();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;
	}

	public function updateAction () {

		$aContent = array();

		$oMail = new Model_Kwgldev_Mail();
		$aContent = $oMail->updateTemplate();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;
	}

	public function deleteAction () {

		$aContent = array();

		$oMail = new Model_Kwgldev_Mail();
		$aContent = $oMail->deleteTemplate();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;
	}

}