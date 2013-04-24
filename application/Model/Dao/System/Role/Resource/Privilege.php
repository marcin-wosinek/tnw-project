<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Dao_System_Role_Resource_Privilege extends Kwgl_Db_Table {

	const TYPE_PAGE = 'page';
	const TYPE_MODEL = 'model';

	/**
	 *
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getPermissions () {
		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('rrp' => $this->_name));
		$oQuery->join(array('rp' => 'sys_resource_privilege'), 'rrp.id_sys_resource_privilege = rp.id', array('privilege_name' => 'name'));
		$oQuery->join(array('res' => 'sys_resource'), 'rp.id_sys_resource = res.id', array('resource_name' => 'name'));
		$oQuery->join(array('r' => 'sys_role'), 'rrp.id_sys_role = r.id', array('role_name' => 'name'));
		//Zend_Debug::dump((string)$oQuery, 'Query for getPermissions');
		return $this->fetchAll($oQuery);
	}

	/**
	 *
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getPermissionsForPageResources () {

		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('rrp' => $this->_name));
		$oQuery->join(array('rp' => 'sys_resource_privilege'), 'rrp.id_sys_resource_privilege = rp.id', array('privilege_name' => 'name'));
		$oQuery->join(array('res' => 'sys_resource'), 'rp.id_sys_resource = res.id', array('resource_name' => 'name'));
		$oQuery->join(array('r' => 'sys_role'), 'rrp.id_sys_role = r.id', array('role_name' => 'name'));
		$oQuery->where('res.type = ?', self::TYPE_PAGE);
		$oQuery->order(array('res.name ASC'));
		//Zend_Debug::dump((string)$oQuery, 'Query for getPermissionsForPageResources');
		return $this->fetchAll($oQuery);

	}

	/**
	 *
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getPermissionsForModelResources () {

		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('rrp' => $this->_name), array('*', 'full_privilege_name' => new Zend_Db_Expr("IF(IFNULL(rp.name, 1), res.name, CONCAT(res.name, ' - ', rp.name))")));
		$oQuery->join(array('rp' => 'sys_resource_privilege'), 'rrp.id_sys_resource_privilege = rp.id', array('privilege_name' => 'name'));
		$oQuery->join(array('res' => 'sys_resource'), 'rp.id_sys_resource = res.id', array('resource_name' => 'name'));
		$oQuery->join(array('r' => 'sys_role'), 'rrp.id_sys_role = r.id', array('role_name' => 'name'));
		$oQuery->where('res.type = ?', self::TYPE_MODEL);
		$oQuery->order(array('res.name ASC'));
		//Zend_Debug::dump((string)$oQuery, 'Query for getPermissionsForModelResources');
		return $this->fetchAll($oQuery);

	}

	/**
	 *
	 * @param integer $iRoleId
	 * @param integer $iPrivilegeId
	 * @return mixed
	 */
	public function checkIfExists ($iRoleId, $iPrivilegeId) {

		$aDetail = $this->fetchDetail(array('id'), array('id_sys_role = ?' => $iRoleId, 'id_sys_resource_privilege = ?' => $iPrivilegeId));
		if (count($aDetail) > 0) {
			$iRowId = $aDetail['id'];
			return $iRowId;
		} else {
			return false;
		}

	}

}