<?php
/**
 *
 */
class IndexController extends Kwgl_Controller_Action {

	public function indexAction () {
            //echo "index";
            
            define('API_KEY',      'ijhfvgdjj594'                                          );
            define('API_SECRET',   '2AJOxvV59Y7NCMAK'                                       );
            define('REDIRECT_URI', 'http://' . $_SERVER['SERVER_NAME'] . '/index/index');
            define('SCOPE',        'r_fullprofile r_emailaddress rw_nus'                        );
            
            // OAuth 2 Control Flow
            if (isset($_GET['error'])) {
                // LinkedIn returned an error
                print $_GET['error'] . ': ' . $_GET['error_description'];
                exit;
            } elseif (isset($_GET['code'])) {
                // User authorized your application
                if ($_SESSION['state'] == $_GET['state']) {
                    // Get token so you can make API calls
                    Model_LinkedIn::getAccessToken($_GET["code"]);
                } else {
                    // CSRF attack? Or did you mix up your states?
                    exit;
                }
            } else { 
                if ((empty($_SESSION['expires_at'])) || (time() > $_SESSION['expires_at'])) {
                    // Token has expired, clear the state
                    $_SESSION = array();
                }
                if (empty($_SESSION['access_token'])) {
                    // Start authorization process
                    Model_LinkedIn::getAuthorizationCode();
                }
            }
            
	}

}