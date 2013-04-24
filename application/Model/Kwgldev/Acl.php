<?php
/**
 * Handles all ACL Management / Interface related operations
 * This Model is to be used only in the Kwgldev Module
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category KwglDev
 * @package Model
 * @subpackage Kwgldev_Acl
 */
class Model_Kwgldev_Acl extends Kwgl_Model {

	const PERMISSION_ALLOWED = 'allowed';
	const PERMISSION_DENIED = 'denied';
	const PERMISSION_UNDEFINED = 'undefined';

	/**
	 * Returns list of Roles in the Database with their Parents
	 *
	 * @return array
	 */
	public static function getRolesWithParents () {

		$oDaoRoles = Kwgl_Db_Table::factory('System_Role'); /* @var $oDaoRoles Dao_System_Role */

		$aRoles = $oDaoRoles->getRoles();

		return $aRoles;
	}

	/**
	 * Returns list of Resources in the Database with their Parents
	 * Resources returned are all, of type page or of type model
	 *
	 * @param string $sType
	 * @return array
	 */
	public static function getResourcesWithParents ($sType = null) {

		$oDaoResources = Kwgl_Db_Table::factory('System_Resource'); /* @var $oDaoResources Dao_System_Resource */

		$aResources = $oDaoResources->getResources($sType);

		return $aResources;
	}

	/**
	 * Returns list of Privileges in the Database (Related to Resources)
	 *
	 * @return array
	 */
	public static function getPrivileges () {

		$oDaoPrivileges = Kwgl_Db_Table::factory('System_Resource_Privilege'); /* @var $oDaoPrivileges Dao_System_Resource_Privilege */

		$aPrivileges = $oDaoPrivileges->getPrivilegesWithResources();

		return $aPrivileges;

	}

	/**
	 * Returns Privileges for a specified Resource
	 *
	 * @param integer $iResourceId
	 * @return array
	 */
	public static function getPrivilegesForResource ($iResourceId) {

		$oDaoPrivileges = Kwgl_Db_Table::factory('System_Resource_Privilege');

		$aPrivileges = $oDaoPrivileges->getPrivilegesWithResources($iResourceId);

		return $aPrivileges;

	}

	/**
	 * Returns list of Permissions in the Database for the specified type of Resource
	 *
	 * @param string $sResourceType
	 * @return array
	 */
	public static function getPermissions ($sResourceType = null) {

		$oDaoPermissions = Kwgl_Db_Table::factory('System_Role_Resource_Privilege'); /* @var $oDaoPermissions Dao_System_Role_Resource_Privilege */

		switch ($sResourceType) {
			case 'page':
				$aPermissions = $oDaoPermissions->getPermissionsForPageResources();
				break;
			case 'model':
				$aPermissions = $oDaoPermissions->getPermissionsForModelResources();
				break;
			case null:
				break;
		}

		return $aPermissions;

	}

