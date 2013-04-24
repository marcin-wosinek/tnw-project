<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Controller
 * @subpackage Plugin
 */
class Kwgl_Controller_Plugin_ErrorHandler extends Zend_Controller_Plugin_Abstract{

	public function routeShutdown (Zend_Controller_Request_Abstract $oRequest) {

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_ErrorHandler - routeShutdown - Start');

		$oFrontController = Zend_Controller_Front::getInstance();

		// Use Module Specific Error Controller if they are available
		if ($oFrontController->hasPlugin('Zend_Controller_Plugin_ErrorHandler')) {

			$oErrorHandlerPlugin = $oFrontController->getPlugin('Zend_Controller_Plugin_ErrorHandler');

			$oTestRequest = new Zend_Controller_Request_Http();
			$oTestRequest->setModuleName($oRequest->getModuleName())
				->setControllerName($oErrorHandlerPlugin->getErrorHandlerController())
				->setActionName($oErrorHandlerPlugin->getErrorHandlerAction());

			if ($oFrontController->getDispatcher()->isDispatchable($oTestRequest)) {
				$oErrorHandlerPlugin->setErrorHandlerModule($oRequest->getModuleName());
			}

		}

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_ErrorHandler - routeShutdown - End');

	}

}