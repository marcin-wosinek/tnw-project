<?php

/**
 * Allows fetching of MVC related information
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Utility
 */
class Kwgl_Utility_Mvc {

	private static $aData = array();

	/**
	 * Forms a URL based on the current Request URL.
	 * If $bFull is true, a complete URL with parameters is returned. If false, a minimal URL is returned.
	 *
	 * @param boolean $bFull
	 * @return string
	 */
	public static function getUrl ($bFull = true) {
		$oFrontController = Zend_Controller_Front::getInstance();
		$oRequest = $oFrontController->getRequest();

		// Build URL
		$aUserParameters = $oRequest->getUserParams();
		$sModule = ($aUserParameters['module'] == 'default') ? '' : $aUserParameters['module'];
		$sController = ($aUserParameters['controller'] == 'index') ? '' : $aUserParameters['controller'];
		$sAction = ($aUserParameters['action'] == 'index') ? '' : $aUserParameters['action'];

		$sUrl = $oFrontController->getBaseUrl();
		if ($sUrl == '') {
			$sUrl = '/';
		}

		if ($bFull) {
			if ($sModule == '') {
				$sUrl .= 'default/';
			} else {
				$sUrl .= $sModule . '/';
			}

			if ($sController == '') {
				$sUrl .= 'index/';
			} else {
				$sUrl .= $sController . '/';
			}

			if ($sAction == '') {
				$sUrl .= 'index/';
			} else {
				$sUrl .= $sAction . '/';
			}

			unset($aUserParameters['module']);
			unset($aUserParameters['controller']);
			unset($aUserParameters['action']);

			// Add Parameters
			foreach ($aUserParameters as $sKey => $sValue) {
				$sUrl .= $sKey . '/' . $sValue . '/';
			}

		} else {
			$sEnd = '';
			if ($sAction != '') {
				$sEnd = $sAction . '/' . $sEnd;
			}

			if ($sController != '') {
				$sEnd = $sController . '/' . $sEnd;
			}

			if ($sModule != '') {
				$sEnd = $sModule . '/' . $sEnd;
			}

			$sUrl .= $sEnd;
		}

		return $sUrl;
	}

	/**
	 * Returns current Request Object
	 * @return Zend_Controller_Request_Abstract
	 */
	public static function getRequest () {
		$oFrontController = Zend_Controller_Front::getInstance();
		return $oFrontController->getRequest();
	}

	/**
	 * Returns current Module name
	 * @return string
	 */
	public static function getModule () {
		$oFrontController = Zend_Controller_Front::getInstance();
		return $oFrontController->getRequest()->getModuleName();
	}

	/**
	 * Returns current Controller name
	 * @return string
	 */
	public static function getController () {
		$oFrontController = Zend_Controller_Front::getInstance();
		return $oFrontController->getRequest()->getControllerName();
	}

	/**
	 * Returns current Action name
	 * @return string
	 */
	public static function getAction () {
		$oFrontController = Zend_Controller_Front::getInstance();
		return $oFrontController->getRequest()->getActionName();
	}

	/**
	 * Returns Parameters passed along with the Request
	 * @return array
	 */
	public static function getParameters () {
		$oFrontController = Zend_Controller_Front::getInstance();
		return $oFrontController->getRequest()->getParams();
	}

	/**
	 * Returns Parameter Names passed along with the Request
	 * @return array
	 */
	public static function getParameterKeys () {
		$oFrontController = Zend_Controller_Front::getInstance();
		return array_keys($oFrontController->getRequest()->getParams());
	}

	/**
	 * Iterates through all Modules/Controllers/Actions to get the complete structure
	 *
	 * @return array
	 */
	public static function getStructure () {

		if (isset(self::$aData['structure'])) {
			return self::$aData['structure'];
		}

		$oFrontController = Zend_Controller_Front::getInstance();

		$sDefaultModuleName = $oFrontController->getDefaultModule();
		$sCurrentModuleName = Kwgl_Utility_Mvc::getModule();
		$sCurrentControllerName = Kwgl_Utility_Mvc::getController();
		$sCurrentActionName = Kwgl_Utility_Mvc::getAction();

        $aMvcStructure = array();

		// Iterate through all Controller Directories (for all Modules) and acquire all Actions defined
		foreach ($oFrontController->getControllerDirectory() as $sModuleName => $sPath) {

			//skip if path doesn't exist
			if(!file_exists($sPath)){
				continue;
			}

			$aMvcStructure[$sModuleName] = array();

			foreach (scandir($sPath) as $sFileName) {

				if (strstr($sFileName, "Controller.php") !== false) {

					$sControllerName = strtolower(str_replace('Controller.php', '', $sFileName));
					$aMvcStructure[$sModuleName][$sControllerName] = array();


					if ($sModuleName == $sDefaultModuleName) {
						$sActualControllerClassName = $sControllerName . 'Controller';
					} else {
						$sActualControllerClassName = ucfirst($sModuleName) . '_' . $sControllerName . 'Controller';
					}

					if ($sModuleName == $sCurrentModuleName && $sControllerName == $sCurrentControllerName) {
						$aControllerClassMethods = get_class_methods($sActualControllerClassName);
					} else {
						include_once $sPath . DIRECTORY_SEPARATOR . $sFileName;
						$aControllerClassMethods = get_class_methods($sActualControllerClassName);
					}

					$aActions = array();
					foreach ($aControllerClassMethods as $sMethodName) {
						if (strstr($sMethodName, "Action") !== false) {
							$sActionName = substr($sMethodName, 0, -6);
							$aMvcStructure[$sModuleName][$sControllerName][$sActionName] = array();
						}
					}

				} else {
					if ($sFileName == 'Xhr') {

					}
				}


			}

		}

		self::$aData['structure'] = $aMvcStructure;

		return self::getStructure();

	}
	
}