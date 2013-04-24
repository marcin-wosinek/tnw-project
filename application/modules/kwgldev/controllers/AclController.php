<?php
/**
 *
 */
class Kwgldev_AclController extends Kwgl_Controller_Action {

	/**
	 *
	 */
	public function init () {
		parent::init();

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
//		$this->view->headLink()->appendStylesheet('/css/library/bootstrap.min.css');
//		$this->view->headLink()->appendStylesheet('/css/library/bootstrap-responsive.min.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/acl.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');
	}

	public function indexAction () {


	}

	/**
	 *
	 * @return type
	 */
	public function rolesAction () {

		$aContent = array();

		$oAcl = new Model_Kwgldev_Acl();
		$aContent = $oAcl->manageRoles();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 * @return type
	 */
	public function pageresourcesAction () {

		$aContent = array();

		$oAcl = new Model_Kwgldev_Acl();
		$aContent = $oAcl->managePageResources();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 * @return type
	 */
	public function modelresourcesAction () {

		$aContent = array();

		$oAcl = new Model_Kwgldev_Acl();
		$aContent = $oAcl->manageModelResources();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$aResources = Model_Kwgldev_Acl::getResourcesWithParents('model');

		$aContent['resources'] = $aResources;

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 * @return type
	 */
	public function privilegesAction () {

		$aContent = array();

		$oAcl = new Model_Kwgldev_Acl();
		$aContent = $oAcl->managePrivileges();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 * @return type
	 */
	public function permissionsAction () {

		$aContent = array();

		$oAcl = new Model_Kwgldev_Acl();
		$aContent = $oAcl->managePermissions();
		if (isset($aContent['redirect'])) {
			$this->_redirect($aContent['redirect']);
			return;
		}

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 */
	public function pageoverviewAction () {

		$aContent = array();

		$aContent = Model_Kwgldev_Acl::getPageOverview();

		$this->view->aContent = $aContent;

	}

	/**
	 *
	 */
	public function modeloverviewAction () {

		$aContent = array();

		$aContent = Model_Kwgldev_Acl::getModelOverview();

		$this->view->aContent = $aContent;

	}

}