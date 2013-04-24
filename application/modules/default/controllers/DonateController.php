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

            $result = Braintree_Transaction::search(array( Braintree_TransactionSearch::type()->is(Braintree_Transaction::SALE)));

            
            
            //Zend_Debug::dump($result);
            $donations = array();
            foreach($result->_ids AS $transactionId){
                
                //echo $transactionId."<br/>";
                $transaction = Braintree_Transaction::find($transactionId);
                
                
                //echo $transaction->_attributes["status"];
                
                if($transaction->_attributes["status"] == "authorized"){
                
                    //echo "email ".$transaction->_attributes["customer"]["email"];
                    
                    if($transaction->_attributes["customer"]["email"]){
                        
                        //Zend_Debug::dump($transaction);
                        
                        $donations[$transaction->_attributes["customer"]["email"]]["amount"] += $transaction->amount;
                
                    }
                    
                }
            }
            
            Zend_Debug::dump($donations);
            
            die();
            
        }

}