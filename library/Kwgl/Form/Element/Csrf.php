<?php
/**
 * Description of Csrf
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Form
 * @subpackage Element
 * @uses Kwgl_Validate_Csrf
 */
class Kwgl_Form_Element_Csrf extends Zend_Form_Element_Hash {

	/**
     * Initialize CSRF token in session
     *
     * @return void
     */
	public function initCsrfToken() {
		$oSession = $this->getSession();
		$oSession->setExpirationSeconds($this->getTimeout());
		$oSession->hash = $this->getHash();
	}

	/**
     * Initialize CSRF validator
     *
     * Creates Session namespace, and initializes CSRF token in session.
     * Additionally, adds validator for validating CSRF token.
     *
     * @return Zend_Form_Element_Hash
     */
    public function initCsrfValidator() {

		$oSession = $this->getSession();

		if (isset($oSession->hash)) {
			$rightHash = $oSession->hash;
		} else {
			$rightHash = null;
		}

		$this->addValidator('Csrf', true, array($this, $rightHash));
		return $this;
    }

	/**
	 *
	 */
	public function expireCsrfSession () {
		$oSession = $this->getSession();
        if (isset($oSession->hash)) {
            unset($oSession->hash);
        }
	}

}