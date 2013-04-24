<?php
/**
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_View
 * @subpackage Helper
 */
class Kwgl_View_Helper_NumberFormat {

	public static function numberFormat($mAmount, $mFormat = 'u') {
		if (is_numeric($mFormat)) {
			$mAmount = round($mAmount, $mFormat, PHP_ROUND_HALF_DOWN);
		} elseif (is_string($mFormat)) {
			$mAmount = sprintf('%' . $mFormat, $mAmount);
		}
		return $mAmount;
    }
}