<?php

class Xhr_IndexController extends Kwgl_Controller_Action {

	public function indexAction() {


	}
	
	public function ajaxexampleAction() {
		
		$aResponse = array();
		//delay the response
		sleep(1);
		//do something
		$aResponse['data'] = uniqid();
		
		//send request back to front
		$this->setAjaxResponse($aResponse);
		
	}
	
	public function paginationAction() {
		
		//create select object to pass to pagination function (should be done in module level)
		$oSelect = new Zend_Db_Select(Kwgl_Db_Table::getDefaultAdapter());
		$oSelect->from(Kwgl_Db_Table::name()->System_Account);
		sleep(2);
		//execute pagination
		$this->buildPagination($oSelect);
		
	}
	
	public function ajaxformAction() {
		
		$oForm = new Form_Test();
		//check if post
		if($this->_request->isPost()){
			//if form is valid
			if($oForm->isValid($this->_request->getPost())){
				
			}
		}
		
		//pass form object to ajax response method to sent response to front end
		$this->setAjaxResponse($oForm);
		
	}

}


