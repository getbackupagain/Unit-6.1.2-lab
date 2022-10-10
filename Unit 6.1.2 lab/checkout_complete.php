<!DOCTYPE HTML>
<HTML>
<HEAD>

<!--  -->
<?php

include 'utils.php';
include 'php_charge_card.php';


$er_report = "problems included; <br>"; /* as problems are found, descriptions are concatenated onto this string*/
$payment_successful = true; /* pretty self explanatory, if something goes wrong, this guy gets tripped */
$tst_mssg = ""; /* leaving in for now in case i still need to test something*/

function validate_info() { /* runs payment info through a series of checks to see if it is valid. O(c)*/

  global $er_report, $payment_successful;

  $current_date_Ym = date("Y") . "-" . date("m");

  if((int)$_POST["card_payment"] > 99 ) { /* determined there was no reason in this hypothetical scenario that anyone should be spending more than $99*/
        $er_report .= "payment too large <br>"; /* and frankly it's easier to catch repetitive purchases in progress */
        $payment_successful = false;
  };
  if(!check_for_special_characters($_POST["card_fname"])) {/* checks if a given first name is valid */
    $er_report .= "Cardname first name valid <br>";
    $payment_successful = false;
  };
  if(!check_for_special_characters($_POST["card_lname"])) {/* checks if a given last name is valid */
    $er_report .= "Card last name not valid <br>";
    $payment_successful = false;
  };
  if(!check_if_valid_provider($_POST["card_provider"])) { /* checks if a given provider is valid */
    $er_report .= "Provider not valid <br>";
    $payment_successful = false;
  };
  if((int)$_POST["card_number"] > 9999999999999999.00 ) { /* card numbers aren't longer than 16 digits, though thanks to how html and php handles numbers, they can be shorter than 16 digits since leading zeros are removed */
    $er_report .= "card number not valid <br>";
    $payment_successful = false;
  };
  if($_POST["card_expiration_date"]<=$current_date_Ym) { /* checks if the card is expired */
    $er_report .= "card has expired <br>";
    $payment_successful = false;
  };
  if((int)$_POST["card_security_code"]>999.00) { /* cvcs aren't longer than 3 digits, but can have leading zeroes, so I have to allow numbers smaller than 3 digits */

    if(!(strtolower($_POST["card_provider"]) === "american express" && ($_POST["card_security_code"] <= 9999.00))) { /* except amex, who are special little princesses with 4 digit cvcs */

    $er_report .= "security code too large <br>"; 
    $payment_successful = false;

          };
  };
  if(!check_if_valid_country_or_state($_POST["card_country"],false,false)) { /* checks if a given coutry is valid */
    $er_report .= "not a valid country <br>";
    $payment_successful = false;
  };
  if((int)$_POST["card_zip"]>99999) {  /* once again, any zip larger than 6 digits is not valid (sort of, technically zip codes are longer than 6 digits but i dont feel like dealing with that problem yet) */
    $er_report .= "not a valid zip <br>"; /* but i still have to account for leading zeroes */
    $payment_successful = false;
  };
  if(check_for_special_characters($_POST["card_company"])) { // For now, I'm just going to scrub company names down the usual way
    $er_report .= "not a valid company <br>";
    $payment_successful = false;

  };
  if(!filter_var($_POST["card_email"], FILTER_VALIDATE_EMAIL)) {  //apparently php has a builtin function for validating email addresses, nifty
    $er_report .= "not a valid email address <br>";
    $payment_successful = false;

  };
  if(check_for_special_characters($_POST["card_address"])) {// sorry if you need hyphens in your streetname, but I'm erring on the side of caution for now
    $er_report .= "not a valid street address <br>";
    $payment_successful = false;

  };
  if(check_for_special_characters($_POST["card_city"])) { // gets the normal scrubbing treatment
    $er_report .= "not a valid city <br>";
    $payment_successful = false;

  };
  if(!check_if_valid_country_or_state($_POST["card_state"],true,false)) { // checks if the state is valid
    $er_report .= "not a valid state <br>";
    $payment_successful = false;

  };


  if(!$payment_successful) {return;}; /* if something has gone wrong, the program should not proceed onward*/
  /* otherwise everything is successful and the program tries to insert the data in the database, numbers that arent payment amount are coerced into integers since that's what they should be, and i don't want funny business */
  payment_valid($_POST["card_payment"],
  $_POST["card_fname"],
  $_POST["card_lname"],
  $_POST["card_provider"],
  (int)$_POST["card_number"], 
  $_POST["card_expiration_date"],
  (int)$_POST["card_security_code"],
  abbreviate_country_or_state($_POST["card_country"],false),// to conserve database space, i'm abbreviating country and state names
  (int)$_POST["card_zip"],
  $_POST["card_company"],
  $_POST["card_email"],
  $_POST["card_address"],
  $_POST["card_city"],
  abbreviate_country_or_state($_POST["card_state"],true)

);

};


