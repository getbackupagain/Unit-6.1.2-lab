<?php
/*This page was sample code for credit card transaction made by "saikatbasu01" @ https://github.com/AuthorizeNet/sample-code-php/blob/master/PaymentTransactions/charge-credit-card.php
I have adapted it to better serve the application


API Login ID
bizdev05

Transaction Key
4kJd237rZu59qAZd


Original code was:

  require 'vendor/autoload.php';
  require_once 'constants/SampleCodeConstants.php';
  use net\authorize\api\contract\v1 as AnetAPI;
  use net\authorize\api\controller as AnetController;

  I changed the addresses to something valid, hopefully, I have no idea at all if It's correct, there's literally no information on that, but the compiler stopped whining for now.

    */


  include 'sdk_php_master/autoload.php';//as far as I can tell these are the correct file addresses
  //require_once 'constants/SampleCodeConstants.php'; // commenting because this caused a problem and it stopped throwing a problem when I got rid of it
  use sdk_php_master\lib\net\authorize\api\contract\v1 as AnetAPI;
  use sdk_php_master\lib\net\authorize\api\controller as AnetController;

  define("AUTHORIZENET_LOG_FILE", "phplog");


function chargeCreditCard($transact_array)// Takes an associative array of credit card information, and uses the api to charge the card indicated. O(c)
{
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName("bizdev05");
    $merchantAuthentication->setTransactionKey("4kJd237rZu59qAZd");
    
    // Set the transaction's refId
    $refId = 'ref' . time();

    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber($transact_array["card_number"]);  
    $creditCard->setExpirationDate($transact_array["card_exp_date"]);       
    $creditCard->setCardCode($transact_array["card_security_code"]);                   

    // Add the payment data to a paymentType object
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    // Create order information
    $order = new AnetAPI\OrderType();
    $order->setInvoiceNumber($transact_array["invoice_number"]);                     
    $order->setDescription("Placeholder");                  

    // Set the customer's Bill To address
    $customerAddress = new AnetAPI\CustomerAddressType();
    $customerAddress->setFirstName($transact_array["card_fname"]);       
    $customerAddress->setLastName($transact_array["card_lname"]);           
    $customerAddress->setCompany($transact_array["company"]);    
    $customerAddress->setAddress($transact_array["billing_address"]);   
    $customerAddress->setCity($transact_array["billing_city"]);         
    $customerAddress->setState($transact_array["billing_state"]);                   
    $customerAddress->setZip($transact_array["billing_zip"]);                  
    $customerAddress->setCountry($transact_array["billing_country"]);                

    // Set the customer's identifying information
    $customerData = new AnetAPI\CustomerDataType();
    $customerData->setType("individual");       
    $customerData->setId($transact_array["customer_data"]);            
    $customerData->setEmail($transact_array["customer_email"]);   

    // Add values for transaction settings
    $duplicateWindowSetting = new AnetAPI\SettingType();
    $duplicateWindowSetting->setSettingName("duplicateWindow");   
    $duplicateWindowSetting->setSettingValue("60");                     

    // Add some merchant defined fields. These fields won't be stored with the transaction,
    // but will be echoed back in the response.
    $merchantDefinedField1 = new AnetAPI\UserFieldType();    //I think I can get rid of these
    $merchantDefinedField1->setName("customerLoyaltyNum");   //but I'm waiting to see if I can get the code working first before experimenting
    $merchantDefinedField1->setValue("1128836273");

    $merchantDefinedField2 = new AnetAPI\UserFieldType();   //ditto last comment
    $merchantDefinedField2->setName("favoriteColor");      //<------------
    $merchantDefinedField2->setValue("blue");

    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();    
    $transactionRequestType->setTransactionType("authCaptureTransaction");   
    $transactionRequestType->setAmount($transact_array["amount"]);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setPayment($paymentOne);
    $transactionRequestType->setBillTo($customerAddress);
    $transactionRequestType->setCustomer($customerData);
    $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
    $transactionRequestType->addToUserFields($merchantDefinedField1);
    $transactionRequestType->addToUserFields($merchantDefinedField2);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();   
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    

    if ($response != null) {
        // Check to see if the API request was successfully received and acted upon
        if ($response->getMessages()->getResultCode() == "Ok") {
            // Since the API request was successful, look for a transaction response
            // and parse it to display the results of authorizing the card
            $tresponse = $response->getTransactionResponse();
        
            if ($tresponse != null && $tresponse->getMessages() != null) {
                echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
                echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
                echo " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
                echo " Auth Code: " . $tresponse->getAuthCode() . "\n";
                echo " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";
            } else {
                echo "Transaction Failed \n";
                if ($tresponse->getErrors() != null) {
                    echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                }
            }
            // Or, print errors if the API request wasn't successful
        } else {
            echo "Transaction Failed \n";
            $tresponse = $response->getTransactionResponse();
        
            if ($tresponse != null && $tresponse->getErrors() != null) {
                echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
            } else {
                echo " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                echo " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
            }
        }
    } else {
        echo  "No response returned \n";
    }

    return $response;
}

if (!defined('DONT_RUN_SAMPLES')) {
    chargeCreditCard("2.23");
}