	/**
	 * Retruns a breakdown of all Modules/Controllers and Actions in the site and their accessiblity to the various roles.
	 *
	 * @return array
	 */
	public static function getPageOverview () {

		$aContent = array();

		$aOverview = Kwgl_Utility_Mvc::getStructure();

		$oDaoRoles = Kwgl_Db_Table::factory('System_Role');
		$aRoles = $oDaoRoles->fetchPairs(array('id', 'name'));
		$aContent['roles'] = $aRoles;

		foreach ($aOverview as $sModuleToCheck => $aControllersToCheck) {
			$aModulePermissions = array();

			foreach ($aRoles as $iRoleId => $sRoleName) {
				$bPermission = null;
				try {
					$bPermission = Kwgl_Acl::allowedForPage($sRoleName, $sModuleToCheck);
				} catch (Exception $oException) {
					if ($oException->getMessage() != "Page Level Resource not found in Resource Registry.") {
						throw $oException;
					}
				}
				if ($bPermission === true) {
					$mPermission = self::PERMISSION_ALLOWED;
				} elseif ($bPermission === false) {
					$mPermission = self::PERMISSION_DENIED;
				} else {
					$mPermission = self::PERMISSION_UNDEFINED;
				}
				$aModulePermissions[$sRoleName] = $mPermission;
			}
			$aOverview[$sModuleToCheck]['acl-permissions'] = $aModulePermissions;

			foreach ($aControllersToCheck as $sControllerToCheck => $aActionsToCheck) {
				$aControllerPermissions = array();

				foreach ($aRoles as $iRoleId => $sRoleName) {
					$bPermission = null;
					try {
						$bPermission = Kwgl_Acl::allowedForPage($sRoleName, $sModuleToCheck, $sControllerToCheck);
					} catch (Exception $oException) {
						if ($oException->getMessage() != "Page Level Resource not found in Resource Registry.") {
							throw $oException;
						}
					}
					if ($bPermission === true) {
						$mPermission = self::PERMISSION_ALLOWED;
					} elseif ($bPermission === false) {
						$mPermission = self::PERMISSION_DENIED;
					} else {
						$mPermission = self::PERMISSION_UNDEFINED;
					}
					$aControllerPermissions[$sRoleName] = $mPermission;
				}
				$aOverview[$sModuleToCheck][$sControllerToCheck]['acl-permissions'] = $aControllerPermissions;

				foreach ($aActionsToCheck as $sActionToCheck => $aPermissions) {
					$aActionPermissions = array();

					foreach ($aRoles as $iRoleId => $sRoleName) {
						$bPermission = null;
						try {
							$bPermission = Kwgl_Acl::allowedForPage($sRoleName, $sModuleToCheck, $sControllerToCheck, $sActionToCheck);
						} catch (Exception $oException) {
							if ($oException->getMessage() != "Page Level Resource not found in Resource Registry.") {
								throw $oException;
							}
						}
						if ($bPermission === true) {
							$mPermission = self::PERMISSION_ALLOWED;
						} elseif ($bPermission === false) {
							$mPermission = self::PERMISSION_DENIED;
						} else {
							$mPermission = self::PERMISSION_UNDEFINED;
						}
						$aActionPermissions[$sRoleName] = $mPermission;
					}
					$aOverview[$sModuleToCheck][$sControllerToCheck][$sActionToCheck]['acl-permissions'] = $aActionPermissions;
				}
			}

		}



		$aContent['overview'] = $aOverview;

		return $aContent;

	}

	/**
	 * Returns a breakdown of all Resources designated as type Model and their accessibility to the various roles.
	 *
	 * @return array
	 */
	public static function getModelOverview () {

		$aContent = array();

		$aOverview = array();

		// Get All Model Resources
		$oDaoPrivileges = Kwgl_Db_Table::factory('System_Resource_Privilege');
		$aModelResources = $oDaoPrivileges->getModelResourcesWithPrivileges();

		// Get All Roles
		$oDaoRoles = Kwgl_Db_Table::factory('System_Role');
		$aRoles = $oDaoRoles->fetchPairs(array('id', 'name'));
		$aContent['roles'] = $aRoles;

		// Iterate through all Model Resources
		foreach ($aModelResources as $mKey => $aModelResource) {
			$sResourceName = $aModelResource['resource_name'];
			$sPrivilegeName = $aModelResource['privilege_name'];

			$aResourcePrivilegePermissions = array();

			// Iterate through all Roles
			foreach ($aRoles as $iRoleId => $sRoleName) {
				$bPermission = null;

				// Check if the Role has access to the particular Resource/Privilege
				if (is_null($sPrivilegeName)) {
					$bPermission = Kwgl_Acl::allowed($sRoleName, $sResourceName);
				} else {
					$bPermission = Kwgl_Acl::allowed($sRoleName, $sResourceName, $sPrivilegeName);
				}

				if ($bPermission === true) {
					$mPermission = self::PERMISSION_ALLOWED;
				} elseif ($bPermission === false) {
					$mPermission = self::PERMISSION_DENIED;
				} else {
					$mPermission = self::PERMISSION_UNDEFINED;
				}

				$aResourcePrivilegePermissions[$sRoleName] = $mPermission;
			}

			if (is_null($sPrivilegeName)) {
				$aOverview[$sResourceName]['acl-permissions'] = $aResourcePrivilegePermissions;
			} else {
				$aOverview[$sResourceName][$sPrivilegeName]['acl-permissions'] = $aResourcePrivilegePermissions;
			}
		}

		$aContent['overview'] = $aOverview;

		return $aContent;
	}

