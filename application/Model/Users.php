<?php

/**
 * Description of Users
 *
 * @author user
 */
class Model_Users {
    
    public $linkedInId = null;
    
    public function __construct($linkedInId) {
        $this->linkedInId = $linkedInId;
    }
    
    public function storeUser(){}
    
    public function getConnectionsByLinkedInId(){}
    
    public static function formUserData($user){
        
        // form user data array
        $userData = array();
        $userData["signedIn"] = date("Y-m-d H:i:s");
        $userData["linkedInId"] = $user->id;
        $userData["headLine"] = $user->headline;
        $userData["firstName"] = $user->firstName;
        $userData["lastName"] = $user->lastName;
        $userData["pictureUrl"] = $user->pictureUrl;
        $userData["emailAddress"] = $user->emailAddress;
        $userData["numConnections"] = $user->numConnections;
        
        return $userData;
        
    }
    
}

?>
