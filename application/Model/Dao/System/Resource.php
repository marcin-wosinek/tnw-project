<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Dao_System_Resource extends Kwgl_Db_Table {

	const TYPE_PAGE = 'page';
	const TYPE_MODEL = 'model';

	/**
	 * Returns Resources with their Parent (if any) for the specified Type (or both if not specified)
	 *
	 * @param string $sType
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getResources($sType = null) {

		$oQuery = $this->select();
		$oQuery->from(array('r' => $this->_name));
		$oQuery->joinLeft(array('pr' => $this->_name), 'r.id_parent = pr.id', array('parent' => 'name'));

		if (!is_null($sType)) {
			switch ($sType) {
				case self::TYPE_PAGE:
				case self::TYPE_MODEL:
					$oQuery->where('r.type = ?', $sType);
					break;
			}

		}

		$oQuery->order('r.name ASC');

		//Zend_Debug::dump((string)$oQuery, 'Query for getResources');
		return $this->fetchAll($oQuery);

	}

}