	/**
	 * Handles CRUD operations for Roles
	 *
	 * @return array
	 */
	public function manageRoles () {

		$aContent = array();
		$bOperationCreate = false;
		$bOperationUpdate = false;
		$bOperationDelete = false;
		$bOperationList = false;

		$oDaoRoles = Kwgl_Db_Table::factory('System_Role'); /* @var $oDaoRoles Dao_System_Role */

		$sOperation = 'list';
		if (in_array('operation', $this->_aParameterKey)) {
			$sOperation = $this->_aParameter['operation'];
		}

		switch ($sOperation) {
			case 'create':
				$bOperationCreate = true;
				$sDisplay = 'create';
				break;
			case 'update':
				$bOperationUpdate = true;
				$sDisplay = 'update';
				break;
			case 'delete':
				$bOperationDelete = true;
				$sDisplay = 'delete';
				break;
			case 'list':
			default:
				$bOperationList = true;
				$sDisplay = 'list';
				break;
		}

		if ($bOperationCreate) {
			$aRoleDetails = $oDaoRoles->createRow();
		}

		if ($bOperationUpdate || $bOperationDelete) {
			// Check if ID was provided
			if (in_array('id', $this->_aParameterKey)) {
				$iRoleId = $this->_aParameter['id'];
			} else {
				// ID not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/roles/';
				return $aContent;
			}

			// Check if Role exists
			$aRoleDetails = $oDaoRoles->fetchRow(array('id = ?' => $iRoleId));
			if (empty($aRoleDetails)) {
				// Role not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/roles/';
				return $aContent;
			}
		}

		switch ($sOperation) {
			case 'create':
				$oFormRoles = new Form_Kwgldev_SysRoles(Form_Kwgldev_SysRoles::CONTEXT_CREATE, array('id' => 'iFormRolesCreate'));
				break;
			case 'update':
				$oFormRoles = new Form_Kwgldev_SysRoles(Form_Kwgldev_SysRoles::CONTEXT_UPDATE, array('id' => 'iFormRolesUpdate'));
				$oFormRoles->getElement('textRoleName')->setValue($aRoleDetails['name']);
				$oFormRoles->getElement('selectRoleParent')->setValue($aRoleDetails['id_parent']);
				break;
			case 'delete':
				$oFormRoles = new Form_Kwgldev_SysRoles(Form_Kwgldev_SysRoles::CONTEXT_DELETE, array('id' => 'iFormRolesDelete'));
				$oFormRoles->getElement('textRoleName')->setValue($aRoleDetails['name']);
				$oFormRoles->getElement('selectRoleParent')->setValue($aRoleDetails['id_parent']);
				break;
			case 'list':
				$oFormRoles = null;
				$aRoles = Model_Kwgldev_Acl::getRolesWithParents();
				$aContent['roles'] = $aRoles;
				break;
		}

		if ($this->_oRequest->isPost()) {

			if ($oFormRoles->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oFormRoles->getValues();

				$aRoleDetails['name'] = $aFormValues['textRoleName'];
				$aRoleDetails['id_parent'] = ($aFormValues['selectRoleParent'] == 0) ? null : $aFormValues['selectRoleParent'];

				if ($bOperationDelete) {
					$aRoleDetails->delete();
				} else {
					$aRoleDetails->save();
				}

				$aContent['redirect'] = '/kwgldev/acl/roles/';

			} else {
				// Do nothing
			}

		}

		$aContent['display'] = $sDisplay;
		$aContent['form'] = $oFormRoles;

		return $aContent;
	}

