<?php
/**
 * Handles the Authentication and Access Control for the User
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Controller
 * @subpackage Plugin
 */
class Kwgl_Controller_Plugin_AuthenticateSetup extends Zend_Controller_Plugin_Abstract {

	public function preDispatch (Zend_Controller_Request_Abstract $oRequest) {

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_AuthenticateSetup - preDispatch - Start');

		Kwgl_Authenticate::initialise();

		$bVerified = Kwgl_Authenticate::verifySession();
		if (!$bVerified) {
			Kwgl_Authenticate::logout();
		}

		$bAllowed = $this->_pageAclCheck($oRequest);

		if ($bAllowed) {
			// User has permission to access the page
		} else {
			// User does not have permission to access the page
			$sModuleToUse = 'default';

			// Check if Module Specific 'Permission Denied' page exists
			$oTestRequest = new Zend_Controller_Request_Http();
			$oTestRequest->setModuleName($oRequest->getModuleName())
				->setControllerName('error')
				->setActionName('notallowed');
			$oFrontController = Zend_Controller_Front::getInstance();
			if ($oFrontController->getDispatcher()->isDispatchable($oTestRequest)) {
				$sModuleToUse = $oRequest->getModuleName();
			}

			$oRequest->setModuleName($sModuleToUse)
				->setControllerName('error')
				->setActionName('notallowed')
				->setDispatched(false);
		}

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_AuthenticateSetup - preDispatch - End');

	}

	/**
	 * Returns true or false based on Current User's Access to the Page
	 * Throws an Exception if the Resource or part of it (parent) can't be found
	 *
	 * @param Zend_Controller_Request_Abstract $oRequest
	 * @return boolean
	 */
	protected function _pageAclCheck ($oRequest) {
		// To see if the User has Permissions to the Page Requested
		$sRole = Kwgl_User::getRole('name');
		$sModuleName = $oRequest->getModuleName();
		$sControllerName = $oRequest->getControllerName();
		$sActionName = $oRequest->getActionName();

		return Kwgl_Acl::allowedForPage($sRole, $sModuleName, $sControllerName, $sActionName);
	}

}