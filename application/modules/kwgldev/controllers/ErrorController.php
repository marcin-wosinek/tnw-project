<?php
/**
 * Description of ErrorController
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Kwgldev_ErrorController extends Kwgl_Controller_Action {

	public function init() {
		$this->_helper->layout()->setLayout('layout_error');

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
//		$this->view->headLink()->appendStylesheet('/css/library/bootstrap.min.css');
//		$this->view->headLink()->appendStylesheet('/css/library/bootstrap-responsive.min.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');
	}

	public function errorAction () {

		$aContent = array();

		$oErrorHandler = $this->_getParam('error_handler');
		$oException = $oErrorHandler->exception;

		// Handle the Error/Exception based on its Type

		switch ($oErrorHandler->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 Error - Controller or Action not found
				if(!$this->getRequest()->isXmlHttpRequest()){
					$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
				}
				$this->_forward('fourohfour');
				break;
			default:
				if ($oException instanceof Kwgl_Exception) {
					$oKwglException = $oException;
				} else {
					$sMessage = $oException->getMessage();
					$sCode = $oException->getCode();
					$oKwglException = new Kwgl_Exception($sMessage, $sCode, $oException);
				}

				$aContent = array(
					'header' => 'Error - Something went wrong',
				);

				$bDebug = Kwgl_Config::get(array('mode', 'debug', 'active'));
				if ($bDebug) {
					$aContent['message'] = $oKwglException->getMessage();
					$aContent['trace'] = $oKwglException->getPreviousTraceAsString();
					$aContent['sql'] = $oKwglException->getSql();
				} else {
					$aContent['message'] = "An unexpected error has occurred while processing your request. Please try again.";
				}

                $this->view->headTitle('Error - Something went wrong');

				Model_Kwgldev_Response::addResponse(
					$aContent['message'],
					Model_Kwgldev_Response::STATUS_ERROR);

				break;
		}

		$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
		$this->view->aContent = $aContent;

		if($this->getRequest()->isXmlHttpRequest()){
			// Handle the AJAX Request within the parent Controller's postDispatch
			$this->setAjaxError($aContent['message']);
		}

	}

	public function fourohfourAction () {

		$this->view->headTitle('404 - Page not found');

		$aContent = array(
			'header' => 'Error 404 - Page not found',
			'message' => 'The page you requested was not found.',
		);

		$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
		$this->view->aContent = $aContent;

		Model_Kwgldev_Response::addResponse(
					$aContent['message'],
					Model_Kwgldev_Response::STATUS_ERROR);

		if($this->getRequest()->isXmlHttpRequest()){
			// Handle the AJAX Request within the parent Controller's postDispatch
			$this->setAjaxError($aContent['message']);
		}

	}

    public function notallowedAction () {

		$this->view->headTitle('Not authorised');

		$aContent = array(
			'header' => 'Error - Not authorised to access Page',
			'message' => 'You do not have sufficient privileges to access the page you requested.',
		);

		$this->view->aContent = $aContent;

		Model_Kwgldev_Response::addResponse(
			$aContent['message'],
			Model_Kwgldev_Response::STATUS_ERROR);

		if($this->getRequest()->isXmlHttpRequest()){
			// Handle the AJAX Request within the parent Controller's postDispatch
			$this->setAjaxError($aContent['message']);
		}

    }

}