	/**
	 * Handles CRUD operations for Resources of type Page
	 *
	 * @return array
	 */
	public function managePageResources () {
		$aContent = array();
		$bOperationCreate = false;
		$bOperationDelete = false;
		$bOperationList = false;

		$oDaoResources = Kwgl_Db_Table::factory('System_Resource'); /* @var $oDaoResources Dao_System_Resource */

		$sOperation = 'list';
		if (in_array('operation', $this->_aParameterKey)) {
			$sOperation = $this->_aParameter['operation'];
		}

		switch ($sOperation) {
			case 'create':
				$bOperationCreate = true;
				$sDisplay = 'create';
				break;
			case 'delete':
				$bOperationDelete = true;
				$sDisplay = 'delete';
				break;
			case 'list':
			default:
				$bOperationList = true;
				$sDisplay = 'list';
				break;
		}

		if ($bOperationCreate) {
			$aResourceDetails = $oDaoResources->createRow();
		}

		if ($bOperationDelete) {
			// Check if ID was provided
			if (in_array('id', $this->_aParameterKey)) {
				$iResourceId = $this->_aParameter['id'];
			} else {
				// ID not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/pageresources/';
				return $aContent;
			}

			// Check if Resource exists
			$aResourceDetails = $oDaoResources->fetchRow(array('id = ?' => $iResourceId));
			if (empty($aResourceDetails)) {
				// Resource not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/pageresources/';
				return $aContent;
			}
		}

		switch ($sOperation) {
			case 'create':
				$oFormResources = new Form_Kwgldev_SysResources(Form_Kwgldev_SysResources::CONTEXT_PAGE_CREATE, array('id' => 'iFormPageResourcesCreate'));
				break;
			case 'delete':
				$oFormResources = new Form_Kwgldev_SysResources(Form_Kwgldev_SysResources::CONTEXT_PAGE_DELETE, array('id' => 'iFormPageResourcesDelete'));
				$oFormResources->getElement('textResourceName')->setValue($aResourceDetails['name']);
				break;
			case 'list':
				$oFormResources = null;
				$aResources = Model_Kwgldev_Acl::getResourcesWithParents('page');
				$aContent['resources'] = $aResources;
				break;
		}

		if ($this->_oRequest->isPost()) {

			if ($oFormResources->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oFormResources->getValues();

				$aResourceDetails['name'] = $aFormValues['textResourceName'];
				$aResourceDetails['type'] = Dao_System_Resource::TYPE_PAGE;
				$aResourceDetails['id_parent'] = null;

				if ($bOperationDelete) {
					$aResourceDetails->delete();
				} else {
					$mPrimaryKey = $aResourceDetails->save();

					// If Creating a Resource, create a corresponding Privilege
					if ($bOperationCreate) {
						$oDaoPrivilege = Kwgl_Db_Table::factory('System_Resource_Privilege');
						if (is_array($mPrimaryKey)) {
							throw new Exception("Multiple Primary Keys encountered when attempting to create Privielges corresponding to Resource that has been just created.");
						} else {
							$aPrivilegeDetails = $oDaoPrivilege->createRow();
							$aPrivilegeDetails['name'] = null;
							$aPrivilegeDetails['id_sys_resource'] = $mPrimaryKey;
							$aPrivilegeDetails->save();
						}
					}
				}

				$aContent['redirect'] = '/kwgldev/acl/pageresources/';

			} else {
				// Do nothing
			}

		}

		$aContent['display'] = $sDisplay;
		$aContent['form'] = $oFormResources;

		return $aContent;
	}

