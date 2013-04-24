<?php
/**
 *
 */
class DonateController extends Kwgl_Controller_Action {

	public function indexAction () {
            
            if($_POST){
                
                require_once 'Braintree/Braintree.php';
                
                $daoDonations = Kwgl_Db_Table::factory('Donations');
                $daoUser = Kwgl_Db_Table::factory('Users');
                
                $money = $_POST["money"];
                $linkedInId = $_SESSION["linked_in_id"];
                
                // get user id based on session linkedinid
                $userData = $daoUser->fetchDetail(array("id","firstName","lastName","emailAddress"), array("linkedInId = ?" => $linkedInId));
                $userId = $userData->id;
                
                Braintree_Configuration::environment('sandbox');
                Braintree_Configuration::merchantId('x2cz2ktbj4ywcntv');
                Braintree_Configuration::publicKey('rsc6rj5dkxfsvqhm');
                Braintree_Configuration::privateKey('6f9417d781216e146c36a15f8350b950');

                $result = Braintree_Transaction::sale(array(
                    'amount' => $money,
                    'creditCard' => array(
                        'number' => '5105105105105100',
                        'expirationDate' => '05/12'
                    ), 
                    'customer' => array(
                        'firstName' => $userData->firstName,
                        'lastName' => $userData->lastName,
                        'email' => $userData->emailAddress,
                        'id' => "1234"
                  ),
                ));
                
                $donationData["money"] = $money;
                $donationData["message"] = $message;
                $donationData["userId"] = $userId;
                $donationData["datetime"] = date("Y-m-d H:i:s");
                $daoDonations->insert($donationData);

                if ($result->success) {
                    print_r("success!: " . $result->transaction->id);
                } else if ($result->transaction) {
                    print_r("Error processing transaction:");
                    print_r("\n  message: " . $result->message);
                    print_r("\n  code: " . $result->transaction->processorResponseCode);
                    print_r("\n  text: " . $result->transaction->processorResponseText);
                } else {
                    print_r("Message: " . $result->message);
                    print_r("\nValidation errors: \n");
                    print_r($result->errors->deepAll());
                }
                
            }
            
	}
        
        public function succesfullAction(){
            
            
            
        }
        
        public function listAction(){
            
            require_once 'Braintree/Braintree.php';
            
            Braintree_Configuration::environment('sandbox');
            Braintree_Configuration::merchantId('x2cz2ktbj4ywcntv');
            Braintree_Configuration::publicKey('rsc6rj5dkxfsvqhm');
            Braintree_Configuration::privateKey('6f9417d781216e146c36a15f8350b950');

            $daoUsers = Kwgl_Db_Table::factory('Users');
            $daoConnections = Kwgl_Db_Table::factory('Connections');
            
            $result = Braintree_Transaction::search(array( Braintree_TransactionSearch::type()->is(Braintree_Transaction::SALE)));
            $donators = array();
            
            // loop through transactions
            foreach($result->_ids AS $transactionId){
                
                $transaction = Braintree_Transaction::find($transactionId);
                
                // if the transaction is authorized
                if($transaction->_attributes["status"] == "authorized"){
                    
                    // and the email address is know (used as unique identifier)
                    if($transaction->_attributes["customer"]["email"]){
                     
                        $userData = $daoUsers->fetchDetail(array("id"), array("emailAddress = ?" => $transaction->_attributes["customer"]["email"]));
                        
                        // add the amount as points
                        $donators[$userData->id]["amount"] += $transaction->amount;
                        
                    }
                    
                }
            }
            
            $userScores = array();
            
            // loop through donators and give their connections points
            foreach($donators AS $ownerId => $donations){
                
                // get connections from donator
                $connectionUsers = $daoUsers->getConnectionUsersByOwnerId($ownerId);
            
                foreach($connectionUsers AS $connectionUser){
                    $userScores[$connectionUser["id"]]["points"] += $donations["amount"]/5;
                    $userScores[$connectionUser["id"]]["firstname"] = $connectionUser["firstName"];
                    $userScores[$connectionUser["id"]]["lastname"] = $connectionUser["lastName"];
                    $userScores[$connectionUser["id"]]["picture"] = $connectionUser["pictureUrl"];
                    $userScores[$connectionUser["id"]]["headline"] = $connectionUser["headLine"];
                }
                
                $userData = $daoUsers->fetchDetail(array("firstName","lastName","headLine","pictureUrl"), array("id = ?" => $ownerId));
                
                $userScores[$ownerId]["points"] += $donations["amount"];
                $userScores[$ownerId]["firstname"] = $userData["firstName"];
                $userScores[$ownerId]["lastname"] = $userData["lastName"];
                $userScores[$ownerId]["headline"] = $userData["headLine"];
                $userScores[$ownerId]["picture"] = $userData["pictureUrl"];
                
            }
            
            $this->view->userScores = json_encode($userScores);
            
        }

}