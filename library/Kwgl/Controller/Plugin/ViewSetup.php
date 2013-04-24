<?php
/**
 *
 * @category PHP-Kwgl
 * @package Kwgl_Controller
 * @subpackage Plugin
 */
class Kwgl_Controller_Plugin_ViewSetup extends Zend_Controller_Plugin_Abstract {

	protected $_oView;

	public function preDispatch (Zend_Controller_Request_Abstract $oRequest) {

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_ViewSetup - preDispatch - Start');

		$sModuleName = $oRequest->getModuleName();

		// Setup Module specific Layout
		$sLayoutPath = ROOT_DIR . '/application/modules/' . $sModuleName . '/views/layouts';
		Zend_Layout::startMvc(array(
			'layoutPath' => $sLayoutPath,
			'layout' => 'layout_default',
		));

		$oViewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$oViewRenderer->init();
		$oView = $oViewRenderer->view;

		// Add Module Specific View Helper(s)
		$sModuleViewHelperPath = ROOT_DIR . '/application/modules/' . $sModuleName . '/views/helpers';
		$sModuleViewHelperClassPrefix = ucwords($sModuleName) . '_View_Helper';
		$oView->addHelperPath($sModuleViewHelperPath, $sModuleViewHelperClassPrefix);

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_ViewSetup - preDispatch - End');

	}

	public function postDispatch (Zend_Controller_Request_Abstract $oRequest) {

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_ViewSetup - postDispatch - Start');

        // Check if AJAX Request is defined
        if (Kwgl_Ajax::hasResult()) {
            $this->getResponse()->setHeader('Content-Type', 'application/json', true);
            // Print JSON Encoded AJAX Response
            echo Kwgl_Ajax::display();
        }

		//Kwgl_Benchmark::setMarker('Kwgl_Controller_Plugin_ViewSetup - postDispatch - End');

    }

}