	/**
	 * Handles CRUD operations for Resources of type Model
	 *
	 * @return array
	 */
	public function manageModelResources () {
		$aContent = array();
		$bOperationCreate = false;
		$bOperationUpdate = false;
		$bOperationDelete = false;
		$bOperationList = false;

		$oDaoResources = Kwgl_Db_Table::factory('System_Resource'); /* @var $oDaoResources Dao_System_Resource */

		$sOperation = 'list';
		if (in_array('operation', $this->_aParameterKey)) {
			$sOperation = $this->_aParameter['operation'];
		}

		switch ($sOperation) {
			case 'create':
				$bOperationCreate = true;
				$sDisplay = 'create';
				break;
			case 'update':
				$bOperationUpdate = true;
				$sDisplay = 'update';
				break;
			case 'delete':
				$bOperationDelete = true;
				$sDisplay = 'delete';
				break;
			case 'list':
			default:
				$bOperationList = true;
				$sDisplay = 'list';
				break;
		}

		if ($bOperationCreate) {
			$aResourceDetails = $oDaoResources->createRow();
		}

		if ($bOperationUpdate || $bOperationDelete) {
			// Check if ID was provided
			if (in_array('id', $this->_aParameterKey)) {
				$iResourceId = $this->_aParameter['id'];
			} else {
				// ID not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/modelresources/';
				return $aContent;
			}

			// Check if Resource exists
			$aResourceDetails = $oDaoResources->fetchRow(array('id = ?' => $iResourceId));
			if (empty($aResourceDetails)) {
				// Resource not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/modelresources/';
				return $aContent;
			}
		}

		switch ($sOperation) {
			case 'create':
				$oFormResources = new Form_Kwgldev_SysResources(Form_Kwgldev_SysResources::CONTEXT_MODEL_CREATE, array('id' => 'iFormModelResourcesCreate'));
				break;
			case 'update':
				$oFormResources = new Form_Kwgldev_SysResources(Form_Kwgldev_SysResources::CONTEXT_MODEL_UPDATE, array('id' => 'iFormModelResourcesUpdate'), array('resource_id' => $iResourceId));
				$oFormResources->getElement('textResourceName')->setValue($aResourceDetails['name']);
				break;
			case 'delete':
				$oFormResources = new Form_Kwgldev_SysResources(Form_Kwgldev_SysResources::CONTEXT_MODEL_DELETE, array('id' => 'iFormModelResourcesDelete'));
				$oFormResources->getElement('textResourceName')->setValue($aResourceDetails['name']);
				break;
			case 'list':
				$oFormResources = null;
				$aResources = Model_Kwgldev_Acl::getResourcesWithParents('model');
				$aContent['resources'] = $aResources;
				break;
		}

		if ($this->_oRequest->isPost()) {

			if ($oFormResources->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oFormResources->getValues();

				$aResourceDetails['name'] = $aFormValues['textResourceName'];
				$aResourceDetails['type'] = Dao_System_Resource::TYPE_MODEL;
				$aResourceDetails['id_parent'] = ($aFormValues['selectResourceParent'] == 0) ? null : $aFormValues['selectResourceParent'];

				if ($bOperationDelete) {
					$aResourceDetails->delete();
				} else {
					$mPrimaryKey = $aResourceDetails->save();

					// If Creating a Resource, create a corresponding Privilege
					if ($bOperationCreate) {
						$oDaoPrivilege = Kwgl_Db_Table::factory('System_Resource_Privilege');
						if (is_array($mPrimaryKey)) {
							throw new Exception("Multiple Primary Keys encountered when attempting to create Privielges corresponding to Resource that has been just created.");
						} else {
							$aPrivilegeDetails = $oDaoPrivilege->createRow();
							$aPrivilegeDetails['name'] = null;
							$aPrivilegeDetails['id_sys_resource'] = $mPrimaryKey;
							$aPrivilegeDetails->save();
						}
					}
				}

				$aContent['redirect'] = '/kwgldev/acl/modelresources/';

			} else {
				// Do nothing
			}

		}

		$aContent['display'] = $sDisplay;
		$aContent['form'] = $oFormResources;

		return $aContent;
	}

