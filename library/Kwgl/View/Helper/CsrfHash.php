<?php
/**
 * View Helper to return the CSRF Hash Element if it has been set in the Form
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_View
 * @subpackage Helper
 */
class Kwgl_View_Helper_CsrfHash {

	public function csrfHash ($oForm) {

		// Check if a CSRF Hash has been set
		if (isset($oForm->element->csrf_hash)) {
			$sContent = $oForm->element->csrf_hash;
		} else {
			$sContent = '';
		}

		return $sContent;
    }
}