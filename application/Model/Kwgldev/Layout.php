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
class Model_Kwgldev_Layout extends Kwgl_Model {

	public static $bPaginationTemplateRequired = false;

	public static function requirePaginationTemplate () {
		self::$bPaginationTemplateRequired = true;
	}

	public static function isPaginationTemplateRequired () {
		return self::$bPaginationTemplateRequired;
	}

}