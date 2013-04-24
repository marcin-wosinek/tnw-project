<?php
/**
 *
 */
class Kwgldev_CacheController extends Kwgl_Controller_Action {

	/**
	 *
	 */
	public function init () {
		parent::init();

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');
	}

	/**
	 *
	 */
	public function indexAction () {

		// List of Cache Names created by default
		$aCacheWhiteList = array(
			'default',
			'page',
			'pagetag',
		);

		$oCacheManager = Kwgl_Cache::getManager(); /* @var $oCacheManager Zend_Cache_Manager */
		$aCaches = $oCacheManager->getCaches();

		$aCacheManageList = array();

		foreach ($aCaches as $sCacheName => $oCache) {
			if (!in_array($sCacheName, $aCacheWhiteList)) {

				$aCacheManageList[$sCacheName] = array(
						'cache' => $oCache,
						'directory' => str_replace(APPLICATION_PATH, '', Kwgl_Config::get(array('resources', 'cachemanager', $sCacheName, 'backend', 'options', 'cache_dir'))),
						'storage' => $oCache->getFillingPercentage(),
						'lifetime' => $oCache->getOption('lifetime'),
					);
			}
		}

		if (in_array('operation', $this->_aParameterKey) && in_array('cache', $this->_aParameterKey)) {

			$sRequestOperation = $this->_aParameter['operation'];
			$sRequestCache = $this->_aParameter['cache'];

			switch ($sRequestOperation) {

				case 'clear':

					// Check if the Cache to clear is found in the List

					if (array_key_exists($sRequestCache, $aCacheManageList)) {

						$oCache = $oCacheManager->getCache($sRequestCache);
						$bStatus = $oCache->clean(Zend_Cache::CLEANING_MODE_ALL);

						if ($bStatus) {
							Model_Kwgldev_Response::addResponse(
								'The "' . $sRequestCache . '" Cache has been removed.',
								Model_Kwgldev_Response::STATUS_SUCCESS);
						} else {
							Model_Kwgldev_Response::addResponse(
								'An error occurred while trying to remove the "' . $sRequestCache . '" Cache.',
								Model_Kwgldev_Response::STATUS_ERROR);
						}

					} else {
						Model_Kwgldev_Response::addResponse(
							'A cache by the name of "' . $sRequestCache . '" cannot be found.',
							Model_Kwgldev_Response::STATUS_WARNING);
					}

					break;
				default:
					Model_Kwgldev_Response::addResponse(
						'Unknown operation.',
						Model_Kwgldev_Response::STATUS_WARNING);
					break;

			}

		}

		$aContent['cache-list'] = $aCacheManageList;

		$this->view->aContent = $aContent;
	}


}