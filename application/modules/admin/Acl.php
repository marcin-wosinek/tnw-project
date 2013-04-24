<?php
class Admin_Acl extends Zend_Acl
{
	private $_aACLExceptionsAllow = array();

	/**
	 * Function to Setup the Access Control List
	 */
	protected $_aRole = null;

    public function __construct(){
		
		$this->_aRole = array();
        $this->addRole(new Zend_Acl_Role('administrator'));
		
		$this->add(new Zend_Acl_Resource('dashboard'));

		$this->allow('administrator', 'dashboard');

    }

	public function getACLExceptions($sType = 'allow') {
		switch ($sType) {
			case 'deny':
				return $this->_aACLExceptionsDeny;
				break;
			case 'allow':
			default:
				return $this->_aACLExceptionsAllow;
				break;
		}
	}
}
?>