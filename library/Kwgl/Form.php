<?php
/**
 * Provides Form functionality.
 *
 * @author Darshan Wijekoon <darshanchaturanga@gmail.com>
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Form
 */
class Kwgl_Form extends Zend_Form {

	const CONTEXT_CREATE = 'create';
	const CONTEXT_UPDATE = 'update';
	const CONTEXT_DELETE = 'delete';

	/**
	 * Defines what context the Form should be used in.
	 * For example, can be used to indicate contexts such as create, update, delete or as a section of the whole.
	 *
	 * @var string
	 */
	protected $_sContext = 'default';

	/**
	 * Identifier for the Form Element.
	 *
	 * @var string
	 */
	protected $_sId = '';

	/**
	 * Specifies which HTTP method will be used to submit the form data set.
	 * Possible values are 'get' and 'post'.
	 *
	 * @var string
	 */
	protected $_sMethod = 'post';

	/**
	 * Specifies where to direct the form submission to for processing.
	 *
	 * @var string
	 */
	protected $_sAction = '';

	/**
	 * Specifies the content type used to submit the form to the server (when method is 'post').
	 * Possible values are 'application/x-www-form-urlencoded' and 'multipart/form-data'
	 *
	 * @var string
	 */
	protected $_sEnctype = self::ENCTYPE_URLENCODED;

	/**
	 * Location of the View Script used to Render this Zend Form
	 *
	 * @var string
	 */
	protected $_sViewScript = '';

	/**
	 * Additional Data provided for use by the Form
	 *
	 * @var array
	 */
	protected $_aParameters = null;

	/**
	 * ?
	 *
	 * @var array
	 */
	protected $_aDefaultFilters = array();

	/**
	 * Indicates whether a Zend_Form_Element_Hash should be used in the form to guard against CSRF Attacks.
	 *
	 * @var boolean
	 */
	private $_bUseCsrfProtection = true;

	/**
	 * Indicates whether the form has been validated using isValid or isValidPartial
	 *
	 * @var boolean
	 */
	protected $_bHasBeenValidated = false;

	/**
	 *
	 * @param string $sContext
	 * @param array $aAttributes
	 * @param array $aParameters
	 */
	public function __construct ($sContext = null, $aAttributes = null, $aParameters = null){

		if (!is_null($sContext)) {
			$this->_sContext = $sContext;
		}

		if (is_array($aAttributes)) {
			if (isset($aAttributes['id']) && !is_null($aAttributes['id'])) {
				$this->_sId = $aAttributes['id'];
			}
			if (isset($aAttributes['method']) && !is_null($aAttributes['method'])) {
				$this->_sMethod = $aAttributes['method'];
			}
			if (isset($aAttributes['action']) && !is_null($aAttributes['action'])) {
				$this->_sAction = $aAttributes['action'];
			}
			if (isset($aAttributes['enctype']) && !is_null($aAttributes['enctype'])) {
				$this->_sEnctype = $aAttributes['enctype'];
			}
		} else {
			// Use Default Values
		}

		if (is_array($aParameters)) {
			$this->_aParameters = $aParameters;
		}

		$this->_setupFormAttributes();
		$this->_initDefaultFilters();

		$this->addPrefixPath('Kwgl_Form_Element', 'Kwgl/Form/Element', 'element');
		$this->addElementPrefixPath('Kwgl_Validate', 'Kwgl/Validate', 'validate');
		$this->addElementPrefixPath('Kwgl_Filter', 'Kwgl/Filter', 'filter');

		parent::__construct();

		$this->_addCsrfProtection();
		$this->_removeUnusedDecorators();
		$this->_setElementFilters();

	}


	/**
	 * Returns whether the form has been validated using isValid or isValidPartial
	 *
	 * @return boolean
	 */
	public function hasBeenValidated () {
		return $this->_bHasBeenValidated;
	}

	/**
	 * Wrapper to track if the form has undergone a validation attempt
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function isValid($aData) {
		$bValid = parent::isValid($aData);
		$this->_bHasBeenValidated = true;
		return $bValid;
	}

	/**
	 * Wrappter to track if the form has undergone a partial validation attempt
	 *
	 * @param array $data
	 * @return type
	 */
	public function isValidPartial(array $data) {
		$bValidPartial = parent::isValidPartial($data);
		$this->_bHasBeenValidated = true;
		return $bValidPartial;
	}

	protected function _setupFormAttributes () {

		$this->setAction($this->_sAction)
				->setMethod($this->_sMethod)
				->setEnctype($this->_sEnctype)
				->setAttrib('id', $this->_sId);

	}

	/**
	 * remove unused default decorators
	 */
	private function _removeUnusedDecorators() {

		$aFormElements = $this->getElements();

		foreach($aFormElements as $oElement) {
			$oElement->removeDecorator('label');
			$oElement->removeDecorator('HtmlTag');
		}
	}

	/**
	 * Defines default set of element filters-HtmlEntities & StringTrim
	 */
	private function _initDefaultFilters() {

		$this->_aDefaultFilters = array(
			new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES)),
			new Zend_Filter_StringTrim()
		);
	}

	/**
	 * Sets default filters for elements where filters have not been provided
	 */
	private function _setElementFilters() {

		 foreach($this->getElements() as $oElement) {
			$aFilters = $oElement->getFilters();
			if (empty($aFilters)) {
				$oElement->setFilters($this->_aDefaultFilters);
			}
        }
	}

	/**
	 * set path & options to form view script decorator
	 * @param string $sScriptPath
	 * @param array $aScriptVars
	 */
	protected function _setViewScriptDecorator($sScriptPath, $aScriptVars = array()) {

		$aOptions = array(
			'viewScript' => $sScriptPath
		);

		if (! empty($aScriptVars)) {
			$aOptions = array_merge($aOptions, $aScriptVars);
		}

		$this->setDecorators(array(array('ViewScript', $aOptions)));
	}

	/**
	 * Adds a hash element which provides protection from CSRF attacks on forms,
	 * ensuring the data is submitted by the user session that generated the form and not by a rogue script.
	 * Protection is achieved by adding a hash element to a form and verifying it when the form is submitted.
	 */
	private function _addCsrfProtection() {
		if ($this->_bUseCsrfProtection) {
			$sContext = $this->_sContext;
			$sId = $this->getAttrib('id');
			$sSalt = $sContext . '_' . $sId;
			$this->addElement('csrf', 'csrf_hash', array('salt' => $sSalt, 'ignore' => true));
		}
	}

	/**
	 * Adds CSRF Protection to the Form (Form uses CSRF Protection by default)
	 * Has to be called in the init() function of the Form Definition
	 */
	public function addCsrfProtection () {
		$this->_bUseCsrfProtection = true;
	}

	/**
	 * Removes CSRF Protection from the Form (Form uses CSRF Protection by default)
	 * Has to be called in the init() function of the Form Definition
	 */
	public function removeCsrfProtection () {
		$this->_bUseCsrfProtection = false;
	}

}