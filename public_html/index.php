<?php
// Set Error Reporting Level to ALL until Application is set up.
// To use Application specific Error Reporting Level then onwards.
error_reporting(E_ALL);

// Use ONLY if you want a full Benchmark. To be commented out in Production environments
require_once('../library/Kwgl/Benchmark.php');
Kwgl_Benchmark::initialise(true, false);

// Define the Path to the Application Directory
if (!defined('APPLICATION_PATH')) {
	define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
}

// Define Application Environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'general'));

// Set Include Path
$aIncludePath = array(
	realpath(APPLICATION_PATH . '/../library/'),
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $aIncludePath));

// Start up Zend_Application
require_once 'Zend/Application.php';
try {
    // Create application, bootstrap, and run
    $oApplication = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
    );
	// Initialisation Function in order to be executed
    $oBootstrap = $oApplication->bootstrap();

	// send a gzip compressed string if browser accepts gzip encoding
	if (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
		ob_start();
		$oBootstrap->run();
//		Kwgl_Benchmark::stop();
//		Kwgl_Benchmark::displayStyled(true);
		$sOutput = gzencode(ob_get_contents(), 1);
		ob_end_clean();
		header('Content-Encoding: gzip');
		echo $sOutput;
	}
	else {
		$oBootstrap->run();
//		Kwgl_Benchmark::stop();
//		Kwgl_Benchmark::displayStyled(true);
	}
} catch (Exception $oException) {
    echo $oException->getMessage();
    echo '<br /><pre>' . $oException->getTraceAsString() . '</pre>';
}
Kwgl_Benchmark::stop();
Kwgl_Benchmark::displayStyled(true);
?>