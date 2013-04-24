<?php

/**
 * Attempts to retrieve a reliable IP from the User
 * Note: Advised to store this with $_SERVER['REMOTE_ADDR'] if you want to be thorough as most headers can be spoofed
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Utility
 */
class Kwgl_Utility_Ip {

	/**
	 * Function to retrieve a fairly accurate IP Address of the User / Visitor
	 *
	 * @return string Returns an IP Address that is deemed reliable
	 */
	public static function getIp () {

		if (isset($_SERVER['HTTP_CLIENT_IP']) && self::_checkIp($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		}

		if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $sIp) {
				if (self::_checkIp(trim($sIp))) {
					return $sIp;
				}
			}
		}

		if (isset($_SERVER['HTTP_X_FORWARDED']) && self::_checkIp($_SERVER["HTTP_X_FORWARDED"])) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::_checkIp($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
			return $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
		} elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && self::_checkIp($_SERVER["HTTP_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_FORWARDED_FOR"];
		} elseif (isset($_SERVER['HTTP_FORWARDED']) && self::_checkIp($_SERVER["HTTP_FORWARDED"])) {
			return $_SERVER["HTTP_FORWARDED"];
		} else {
			return $_SERVER["REMOTE_ADDR"];
		}

	}

	/**
	 * Checks if the IP is from within a special/reserved range
	 *
	 * @param string $sIp
	 * @return boolean
	 */
	protected static function _checkIp ($sIp) {

		if (!empty($sIp) && ip2long($sIp) != -1 && ip2long($sIp) != false) {
			$aPrivateIp = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
			);

			foreach ($aPrivateIp as $aIpRange) {
				$sIpLow = ip2long($aIpRange[0]);
				$sIpHigh = ip2long($aIpRange[1]);
				if ((ip2long($sIp) >= $sIpLow) && (ip2long($sIp) <= $sIpHigh))
					return false;
			}

			return true;
		} else {
			return false;
		}

	}
	
}