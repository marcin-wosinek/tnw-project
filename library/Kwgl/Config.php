<?php
/**
 * Configuration Class holding all Configuration Data set by ini files
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Config
 */
class Kwgl_Config {

	/**
	 * Store the Configuration
	 * @var array
	 */
	private static $aConfig = array();

	/**
	 * Assigns the Configuration Data to the Static variable designated to hold it within the class
	 *
	 * @param Zend_Config_Ini|array|string $mConfig
	 */
	public static function setConfig ($mConfig) {
		if ($mConfig instanceof Zend_Config_Ini) {
			self::$aConfig = $mConfig->toArray();
		} elseif (is_array($mConfig)) {
			self::$aConfig = $mConfig;
		} elseif(is_string($mConfig)) {
			//try to load the file
			$sSuffix = strtolower(pathinfo($mConfig, PATHINFO_EXTENSION));
			//get object based on file extension
			switch ($sSuffix) {
				case 'ini':
					$oConfig = new Zend_Config_Ini($mConfig);
					break;
				case 'xml':
					$oConfig = new Zend_Config_Xml($mConfig);
					break;
				case 'json':
					$oConfig = new Zend_Config_Json($mConfig);
					break;
				case 'yaml':
					$oConfig = new Zend_Config_Yaml($mConfig);
					break;
				case 'php':
				case 'inc':
					$oConfig = include $mConfig;
					if (!is_array($oConfig)) {
						throw new Exception('Invalid configuration file provided; PHP file does not return array value');
					}
					return $oConfig;
					break;
				default:
					throw new Exception('Invalid configuration file provided; unknown config type');
			}
			//save as an array
			self::$aConfig = $oConfig->toArray();
		} else{
			throw new Exception('Invalid database configuration provided');
		}
	}

	/**
	 * Get an option/value from the Configuration Data
	 * If the option/value cannot be found, a null value is returned
	 *
	 * @param array|string $mOption
	 * @return mixed
	 */
	public static function get ($mOption) {
		if (empty(self::$aConfig)) {
			return null;
		}
		if (!isset($mOption) || is_null($mOption)) {
			return null;
		}

		if (!is_array($mOption)) {
			if (is_string($mOption)) {
				$mOption = explode('.', $mOption);
			} else {
				$mOption = array($mOption);
			}

		}

		return self::traverse($mOption, self::$aConfig);
	}

	protected static function traverse ($aOption, $mConfig) {
		$sCurrentOption = array_shift($aOption);
		if (isset($mConfig[$sCurrentOption])) {
			$mConfig = $mConfig[$sCurrentOption];
			if (empty($aOption)) {
				return $mConfig;
			} else {
				return self::traverse($aOption, $mConfig);
			}
		} else {
			return null;
		}
	}

}