	/**
	 * Handles CRUD operations for Privileges
	 *
	 * @return array
	 */
	public function managePrivileges () {
		$aContent = array();
		$bOperationCreate = false;
		$bOperationUpdate = false;
		$bOperationDelete = false;
		$bOperationList = false;

		if (in_array('resourceid', $this->_aParameterKey)) {
			$iResourceId = $this->_aParameter['resourceid'];
		} else {
			$aContent['redirect'] = '/kwgldev/acl/modelresources/';
			return $aContent;
		}

		$oDaoResources = Kwgl_Db_Table::factory('System_Resource'); /* @var $oDaoResources Dao_System_Resource */
		$aResourceDetails = $oDaoResources->fetchDetail(null, array('id = ?' => $iResourceId));
		if (empty($aResourceDetails)) {
			$aContent['redirect'] = '/kwgldev/acl/modelresources/';
			return $aContent;
		}
		$aContent['resource'] = $aResourceDetails;

		$oDaoPrivileges = Kwgl_Db_Table::factory('System_Resource_Privilege'); /* @var $oDaoPrivileges Dao_System_Resource_Privilege */

		$sOperation = 'list';
		if (in_array('operation', $this->_aParameterKey)) {
			$sOperation = $this->_aParameter['operation'];
		}

		switch ($sOperation) {
			case 'create':
				$bOperationCreate = true;
				$sDisplay = 'create';
				break;
			case 'update':
				$bOperationUpdate = true;
				$sDisplay = 'update';
				break;
			case 'delete':
				$bOperationDelete = true;
				$sDisplay = 'delete';
				break;
			case 'list':
			default:
				$bOperationList = true;
				$sDisplay = 'list';
				break;
		}

		if ($bOperationCreate) {
			$aPrivilegeDetails = $oDaoPrivileges->createRow();
		}

		if ($bOperationUpdate || $bOperationDelete) {
			// Check if ID was provided
			if (in_array('id', $this->_aParameterKey)) {
				$iPrivilegeId = $this->_aParameter['id'];
			} else {
				// ID not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/privileges/resourceid/' . $iResourceId . '/';
				return $aContent;
			}

			// Check if Privilege exists
			$aPrivilegeDetails = $oDaoPrivileges->fetchRow(array('id = ?' => $iPrivilegeId));
			if (empty($aPrivilegeDetails)) {
				// Resource not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/privileges/resourceid/' . $iResourceId . '/';
				return $aContent;
			}
		}

		switch ($sOperation) {
			case 'create':
				$oFormPrivileges = new Form_Kwgldev_SysPrivileges(Form_Kwgldev_SysPrivileges::CONTEXT_CREATE, array('id' => 'iFormPrivilegeCreate'), array('resource-id' => $iResourceId));
				$oFormPrivileges->getElement('selectResource')->setValue($iResourceId);
				break;
			case 'update':
				$oFormPrivileges = new Form_Kwgldev_SysPrivileges(Form_Kwgldev_SysPrivileges::CONTEXT_UPDATE, array('id' => 'iFormPrivilegeUpdate'), array('resource-id' => $iResourceId));
				$oFormPrivileges->getElement('selectResource')->setValue($iResourceId);
				$oFormPrivileges->getElement('textPrivilegeName')->setValue($aPrivilegeDetails['name']);
				break;
			case 'delete':
				$oFormPrivileges = new Form_Kwgldev_SysPrivileges(Form_Kwgldev_SysPrivileges::CONTEXT_DELETE, array('id' => 'iFormPrivilegeDelete'), array('resource-id' => $iResourceId));
				$oFormPrivileges->getElement('selectResource')->setValue($iResourceId);
				$oFormPrivileges->getElement('textPrivilegeName')->setValue($aPrivilegeDetails['name']);
				break;
			case 'list':
				$oFormPrivileges = null;
				$aPrivileges = Model_Kwgldev_Acl::getPrivilegesForResource($iResourceId);
				$aContent['privileges'] = $aPrivileges;
				break;
		}

		if ($this->_oRequest->isPost()) {

			if ($oFormPrivileges->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oFormPrivileges->getValues();
				$aData = array();

				$aPrivilegeDetails['name'] = $aFormValues['textPrivilegeName'];
				$aPrivilegeDetails['id_sys_resource'] = $iResourceId;

				if ($bOperationDelete) {
					$aPrivilegeDetails->delete();
				} else {
					$aPrivilegeDetails->save();
				}

				$aContent['redirect'] = '/kwgldev/acl/privileges/resourceid/' . $iResourceId . '/';
			} else {
				// Do nothing
			}

		}

		$aContent['display'] = $sDisplay;
		$aContent['form'] = $oFormPrivileges;

		return $aContent;
	}

