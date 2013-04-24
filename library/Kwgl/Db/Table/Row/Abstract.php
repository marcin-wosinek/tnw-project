<?php
/**
 * Db table abstract row class provides a wrapper for zend row class
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Db
 * @subpackage Table_Row
 */
class Kwgl_Db_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract{

	/**
	 * overriden function to create table object from string
	 * - used in references
	 *
	 * @param string $tableName
	 * @return Kwgl_Db_Table
	 */
	protected function _getTableFromString($tableName) {

		return Kwgl_Db_Table::factory($tableName);

	}

}