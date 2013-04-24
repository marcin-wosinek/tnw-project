<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Model
 * @uses Zend_Controller_Front
 */
class Kwgl_Model {

	/**
	 *
	 * @var Zend_Controller_Request_Abstract
	 */
	protected $_oRequest = null;

	/**
	 *
	 * @var string
	 */
	protected $_sModule = 'default';

	/**
	 *
	 * @var string
	 */
	protected $_sController = 'index';

	/**
	 *
	 * @var string
	 */
	protected $_sAction = 'index';

	/**
	 *
	 * @var array
	 */
	protected $_aParameter = array();

	/**
	 *
	 * @var array
	 */
	protected $_aParameterKey = array();

	public function  __construct() {

		$oFrontController = Zend_Controller_Front::getInstance();
		$this->_oRequest = $oFrontController->getRequest();
		$this->_sModule = $this->_oRequest->getModuleName();
		$this->_sController = $this->_oRequest->getControllerName();
		$this->_sAction = $this->_oRequest->getActionName();
		$this->_aParameter = $this->_oRequest->getParams();
		$this->_aParameterKey = array_keys($this->_aParameter);

	}

}