	/**
	 * Handles CRUD operations for Permissions (Role accessibility to Resource/Privileges
	 * @return array
	 */
	public function managePermissions () {

		$aContent = array();

		$bOperationCreate = false;
		$bOperationUpdate = false;
		$bOperationDelete = false;
		$bOperationList = false;

		$oDaoPermissions = Kwgl_Db_Table::factory('System_Role_Resource_Privilege'); /* @var $oDaoPermissions Dao_System_Role_Resource_Privilege */

		$sOperation = 'list';
		if (in_array('operation', $this->_aParameterKey)) {
			$sOperation = $this->_aParameter['operation'];
		}

		switch ($sOperation) {
			case 'create':
				$bOperationCreate = true;
				$sDisplay = 'create';
				break;
			case 'update':
				$bOperationUpdate = true;
				$sDisplay = 'update';
				break;
			case 'delete':
				$bOperationDelete = true;
				$sDisplay = 'delete';
				break;
			case 'list':
			default:
				$bOperationList = true;
				$sDisplay = 'list';
				break;
		}

		$sType = null;
		if (in_array('type', $this->_aParameterKey)) {
			$sType = $this->_aParameter['type'];
		}
		switch ($sType) {
			case 'page':
			case 'model':
				break;
			default:
				$sType = null;
				break;
		}

		if ($bOperationCreate) {
			$aPermissionDetails = $oDaoPermissions->createRow();
		}

		if ($bOperationUpdate || $bOperationDelete) {
			// Check if ID was provided
			if (in_array('id', $this->_aParameterKey)) {
				$iPermissionId = $this->_aParameter['id'];
			} else {
				// ID not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/permissions/';
				return $aContent;
			}

			// Check if Role exists
			$aPermissionDetails = $oDaoPermissions->fetchRow(array('id = ?' => $iPermissionId));
			if (empty($aPermissionDetails)) {
				// Permission not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/permissions/';
				return $aContent;
			}

			// Check if editing is allowed
			if ($aPermissionDetails['edit_allowed'] == 0) {
				// Editing of Permission is not allowed
				$aContent['display'] = 'edit-not-allowed';
				return $aContent;
			}

		}

		if ($bOperationCreate || $bOperationUpdate || $bOperationDelete) {
			if (is_null($sType)) {
				// Resource Type not found, return to List
				$aContent['redirect'] = '/kwgldev/acl/permissions/';
				return $aContent;
			}
		}

		switch ($sOperation) {
			case 'create':
				if ($sType == 'page') {
					$oFormPermissions = new Form_Kwgldev_SysPermissions(Form_Kwgldev_SysPermissions::CONTEXT_PAGE_CREATE, array('id' => 'iFormPagePermissionsCreate'));
				} elseif ($sType == 'model') {
					$oFormPermissions = new Form_Kwgldev_SysPermissions(Form_Kwgldev_SysPermissions::CONTEXT_MODEL_CREATE, array('id' => 'iFormModelPermissionsCreate'));
				} else {
					throw new Exception('Attempt to Create Permissions without a proper type.');
				}
				break;
			case 'update':
				if ($sType == 'page') {
					$oFormPermissions = new Form_Kwgldev_SysPermissions(Form_Kwgldev_SysPermissions::CONTEXT_PAGE_UPDATE, array('id' => 'iFormPagePermissionsUpdate'));
					$oFormPermissions->getElement('selectRole')->setValue($aPermissionDetails['id_sys_role']);
					$oFormPermissions->getElement('selectPrivilege')->setValue($aPermissionDetails['id_sys_resource_privilege']);
					$oFormPermissions->getElement('selectPermission')->setValue($aPermissionDetails['permission']);
				} elseif ($sType == 'model') {
					$oFormPermissions = new Form_Kwgldev_SysPermissions(Form_Kwgldev_SysPermissions::CONTEXT_MODEL_UPDATE, array('id' => 'iFormModelPermissionsUpdate'));
					$oFormPermissions->getElement('selectRole')->setValue($aPermissionDetails['id_sys_role']);
					$oFormPermissions->getElement('selectPrivilege')->setValue($aPermissionDetails['id_sys_resource_privilege']);
					$oFormPermissions->getElement('selectPermission')->setValue($aPermissionDetails['permission']);
				} else {
					throw new Exception('Attempt to Create Permissions without a proper type.');
				}

				break;
			case 'delete':
				if ($sType == 'page') {
					$oFormPermissions = new Form_Kwgldev_SysPermissions(Form_Kwgldev_SysPermissions::CONTEXT_PAGE_DELETE, array('id' => 'iFormPagePermissionsDelete'));
					$oFormPermissions->getElement('selectRole')->setValue($aPermissionDetails['id_sys_role']);
					$oFormPermissions->getElement('selectPrivilege')->setValue($aPermissionDetails['id_sys_resource_privilege']);
					$oFormPermissions->getElement('selectPermission')->setValue($aPermissionDetails['permission']);
				} elseif ($sType == 'model') {
					$oFormPermissions = new Form_Kwgldev_SysPermissions(Form_Kwgldev_SysPermissions::CONTEXT_MODEL_DELETE, array('id' => 'iFormModelPermissionsDelete'));
					$oFormPermissions->getElement('selectRole')->setValue($aPermissionDetails['id_sys_role']);
					$oFormPermissions->getElement('selectPrivilege')->setValue($aPermissionDetails['id_sys_resource_privilege']);
					$oFormPermissions->getElement('selectPermission')->setValue($aPermissionDetails['permission']);
				} else {
					throw new Exception('Attempt to Create Permissions without a proper type.');
				}
				break;
			case 'list':
				$oFormPermissions = null;
				$aPagePermissions = self::getPermissions('page');
				$aContent['page-permissions'] = $aPagePermissions;
				$aModelPermissions = self::getPermissions('model');
				$aContent['model-permissions'] = $aModelPermissions;
				break;
		}

		if ($this->_oRequest->isPost()) {

			if ($oFormPermissions->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oFormPermissions->getValues();

				$aPermissionDetails['id_sys_role'] = $aFormValues['selectRole'];
				$aPermissionDetails['id_sys_resource_privilege'] = $aFormValues['selectPrivilege'];
				$aPermissionDetails['permission'] = $aFormValues['selectPermission'];

				if ($bOperationDelete) {
					$aPermissionDetails->delete();
					$aContent['redirect'] = '/kwgldev/acl/permissions/';
				} elseif ($bOperationUpdate) {
					$aPermissionDetails->save();
					$aContent['redirect'] = '/kwgldev/acl/permissions/';
				} elseif ($bOperationCreate) {
					// Ensure that a record for the specified Role and Resource does not exist
					$mExists = $oDaoPermissions->checkIfExists($aPermissionDetails['id_sys_role'], $aPermissionDetails['id_sys_resource_privilege']);
					if ($mExists !== false) {
						$aContent['redirect'] = '/kwgldev/acl/permissions/operation/update/type/' . $sType . '/id/' . $mExists . '/';
					} else {
						$aPermissionDetails->save();
						$aContent['redirect'] = '/kwgldev/acl/permissions/';
					}
				}



			} else {
				// Do nothing
			}

		}





		$aContent['display'] = $sDisplay;
		$aContent['form'] = $oFormPermissions;

		return $aContent;
	}

}