<?php
/**
 * Description of ErrorController
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class ErrorController extends Kwgl_Controller_Action {

	public function init() {
		//if($this->getRequest()->isXmlHttpRequest()){
		//	$this->_helper->layout()->disableLayout();
		//} else {
			$this->_helper->layout()->setLayout('layout_error');
		//}
		 //disable the layout
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

				// Get SQL if any
				$sSql = $oKwglException->getSql();
				$aContent = array(
					'header' => 'Error - Something went wrong',
				);

				//$bDebug = (bool)(Zend_Registry::get(CONFIG)->mode->debug->active);
				$bDebug = Kwgl_Config::get(array('mode', 'debug', 'active'));
				if ($bDebug) {
					$aContent['message'] = $oKwglException->getMessage();
					$aContent['trace'] = $oKwglException->getPreviousTraceAsString();
					$aContent['sql'] = $oKwglException->getSql();
				} else {
					$aContent['message'] = "An unexpected error has occurred while processing your request. Please try again.";
				}

				// application error
				$head = '<h1 style="color: #F36F21;">Error!</h1>';
				$content = "<span>An unexpected error occurred. Please try again later.</span>";
                $this->view->headTitle('Error - Something went wrong');
				break;
		}

		/*
		// Clear previous content
		$this->getResponse()->clearBody();

        //show exception in debug mode
		$bDebug = (bool)(Zend_Registry::get(CONFIG)->mode->debug->active == 0);
        if($bDebug)
            $content .= '<br/>' . $exception->getMessage();

		//render the error
		if($this->getRequest()->isXmlHttpRequest()){
			//if the response is an ajax request
			$this->setAjaxError($content);
		}else{
			$this->view->content = $head . $content;
		}
		 *
		 */


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

		if($this->getRequest()->isXmlHttpRequest()){
			// Handle the AJAX Request within the parent Controller's postDispatch
			$this->setAjaxError($aContent['message']);
		}

    }

}