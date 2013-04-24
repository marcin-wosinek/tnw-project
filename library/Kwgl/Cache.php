<?php
/**
 * Description of Cache
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Cache
 */
class Kwgl_Cache {

	/**
	 * Store variables temporaryly
	 *
	 * @param mixed $mEntity
	 * @param type $sType
	 * @param type $fCallBack
	 * @return mixed
	 */
	public static function temporaryStorage ($mEntity, $sType, $fCallBack = null) {

		//create a key to save data
		$sKey = null;
		//if the entity is an object
		if(is_object($mEntity)){
			//if is string exists call and get the string
			if(method_exists($mEntity, 'toString')){
				$sKey = $mEntity->toString();
			}
		}
		//if passed is a string
		elseif (is_string($mEntity)) {
			$sKey = $mEntity;
		}
		//if an array is passed, serialize it
		elseif (is_array($mEntity)){
			$sKey = json_encode($mEntity);
		}
		elseif (is_numeric($mEntity) || is_double($mEntity)) {
			$sKey = $mEntity;
		}
		//if all fails serialize
		else{
			$sKey = serialize($mEntity);
		}

		//md5 to shrten the key
		$sKey = md5($sKey);

		//check if the result exists in memory
		if(Zend_Registry::isRegistered($sKey)){
			return Zend_Registry::get($sKey);
		}
		//execute and save result in registry
		else{
			//if callback is defined call it
			if(!is_null($fCallBack)){
				$sResult = call_user_func($fCallBack, $mEntity);
			}else{
				//if the passed is an instance of db select, execute
				if($mEntity instanceof Zend_Db_Select){
					$oQuery = $mEntity->query();
					$sResult = $oQuery->fetchAll();
				}
				//otherwise save the passed in object
				else{
					$sResult = $mEntity;
				}
			}

			//save result in registry
			Zend_Registry::set($sKey, $sResult);
			return $sResult;
		}

	}

	/**
	 * Utility to function to fetch Zend Cache Manager
	 *
	 * @return Zend_Cache_Manager
	 */
	public static function getManager () {

		$oBootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

		$bCacheManagerAvailable = $oBootstrap->hasPluginResource('cachemanager');

		if ($bCacheManagerAvailable) {
			$oCacheManager = $oBootstrap->getPluginResource('cachemanager')->getCacheManager();
		} else {
			throw new Exception("Cache Manager Resource has not been defined for this Application.");
		}

		return $oCacheManager;
	}
	
}