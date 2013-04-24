<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Dao_System_Role extends Kwgl_Db_Table {

	/**
	 * Returns Roles with their Parent (if any)
	 *
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getRoles () {

		$oQuery = $this->select();
		$oQuery->from(array('r' => $this->_name));
		$oQuery->joinLeft(array('pr' => $this->_name), 'r.id_parent = pr.id', array('parent' => 'name'));

		//Zend_Debug::dump((string)$oQuery, 'Query for getRoles');
		return $this->fetchAll($oQuery);
	}

	/**
	 * Return Role Names with their ID as the key
	 *
	 * @return type
	 */
	public function getRoleNamesWithId () {

		return $this->fetchPairs(array('id', 'name'), null, array('name ASC'));

	}
}