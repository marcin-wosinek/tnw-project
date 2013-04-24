<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initAutoLoading () {
            Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
	}

	protected function _initApp () {
            //Kwgl_Benchmark::setMarker('_initApp in Bootstrap - Start');

            // Set up Constants
            define('ROOT_DIR', APPLICATION_PATH . "/..");
            define('CONFIG', 'configuration');
            define('DB_CONFIG', 'database_configuration');
            define('DB', 'database');
            define('SITE_DOMAIN', $_SERVER['HTTP_HOST']);

            // Save Main Configuration Settings
            $aConfiguration = $this->getOptions();
            Kwgl_Config::setConfig($aConfiguration);
            //Zend_Registry::set(CONFIG, $aConfiguration);
            //Kwgl_Benchmark::setMarker('_initApp in Bootstrap - End');
	}

	protected function _initBenchmarking () {
            Kwgl_Benchmark::initialise(true);
	}

	protected function _initDatabaseConfiguration () {
            
            //Kwgl_Benchmark::setMarker('_initDatabaseConfiguration in Bootstrap - Start');

            $oResource = $this->getPluginResource('db');
            $oDb = $oResource->getDbAdapter();
            Zend_Registry::set(DB, $oDb);

		try {
			$oDb->query("SELECT 1");
		} catch (Exception $oException) {
			// Redirect User to a Static Page
			header('Location: /alternate.php');
			exit(0);
		}

            $aPaths = $this->getOption('paths');
            $sDatabaseConfigurationPath = $aPaths['db']['config'];
            $oDatabaseConfiguration = new Zend_Config_Ini($sDatabaseConfigurationPath);
            Zend_Registry::set(DB_CONFIG, $oDatabaseConfiguration);
            Kwgl_Db_Table::setDbConfig($oDatabaseConfiguration);

            //Kwgl_Benchmark::setMarker('_initDatabaseConfiguration in Bootstrap - End');
	
        }

	protected function _initAuthenticationConfiguration () {
            
            //Kwgl_Benchmark::setMarker('_initAuthenticationConfiguration in Bootstrap - Start');

            $aConfiguration = $this->getOption('auth');

            //Zend_Debug::dump($aConfiguration);
            Kwgl_Authenticate::setAuthConfig($aConfiguration);
            Kwgl_Auth_Storage_Db::setAuthConfig($aConfiguration);

            //Kwgl_Benchmark::setMarker('_initAuthenticationConfiguration in Bootstrap - End');
            
	}
}