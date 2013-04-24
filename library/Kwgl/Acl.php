<?php
/**
 * ACL Handler (stored via database)
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Acl
 * @uses Zend_Acl
 */
class Kwgl_Acl extends Zend_Acl {

	/**
	 * Singleton instance
	 *
	 * @var Kwgl_Acl
	 */
	protected static $_oInstance = null;

	/**
	 * Static ACL Cache Usage Flag
	 *
	 * @var boolean
	 */
	protected static $_bUseCache = false;

	const PERMISSION_TYPE_ALLOW = 'allow';
	const PERMISSION_TYPE_DENY = 'deny';

	const RESOURCE_TYPE_PAGE = 'page';
	const RESOURCE_TYPE_MODEL = 'model';

	/**
	 * Singleton pattern implementation makes new unavailable.
	 * Initialises Roles, Resources and Privileges to be used by Zend_Acl.
	 *
	 * @return void
	 */
	protected function __construct() {

		self::$_bUseCache = (Kwgl_Config::get(array('mode', 'cache', 'acl')) == 1);

		$this->_initRoles();
		$this->_initResources();
		$this->_initPermissions();
	}

	/**
	 * Returns an instance of Kwgl_Acl. Singleton pattern implementation.
	 *
	 * @return Kwgl_Acl
	 */
	public static function getInstance () {
		if (is_null(self::$_oInstance)) {
			self::$_oInstance = new self();
		}
		return self::$_oInstance;
	}

	/**
	 * Wrapper for Zend_Acl's isAllowed method.
	 *
	 * <code>
	 * Eg:
	 * $bAllowed = Kwgl_Acl::allowed('guest', 'kwgldev');
	 * </code>
	 *
	 * @param string $sRole
	 * @param string $sResource
	 * @param string $sPrivilege
	 * @return boolean
	 */
	public static function allowed ($sRole = null, $sResource = null, $sPrivilege = null) {
		return self::getInstance()->isAllowed($sRole, $sResource, $sPrivilege);
	}

	/**
	 * Special check of Page-type Resources (Module/Controller/Action). Attempts to degrade gracefully.
	 *
	 * <code>
	 * Eg:
	 * Kwgl_Acl::allowedForPage($sRole, $sModuleName, $sControllerName, $sActionName);
	 * </code>
	 *
	 * @param string $sRole
	 * @param string $sModuleName
	 * @param string $sControllerName
	 * @param string $sActionName
	 * @return boolean
	 */
	public static function allowedForPage ($sRole, $sModuleName, $sControllerName = null, $sActionName = null) {

		// Exceptions
		// Users should always have access to the Default Error Controller
//		if ($sModuleName == 'default' && $sControllerName == 'error') {
		if ($sControllerName == 'error') {
			return true;
		}

		// Build Resource Combinations (from most restrictive to least restrictive)
		$aResourceCombination = array();
		if (!is_null($sActionName)) {
			$aResourceCombination[] = $sModuleName . '-' . $sControllerName . '-' . $sActionName;
		}

		if (!is_null($sControllerName)) {
			$aResourceCombination[] = $sModuleName . '-' . $sControllerName;
		}

		// XHR Check to get fallback Controller Name to degrade gracefully
		if (!is_null($sControllerName) && stristr($sControllerName, 'xhr_')) {
			$sControllerName = str_replace('xhr_', '', $sControllerName);

			if (!is_null($sActionName)) {
				$aResourceCombination[] = $sModuleName . '-' . $sControllerName . '-' . $sActionName;
			}

			$aResourceCombination[] = $sModuleName . '-' . $sControllerName;
		}

		$aResourceCombination[] = $sModuleName;


		$oDaoResource = Kwgl_Db_Table::factory('System_Resource');

		foreach ($aResourceCombination as $sResource) {
			$aCondition = array(
				'type = ?' => Kwgl_Acl::RESOURCE_TYPE_PAGE,
				'name = ?' => $sResource,
			);
			$aResourceDetail = $oDaoResource->fetchRow($aCondition);

			if (empty($aResourceDetail)) {
				// Keep iterating
			} else {
				// Check if Permission has been allocated (whether allowed or denied)
				$bPermissionGranted = Kwgl_Acl::allowed($sRole, $sResource);
				return $bPermissionGranted;
			}
		}

		throw new Exception("Page Level Resource not found in Resource Registry.");
	}

