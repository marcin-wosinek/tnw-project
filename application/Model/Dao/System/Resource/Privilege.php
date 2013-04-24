<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Dao_System_Resource_Privilege extends Kwgl_Db_Table {

	/**
	 * Returns Privileges with their associated Resource
	 *
	 * @param integer $iResourceId
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getPrivilegesWithResources ($iResourceId = null) {

		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('rp' => $this->_name));
		$oQuery->join(array('res' => 'sys_resource'), 'rp.id_sys_resource = res.id', array('resource_name' => 'name'));

		if (!is_null($iResourceId)) {
			$oQuery->where('res.id = ?', $iResourceId);
		}

		//Zend_Debug::dump((string)$oQuery, 'Query for getPermissions');
		return $this->fetchAll($oQuery);

	}

	/**
	 * Returns Privileges for Resources that are of Type Page
	 * @return array
	 */
	public function getPrivilegesForPageResource () {

		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('rp' => $this->_name), array('id'));
		$oQuery->join(array('res' => 'sys_resource'), 'rp.id_sys_resource = res.id', array('name'));
		$oQuery->where('res.type = ?', Dao_System_Resource::TYPE_PAGE);
		$oQuery->order('name ASC');

		//Zend_Debug::dump((string)$oQuery, 'Query for getPrivilegesForPageResource');
		return self::getDefaultAdapter()->fetchPairs($oQuery);
	}

	/**
	 * Returns Privileges for Resources that are of Type Model
	 * @return array
	 */
	public function getPrivilegesForModelResource () {

		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('rp' => $this->_name), array('id'));
		$oQuery->join(array('res' => 'sys_resource'), 'rp.id_sys_resource = res.id', array('name' => new Zend_Db_Expr("IF(IFNULL(rp.name, 1), res.name, CONCAT(res.name, ' - ', rp.name))")));
		$oQuery->where('res.type = ?', Dao_System_Resource::TYPE_MODEL);
		$oQuery->order('name ASC');

		//Zend_Debug::dump((string)$oQuery, 'Query for getPrivilegesForPageResource');
		return self::getDefaultAdapter()->fetchPairs($oQuery);

	}

	/**
	 *
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getModelResourcesWithPrivileges () {

		$oQuery = $this->select();
		$oQuery->setIntegrityCheck(false);
		$oQuery->from(array('res' => 'sys_resource'), array('resource_name' => 'name'));
		$oQuery->join(array('rp' => $this->_name), 'res.id = rp.id_sys_resource', array('privilege_name' => 'name'));
		$oQuery->where('res.type = ?', Dao_System_Resource::TYPE_MODEL);
		$oQuery->order(array('resource_name ASC', 'privilege_name ASC'));

		//Zend_Debug::dump((string)$oQuery, 'Query for get Model Resources and associated Privileges');
		return $this->fetchAll($oQuery);
	}
}