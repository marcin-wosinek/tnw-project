<?php
/**
 *
 */
class IndexController extends Kwgl_Controller_Action {

	public function indexAction () {
  }

	public function loginAction () {

            define('API_KEY', 'ijhfvgdjj594');
            define('API_SECRET', '2AJOxvV59Y7NCMAK');
            define('REDIRECT_URI', 'http://' . $_SERVER['SERVER_NAME'] . '/index/login');
            define('SCOPE', 'r_fullprofile r_emailaddress rw_nus r_network');

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

            $user = Model_LinkedIn::fetch('GET', '/v1/people/~:(first-name,last-name,email-address,id,headline,num-connections,picture-url,date-of-birth)');
            //Zend_Debug::dump($user);

            $_SESSION['linked_in_id'] = $user->id;

            $daoUsers = Kwgl_Db_Table::factory('Users');
            $daoConnections = Kwgl_Db_Table::factory('Connections');

            $existingUser = $daoUsers->fetchDetail(array("id"), array("linkedInId = ?" => $user->id));

            // if there is no user with this linkedInId yet
            if(!$existingUser){
                $userData = Model_Users::formUserData($user);
                $ownerId = $daoUsers->insert($userData);
            } else {
                $ownerId = $existingUser->id;
                $userData = Model_Users::formUserData($user);
                $daoUsers->update($userData,array("id = ?" => $ownerId));
            }

            // retrieve connections
            $connections = Model_LinkedIn::fetch('GET', '/v1/people/~/connections:(first-name,last-name,id,picture-url,headline,num-connections)');
            $connections = (array) $connections;
            //Zend_Debug::dump($connections);

            foreach($connections["values"] AS $connection){

                //Zend_Debug::dump($connection);

                $existingUser = $daoUsers->fetchDetail(array("id"), array("linkedInId = ?" => $connection->id));

                // if there is no user with this linkedInId yet
                if(!$existingUser){
                    $userData = Model_Users::formUserData($connection);
                    $connectionId = $daoUsers->insert($userData);
                } else {
                    $connectionId = $existingUser->id;
                }

                $existingConnection = $daoConnections->fetchDetail(array("id"), array("ownerId = ?" => $ownerId, "connectionId = ?" => $connectionId));

                if(!$existingConnection){
                    // make the conection
                    $daoConnections->insert(array("ownerId" => $ownerId, "connectionId" => $connectionId));
                }




            }


            //Zend_Debug::dump($connections);

	}

	public function visualizationAction () {
  }

}
