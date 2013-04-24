<?php
/**
 * View Helper for returning the attributes of a Form such as action, method, etc
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_View
 * @subpackage Helper
 */
class Kwgl_View_Helper_FormAttributes extends Zend_View_Helper_Abstract {

	public function formAttributes ($oForm) {

		$sContent = '';
		$sContent .= ' id="' . $oForm->element->getAttrib('id') . '"';
		$sContent .= ' action="' . $oForm->element->getAction() . '"';
		$sContent .= ' method="' . $oForm->element->getMethod() . '"';
		$sContent .= ' enctype="' . $oForm->element->getAttrib('enctype') . '"';
		$sContent .= ' accept-charset="utf-8"';

		return $sContent;
	}
}