function payment_valid($amnt,$fname,$lname,$prov,$num, $date,$sc,$count,$zip,$company,$email,$address,$city,$state ) { /* if everything checks out, the card is charged and records are put into database tables. O(c)*/
/* any numerical value that isn't a payment amount should be an integer */
  global $db_con, $er_report, $payment_successful/*, $tst_mssg */;
  $invoice_num = random_int(1, 999999);
  $customer_data = random_int(1, 99999999999);


  /* encrypts and authenticates card number and security code*/
  // I don't like how many columns I'm using, but I don't have time to come up with a better solution
  $tr_v_s = 
  " ".$invoice_num.
  ", '".$fname.
  "', '".$lname.
  "', ".$customer_data.
  ", '".$company.
  "', ".$email.
  "', ".$amnt.
  ",  '".$prov.
  "', '".encryption($num).
  "', '".$date."-01', '"
  .encryption($sc).
  "', '".$count.
  "', '".$address.
  "', '".$city.
  "', '".$state.
  "', ".$zip.
  " " ;
  $sql_tr_ul = "INSERT INTO unit_six_transaction_record (invoice_number, first_name, last_name, customer_data, company, email_address, card_charge_amount, card_provider, card_number_enc, card_exp_date, card_security_code_enc, billing_country, billing_address, billing_city, billing_state, billing_zip)
  VALUES ($tr_v_s);";
//16 columns + 2 (date, id) = 18 total


  /*$tst_mssg = $sql_ul; keeping for now, in case I have to check if something went wrong*/

  $upload = $db_con->query($sql_tr_ul);

  if(!$upload) {$er_report .= "DB connection error with transaction_record table"; 
    $payment_successful = false;
    return;};

      // loads up an array with the payment information
  $ap_info = array( 
   "amount"=> $amnt,
   "invoice_number"=>$invoice_num,
   "card_provider"=>$prov,
   "card_number"=>$num,
   "card_security_code"=>$sc,
   "card_exp_date"=>$date,
   "card_fname"=>$fname,
   "card_lname"=>$lname,
   "customer_data"=>$customer_data,
   "billing_country"=>$count,
   "billing_address"=>$address,
   "billing_city"=>$city,
   "billing_state"=>$state,
   "billing_zip"=>$zip,
   "company"=>$company,
   "customer_email"=>$email
  );

  $auth_result = chargeCreditCard($ap_info);// card is charged, and a result is returned
  $artr = $auth_result->getTransactionResponse(); // the response is taken

  $apr_v_s = // and the result of the response is stored in a different table
  " ".$invoice_num.
  ", '".$artr->getResponseCode().
  "', '".$artr->getAuthCode().
  "', '".$artr->getAvsResultCode().
  "', '".$artr->getCvvResultCode().
  "', '".$artr->getCavvResultCode().
  "', '".$artr->getTransId().
  "', '".$artr->getRefTransID().
  "', '".$artr->getTransHash().
  "', '".$artr->getTestRequest().
  "', '".$artr->getAccountNumber().
  "', '".$artr->getAccountType().
  "', '".$artr->getMessages()[0]->getCode().
  "', '".$artr->getMessages()[0]->getDescription().
  "', '".$artr->getTransHashSha2().
  "', '".$artr->getSupplementalDataQualificationIndicator().
  "', '".$artr->getNetworkTransId().
  "' " ;

  $sql_apr_ul = "INSERT INTO unit_six_authorized_payments_record (invoice_number, response_code, auth_code, avs_result_code, cvv_result_code, cavv_result_code, trans_id, ref_trans_id, trans_hash, test_request, account_number, account_type, message_code, message_description, trans_hash_sha2, supplemental_data_qualification_indicator, network_trans_id)
  VALUES ($apr_v_s);";

  $upload = $db_con->query($sql_apr_ul);

  if(!$upload) {
    $er_report .= "DB connection error with authorized_payments_record table"; 
    $payment_successful = false;
    return;};


  return;


};


?>


<TITLE>Checkout Page</TITLE>
<META charset="UTF-8">
  <META name="description" content="Payment Portal Checkout">
  <!-- you can rename the file, but never change the description once it's set -->
  <META name="keywords" content="HTML, CSS, JavaScript">
  <META name="author" content="Destry Krepps">
  <META name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- taken from w3 schools -->

<H1>  Thanks For Shopping With Us!  </H1>

</HEAD>
<BODY>

<DIV class = "main content">
<?php

validate_info();
if($payment_successful) {echo "Checkout Success $tst_mssg";} else {echo "Something went wrong <br> $er_report";};

?>


</DIV>
<br>
<A href="index.php">Go Back</A>
</BODY>
</HTML>