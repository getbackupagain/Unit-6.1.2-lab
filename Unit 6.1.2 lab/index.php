<!DOCTYPE HTML>
<HTML>
<HEAD>

<!--  -->

<!-- Online Card Payment Simulator with payment authentication by Destry Krepps Oct 9, 2022
Takes credit card payment on this page and posts it to 'checkout_complete.php' for payment processing and inserting records into database table
Main update for this file was adding more input fields to accomodate the API.

notes:
- obviously sql injection was a concern, so as much as possible I have tried to limit input fields
- for numerical fields this has the unfortunate side affect of removing leading zeroes on inputs that might use them
- this isn't hard to work around, and is much more secure
- might add css to prettyfy things, but im running out of time

-->


<TITLE>Credit Card Payment Portal</TITLE>
<META charset="UTF-8">
  <META name="description" content="Payment Portal">
  <META name="keywords" content="HTML, CSS, JavaScript">
  <META name="author" content="Destry Krepps">
  <META name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- taken from w3 schools -->

<H1>  Destry's Payment Portal  </H1>

</HEAD>
<BODY>



<DIV class = "main content">
<FORM action="checkout_complete.php"  method="post">

<label for="card_payment">Payment Amount</label><br>
<input type="number" id="card_payment" name="card_payment" min="1" max="99"><br>

<label for="card_company">Company ("NA" if not applicable)</label><br>
<input type="text" id="card_company" name="card_company"><br>

<label for="card_email">Email Address</label><br>
<input type="text" id="card_email" name="card_email"><br>

<label for="card_fname">First Name on Card</label><br>
<input type="text" id="card_fname" name="card_fname"><br>

<label for="card_lname">Last Name on Card</label><br>
<input type="text" id="card_lname" name="card_lname"><br>

<label for="card_provider">Card Provider</label><br>
<input type="text" id="card_provider" name="card_provider"><br>

<label for="card_number">Card Number</label><br>
<input type="number" id="card_number" name="card_number"><br>

<label for="card_expiration_date">Expiration Date</label><br>
<input type="month" id="card_expiration_date" name="card_expiration_date"><br>

<label for="card_security_code">Security Code (CVC)</label><br>
<input type="number" id="card_security_code" name="card_security_code"><br>

<label for="card_country">Country</label><br>
<input type="text" id="card_country" name="card_country"><br>

<label for="card_address">Address</label><br>
<input type="text" id="card_address" name="card_address"><br>

<label for="card_city">City</label><br>
<input type="text" id="card_city" name="card_city"><br>

<label for="card_state">State ("NA" for countries other than U.S.A)</label><br>
<input type="text" id="card_state" name="card_state"><br>

<label for="card_zip">Zip</label><br>
<input type="number" id="card_zip" name="card_zip"><br>

<input type="submit" value="Submit">
</FORM>
</DIV>

</BODY>
</HTML>