	/**
	 * Initialises Roles to be used by Zend_Acl
	 *
	 * @return void
	 */
	protected function _initRoles () {

		$bFreshData = false;

		if (self::$_bUseCache) {
			// Identifier for Roles in the ACL Cache
			$sCacheIdentifier = 'acl_roles';

			$oCacheManager = Kwgl_Cache::getManager();
			$oAclCache = $oCacheManager->getCache('acl');

			if (($aRoleListing = $oAclCache->load($sCacheIdentifier)) === false) {
				// Not Cached or Expired

				$bFreshData = true;
			}
		} else {
			$bFreshData = true;
		}

		if ($bFreshData) {
			// Get Roles from the Database
			$oDaoRole = Kwgl_Db_Table::factory('System_Role'); /* @var $oDaoRole Dao_System_Role */
			//$aRoleListing = $oDaoRole->fetchAll();
			$aRoleListing = $oDaoRole->getRoles();

			if (self::$_bUseCache) {
				$oAclCache->save($aRoleListing, $sCacheIdentifier);
			}
		}

		foreach ($aRoleListing as $aRoleDetail) {
			$sRoleName = $aRoleDetail['name'];
			if (is_null($aRoleDetail['parent'])) {
				// Add the Role if it hasn't been defined yet
				if (!$this->hasRole($sRoleName)) {
					$this->addRole(new Zend_Acl_Role($sRoleName));
				}
			} else {
				// Parent Role assigned
				$sRoleParentName = $aRoleDetail['parent'];
				// Add the Parent Role if the Parent Role hasn't been defined yet
				if (!$this->hasRole($sRoleParentName)) {
					$this->addRole(new Zend_Acl_Role($sRoleParentName));
				}
				$this->addRole(new Zend_Acl_Role($sRoleName), $sRoleParentName);
			}
		}
	}

	/**
	 * Initialises Resources to be used by Zend_Acl
	 *
	 * @return void
	 */
	protected function _initResources () {

		$bFreshData = false;

		if (self::$_bUseCache) {
			// Identifier for Roles in the ACL Cache
			$sCacheIdentifier = 'acl_resources';

			$oCacheManager = Kwgl_Cache::getManager();
			$oAclCache = $oCacheManager->getCache('acl');

			if (($aResourceListing = $oAclCache->load($sCacheIdentifier)) === false) {
				// Not Cached or Expired

				$bFreshData = true;
			}
		} else {
			$bFreshData = true;
		}

		if ($bFreshData) {
			// Get Resources from the Database
			$oDaoResource = Kwgl_Db_Table::factory('System_Resource'); /* @var $oDaoResource Dao_System_Resource */
			//$aResourceListing = $oDaoResource->fetchAll();
			$aResourceListing = $oDaoResource->getResources();

			if (self::$_bUseCache) {
				$oAclCache->save($aResourceListing, $sCacheIdentifier);
			}
		}

		foreach ($aResourceListing as $aResourceDetail) {
			$sResourceName = $aResourceDetail['name'];
			if (is_null($aResourceDetail['parent'])) {
				// Add the Resource if it hasn't been defined yet
				if (!$this->has($sResourceName)) {
					$this->addResource(new Zend_Acl_Resource($sResourceName));
				}
			} else {
				// Parent Resource assigned
				$sResourceParentName = $aResourceDetail['parent'];
				// Add the Parent Role if the Parent Role hasn't been defined yet
				if (!$this->has($sResourceParentName)) {
					$this->addResource(new Zend_Acl_Resource($sResourceParentName));
				}
				$this->addResource(new Zend_Acl_Resource($sResourceName), $sResourceParentName);
			}


		}
	}

	/**
	 * Initialises Permissions to be used by Zend_Acl
	 *
	 * @return void
	 */
	protected function _initPermissions () {

		$bFreshData = false;

		if (self::$_bUseCache) {
			// Identifier for Roles in the ACL Cache
			$sCacheIdentifier = 'acl_permissions';

			$oCacheManager = Kwgl_Cache::getManager();
			$oAclCache = $oCacheManager->getCache('acl');

			if (($aPermissionListing = $oAclCache->load($sCacheIdentifier)) === false) {
				// Not Cached or Expired

				$bFreshData = true;
			}
		} else {
			$bFreshData = true;
		}

		if ($bFreshData) {
			// Get Privileges from the Database
			$oDaoRoleResourcePrivilege = Kwgl_Db_Table::factory('System_Role_Resource_Privilege');
			//$aPermissionListing = $oDaoRoleResource->fetchAll();
			$aPermissionListing = $oDaoRoleResourcePrivilege->getPermissions();

			if (self::$_bUseCache) {
				$oAclCache->save($aPermissionListing, $sCacheIdentifier);
			}
		}

		foreach ($aPermissionListing as $aPermissionDetail) {
			$sRoleName = $aPermissionDetail['role_name'];
			$sResourceName = $aPermissionDetail['resource_name'];
			$sPrivilegeName = null;
			if (!is_null($aPermissionDetail['privilege_name'])) {
				$sPrivilegeName = $aPermissionDetail['privilege_name'];
			}
			$sPermissionType = $aPermissionDetail['permission'];

			// Check the Permission to see if you should allow or deny the Resource/Privilege to the Role
			switch ($sPermissionType) {
				case self::PERMISSION_TYPE_ALLOW:
					$this->allow($sRoleName, $sResourceName, $sPrivilegeName);
					break;
				case self::PERMISSION_TYPE_DENY:
					$this->deny($sRoleName, $sResourceName, $sPrivilegeName);
					break;
			}
		}
	}

}