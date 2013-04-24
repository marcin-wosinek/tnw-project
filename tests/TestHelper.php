<?php
/*
 * Start output buffering
 */
ob_start();

/*
 * Set error reporting to the level to which code must comply.
 */
error_reporting(E_ALL & ~E_NOTICE);

/*
 * Testing environment
 */
require_once ('AppEnvironment.php');

/*
 * Determine the root, library, and tests directories
 */
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));
define('TESTS_PATH', realpath(dirname(__FILE__)));

/*
 * Prepend the library/ directory to the
 * include_path. This allows the tests to run out of the box.
 */
$path = array(
	LIBRARY_PATH,
	get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

/**
 * Register autoloader
 */
require_once 'Zend/Loader/Autoloader.php';
$oAutoLoader = Zend_Loader_Autoloader::getInstance();
$oAutoLoader->registerNamespace('Kwgl_');
$oAutoLoader->setFallbackAutoloader(true);

/**
 * Make sure that common $_SERVER vars are set when running
 * tests from command line
 */
if (isset($_SERVER['HTTP_REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_REQUEST_URI'];
}
else {
	if (isset($_SERVER['SCRIPT_NAME'])) {
		$_SERVER['HTTP_REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	}
	else {
		$_SERVER['HTTP_REQUEST_URI'] = $_SERVER['PHP_SELF'];
	}
	if ($_SERVER['QUERY_STRING']) {
		$_SERVER['HTTP_REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
	$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_REQUEST_URI'];
}

/**
 * Create a new application
 */
$oApplication = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);
$oApplication->bootstrap();