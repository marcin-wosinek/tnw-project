<?php
/**
 * Base Controller for all Controllers
 *
 * @author Udanthaya Pathirana <udanthaya@gmail.com>
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Controller
 */
class Kwgl_Controller_Action extends Zend_Controller_Action {

	/**
	 * Alias for the Request Object
	 *
	 * @var Zend_Controller_Request_Abstract
	 */
	protected $_oRequest = null;

	/**
	 * Contains the Parameters in the Request
	 *
	 * @var array
	 */
	protected $_aParameter = array();

	/**
	 * Contains the Parameter Keys in the Request
	 *
	 * @var array
	 */
	protected $_aParameterKey = array();

	/**
	 * Function to initialise necessary variables for use in dependant Controllers
	 *
	 * @author Jayawi Perera <jayawiperera@gmail.com>
	 * @return void
	 */
	function init() {

		//Execute init() function in Zend_Controller_Action
		parent::init();

		// Set up Request Object and Parameter Arrays for later use
		$this->_oRequest = $this->_request;
		$this->_aParameter = $this->_oRequest->getParams();
		$this->_aParameterKey = array_keys($this->_aParameter);
	}

	/**
	 * create pagination based on the data sent by the js
	 *
	 * @author Udantha Pathirana <udanthaya@gmail.com>
	 * @param Zend_Db_Select $oSelect
	 * @param boolean $bReturnResult whether to return result or print it
	 * @return array
	 */
	protected function buildPagination (Zend_Db_Select $oSelect, $bReturnResult = false) {

		$aResponse = array();

		// Build Sorting and Conditions from Request
		$aClauseColumnNames = $this->_oRequest->getParam('clause_column_name');
		$aClauseComparisonFunctions = $this->_oRequest->getParam('clause_comparison_function');
		$aClauseColumValues = $this->_oRequest->getParam('clause_column_value');

		$aClauseType = $this->_oRequest->getParam('clause_type');

		$iPageCurrent = $this->_oRequest->getParam('page_current');
		$iPageItemCount = $this->_oRequest->getParam('page_item_count');
		$iPageRange = $this->_oRequest->getParam('page_range');
		$sPageScrollingStyle = ucfirst($this->_oRequest->getParam('page_scrolling_style'));
		switch ($sPageScrollingStyle) {
			case 'All':
			case 'Elastic':
			case 'Jumping':
			case 'Sliding':
				break;
			default:
				$sPageScrollingStyle = 'Sliding';
				break;
		}

		$sSortingColumn = $this->_oRequest->getParam('sort_column');
		$sSortingOrder = $this->_oRequest->getParam('sort_order');

		// Add Conditions
		if (isset($aClauseColumnNames) && count($aClauseColumnNames) > 0) {
			foreach ($aClauseColumnNames as $mKey => $mColumn) {
				if ($aClauseColumValues[$mKey] != "") {
					//change value according to function
					$aClauseColumValues[$mKey] = (strtoupper($aClauseComparisonFunctions[$mKey]) == 'LIKE') ? '%' . $aClauseColumValues[$mKey] . '%' : $aClauseColumValues[$mKey];

					// Add Custom Filter Clauses
					switch (strtolower($aClauseType[$mKey])) {
						case 'having':
							$oSelect->having("$aClauseColumnNames[$mKey] $aClauseComparisonFunctions[$mKey] ?",  $aClauseColumValues[$mKey]);
							break;
						case 'where':
						default:
							$oSelect->where("$aClauseColumnNames[$mKey] $aClauseComparisonFunctions[$mKey] ?",  $aClauseColumValues[$mKey]);
							break;
					}

				}
			}
		}

		// Add Sorting
		if (isset($sSortingColumn) && $sSortingColumn != '') {
			$oSelect->order(array(htmlspecialchars($sSortingColumn, ENT_QUOTES) . ' ' . htmlspecialchars($sSortingOrder, ENT_QUOTES)));
		}

		// Create Pagination Object
		$oPaginatorAdapter = new Zend_Paginator_Adapter_DbSelect($oSelect);
		$oPaginator = new Zend_Paginator($oPaginatorAdapter);
		$oPaginator->setDefaultScrollingStyle($sPageScrollingStyle);
		$oPaginator->setCurrentPageNumber($iPageCurrent);
		$oPaginator->setItemCountPerPage($iPageItemCount);
		$oPaginator->setPageRange($iPageRange);

		// Prepare Results
		$aItems = array();
		foreach ($oPaginator->getCurrentItems() as $aClauseColumValues) {
			$aItems[] = $aClauseColumValues;
		}
		$oPages = $oPaginator->getPages();

		//set result array
		$aResponse['__paginationData'] = $aItems;
		$aResponse['__paginationMetaData'] = array(
			'pageCount' => $oPages->pageCount,
			'first' => $oPages->first,
			'current' => $oPages->current,
			'last' => $oPages->last,
			'next' => (isset($oPages->next) ? $oPages->next : null),
			'previous' => (isset($oPages->previous) ? $oPages->previous : null),
			'pagesInRange' => $oPages->pagesInRange,
		);

		if ($bReturnResult === true) {
			return $aResponse;
		} else {
			$this->setAjaxResponse($aResponse);
		}
	}

	/**
	 * set error as ajax response
	 *
	 * @author Udantha Pathirana <udanthaya@gmail.com>
	 * @param mix $mError
	 */
	protected function setAjaxError($mError) {
		$this->_helper->viewRenderer->setNoRender(); //stop rendering the view
		$this->_helper->layout()->disableLayout(); //disable the layout
		//set error
		Kwgl_Ajax::setError($mError);
	}

	/**
	 * set result as an ajax response
	 *
	 * @author Udantha Pathirana <udanthaya@gmail.com>
	 * @param mix $mResponse can be an array of results or Zend form object
	 */
	protected function setAjaxResponse($mResponse) {

		$this->_helper->viewRenderer->setNoRender(); //stop rendering the view
		$this->_helper->layout()->disableLayout(); //disable the layout

		//if response is a form object, process form
		if ($mResponse instanceof Kwgl_Form) {

			$aResp = array();
			//if form is valid pass true, else pass the error messages

			if ($mResponse->hasBeenValidated()) {
				if ($mResponse->isErrors()) {
					$aResp = array('__FormResponse' => $mResponse->getMessages());
				} else {
					$aResp = array('__FormResponse' => true);
				}
			} else {
				$this->setAjaxError("Your form has not been processed.");
			}

			//pass custom errors
			$aCustomErrors = $mResponse->getErrorMessages();
			if(count($aCustomErrors)>0){
				$this->setAjaxError($aCustomErrors);
			}
			//send form values back
			$aResp['__FormResponseValues'] = array();
			$aEls = $mResponse->getElements();
			foreach ($aEls as $aEl) {
				//if element is hash type, then change value to saved one
				if($aEl instanceof Zend_Form_Element_Hash){
					//overwrite with value in session
					$aEl->initCsrfToken();
					$sNewHash = $aEl->getHash();
					$aResp['__FormResponseValues'][$aEl->getName()] = $sNewHash;
				} else {
					$aResp['__FormResponseValues'][$aEl->getName()] = $aEl->getValue();
				}
			}

			$mResponse = $aResp;
		}
		//set response
		Kwgl_Ajax::setResponse($mResponse);
	}

}