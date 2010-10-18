<?php

$nzshpcrt_gateways[$num]['name'] = 'Paypal Express Checkout';
$nzshpcrt_gateways[$num]['internalname'] = 'paypal_certified';
$nzshpcrt_gateways[$num]['function'] = 'gateway_paypal_certified';
$nzshpcrt_gateways[$num]['form'] = "form_paypal_certified";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_paypal_certified";
$nzshpcrt_gateways[$num]['payment_type'] = "paypal";

$nzshpcrt_gateways[$num]['supported_currencies']['currency_list'] = array('USD', 'CAD', 'AUD', 'EUR', 'GBP', 'JPY', 'NZD', 'CHF', 'HKD', 'SGD', 'SEK', 'HUF', 'DKK', 'PLN', 'NOK', 'CZK', 'MXN');
$nzshpcrt_gateways[$num]['supported_currencies']['option_name'] = 'paypal_curcode';


function gateway_paypal_certified($seperator, $sessionid)
  {
$_SESSION['paypalExpressMessage']= '	<h4>Transaction Canceled</h4>';
// ==================================
// PayPal Express Checkout Module
// ==================================

//'------------------------------------
//' The paymentAmount is the total value of 
//' the shopping cart, that was set 
//' earlier in a session variable 
//' by the shopping cart page
//'------------------------------------
//exit('<pre>'.print_r($_SESSION, true).'</pre>');
$paymentAmount = wpsc_cart_total(false);
$_SESSION['paypalAmount'] = $paymentAmount;
$_SESSION['paypalexpresssessionid'] = $sessionid;
paypal_certified_currencyconverter();
//exit($_SESSION['paypalAmount']);
//'------------------------------------
//' The currencyCodeType and paymentType 
//' are set to the selections made on the Integration Assistant 
//'------------------------------------
$currencyCodeType = get_option('paypal_curcode');
$paymentType = "Sale";

//'------------------------------------
//' The returnURL is the location where buyers return to when a
//' payment has been succesfully authorized.
//'
//' This is set to the value entered on the Integration Assistant
//'------------------------------------
//exit(get_option('transact_url'));
$transact_url = get_option('transact_url');
$returnURL =  $transact_url.$seperator."sessionid=".$sessionid."&gateway=paypal";

//'------------------------------------
//' The cancelURL is the location buyers are sent to when they hit the
//' cancel button during authorization of payment during the PayPal flow
//'
//' This is set to the value entered on the Integration Assistant 
//'------------------------------------
$cancelURL = $transact_url;
//'------------------------------------
//' Calls the SetExpressCheckout API call
//'
//' The CallShortcutExpressCheckout function is defined in the file PayPalFunctions.php,
//' it is included at the top of this file.
//'-------------------------------------------------
$resArray = CallShortcutExpressCheckout ($_SESSION['paypalAmount'], $currencyCodeType, $paymentType, $returnURL, $cancelURL);
//exit("<pre>".print_r($resArray,true)."</pre>");
$ack = strtoupper($resArray["ACK"]);
	if($ack=="SUCCESS")	{
		RedirectToPayPal ( $resArray["TOKEN"] );
	} else  {
		//Display a user friendly Error on the page using any of the following error information returned by PayPal
		$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
		$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
		$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
		$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
		
		echo "SetExpressCheckout API call failed. ";
		echo "Detailed Error Message: " . $ErrorLongMsg;
		echo "Short Error Message: " . $ErrorShortMsg;
		echo "Error Code: " . $ErrorCode;
		echo "Error Severity Code: " . $ErrorSeverityCode;
	}


 // header("Location: ".get_option('paypal_multiple_url')."?".$output);
  exit();
}
function paypal_certified_currencyconverter(){
	global $wpdb;
	$currency_code = $wpdb->get_results("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A);
	$local_currency_code = $currency_code[0]['code'];
	$paypal_currency_code = get_option('paypal_curcode');
	if($paypal_currency_code == '') {
		$paypal_currency_code = 'US';
  	}
//exit(get_option('currency_type'). " ".$paypal_currency_code);
	
  // Stupid paypal only accepts payments in one of 5 currencies. Convert from the currency of the users shopping cart to the curency which the user has specified in their paypal preferences.
  $curr=new CURRENCYCONVERTER();
	if($paypal_currency_code != $local_currency_code) {
		$paypal_currency_productprice = $curr->convert($_SESSION['paypalAmount'],$paypal_currency_code,$local_currency_code);
		$paypal_currency_shipping = $curr->convert($local_currency_shipping,$paypal_currency_code,$local_currency_code);
		//exit($paypal_currency_productprice . " " . $paypal_currency_shipping.' '.$local_currency_productprice . " " . $local_currency_code);
		 $base_shipping = $curr->convert($purchase_log['base_shipping'],$paypal_currency_code, $local_currency_code);
	} else {
		$paypal_currency_productprice = $_SESSION['paypalAmount'];
		$paypal_currency_shipping = $local_currency_shipping;
		$base_shipping = $purchase_log['base_shipping'];
	}
	 switch($paypal_currency_code) {
    case "JPY":
    $decimal_places = 0;
    break;
    
    case "HUF":
    $decimal_places = 0;
    
    default:
    $decimal_places = 2;
    break;
	}
	//echo "$paypal_currency_code|$local_currency_code";
	$_SESSION['paypalAmount'] = number_format(sprintf("%01.2f", $paypal_currency_productprice),$decimal_places,'.','');

	
}  
function processingfunctions()
{
global $wpdb, $wpsc_cart;
$sessionid = $_SESSION['paypalexpresssessionid'];

	if($_REQUEST['act']=='error'){
		session_start();
		$resArray=$_SESSION['reshash']; 
$_SESSION['paypalExpressMessage']= '
	<center>
	
	<table width="700" align="left">
	<tr>
			<td colspan="2" class="header">The PayPal API has returned an error!</td>
		</tr>
	';
	  //it will print if any URL errors 
		if(isset($_SESSION['curl_error_no'])) { 
				$errorCode= $_SESSION['curl_error_no'] ;
				$errorMessage=$_SESSION['curl_error_msg'] ;	
				$response = $_SESSION['response'];
				session_unset();	
	
	$_SESSION['paypalExpressMessage'].='
	<tr>
			<td>response:</td>
			<td><?php echo $response; ?></td>
		</tr>
	   
	<tr>
			<td>Error Number:</td>
			<td><?= $errorCode ?></td>
		</tr>
		<tr>
			<td>Error Message:</td>
			<td><?= $errorMessage ?></td>
		</tr>
		
		</center>
		</table>';
	 } else {
	
	/* If there is no URL Errors, Construct the HTML page with 
	   Response Error parameters.   
	   */
	$_SESSION['paypalExpressMessage'] .="
	
			<td>Ack:</td>
			<td>".$resArray['ACK']."</td>
		</tr>
		<tr>
			<td>Correlation ID:</td>
			<td>".$resArray['CORRELATIONID']."</td>
		</tr>
		<tr>
			<td>Version:</td>
			<td>".$resArray['VERSION']."</td>
		</tr>";
	
		$count=0;
		while (isset($resArray["L_SHORTMESSAGE".$count])) {		
			  $errorCode    = $resArray["L_ERRORCODE".$count];
			  $shortMessage = $resArray["L_SHORTMESSAGE".$count];
			  $longMessage  = $resArray["L_LONGMESSAGE".$count]; 
			  $count=$count+1; 
		$_SESSION['paypalExpressMessage'] .="
		<tr>
			<td>Error Number:</td>
			<td> $errorCode </td>
		</tr>
		<tr>
			<td>Short Message:</td>
			<td> $shortMessage </td>
		</tr>
		<tr>
			<td>Long Message:</td>
			<td> $longMessage </td>
		</tr>";
		
	 }//end while
	}// end else
	$_SESSION['paypalExpressMessage'] .="
	</center>
		</table>";

}else if($_REQUEST['act']=='do'){
	session_start();
	
	/* Gather the information to make the final call to
	   finalize the PayPal payment.  The variable nvpstr
	   holds the name value pairs
	   */
	   //exit(wpsc_cart_total(false));
	$token =urlencode($_REQUEST['token']);
	$paymentAmount =urlencode ($_SESSION['paypalAmount']);
	$paymentType = urlencode($_SESSION['paymentType']);
	$currCodeType = urlencode(get_option('paypal_curcode'));
	$payerID = urlencode($_REQUEST['PayerID']);
	$serverName = urlencode($_SERVER['SERVER_NAME']);
	$BN='Instinct_e-commerce_wp-shopping-cart_NZ';

	$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION=Sale&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName."&BUTTONSOURCE=".$BN ;
//	exit($nvpstr);
	 /* Make the call to PayPal to finalize payment
		If an error occured, show the resulting errors
		*/
	$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);
	
	/* Display the API response back to the browser.
	   If the response from PayPal was a success, display the response parameters'
	   If the response was an error, display the errors received using APIError.php.
	   */
	$ack = strtoupper($resArray["ACK"]);
	
	//echo('<pre>'.print_r($resArray, true).'</re>');
 	if($ack!="SUCCESS"){
 		$_SESSION['reshash']=$resArray;
 		$location = get_option('transact_url')."&act=error";
		$_SESSION['paypalExpressMessage'] = 'Completed';
 			// header("Location: $location");
 	}else{
		$transaction_id = $wpdb->escape($resArray['TRANSACTIONID']);
		switch($resArray['PAYMENTSTATUS']) {
			case 'Processed': // I think this is mostly equivalent to Completed
			case 'Completed':
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '2' WHERE `sessionid` = ".$sessionid." LIMIT 1");
			transaction_results($sessionid, false, $transaction_id);
			break;

			case 'Pending': // need to wait for "Completed" before processing
			$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `transactid` = '".$transaction_id."', `date` = '".time()."'  WHERE `sessionid` = ".$sessionid." LIMIT 1");
			break;
		}
		$_SESSION['paypalExpressMessage'] = $resArray['PAYMENTSTATUS'];
		$location = add_query_arg('sessionid', $sessionid, get_option('transact_url'));
		//echo $location;

		header("Location: $location");
		exit();
 	}
	//exit('<pre>'.print_r($resArray, true).'</pre>');
// 	$_SESSION['paypalExpressMessage'] ="
// 		<h4>Transaction Accepted Please Keep these References Handy.</h4>
// 		<table class='' >
// 			
// 			<tr>
// 				<td >
// 					Transaction ID:</td>
// 				<td>".$resArray['TRANSACTIONID']."</td>
// 			</tr>
// 			<tr>
// 				<td >
// 					Amount:</td>
// 				<td>".$currCodeType." ".$resArray['AMT']."</td>
// 			</tr>
// 		</table>";


				//unset session shopping cart
				@$_SESSION['nzshpcrt_serialized_cart'] = '';
				$_SESSION['nzshpcrt_cart'] = '';
				$_SESSION['nzshpcrt_cart'] = Array();	 
				//exit('HERE');
				$wpsc_cart->empty_cart();
				
} else if(isset($_REQUEST['paymentType']) || isset($_REQUEST['token'])){
	$token = $_REQUEST['token'];
	if(! isset($token)) {
		/* 
			The servername and serverport tells PayPal where the buyer
		   should be directed back to after authorizing payment.
		   In this case, its the local webserver that is running this script
		   Using the servername and serverport, the return URL is the first
		   portion of the URL that buyers will return to after authorizing payment
		*/
	
		   $paymentAmount=$_SESSION['paypalAmount'];
		   $currencyCodeType=get_option('paypal_curcode');
		   $paymentType='Sale';
	
		 /* The returnURL is the location where buyers return when a
			payment has been succesfully authorized.
			The cancelURL is the location buyers are sent to when they hit the
			cancel button during authorization of payment during the PayPal flow
			*/
		   if(get_option('permalink_structure') != '')
			{
			$seperator ="?";
			}
			else
			  {
			  $seperator ="&";
			  }
		   $returnURL =urlencode(get_option('transact_url').$seperator.'currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType.'&paymentAmount='.$paymentAmount);
		   $cancelURL =urlencode(get_option('transact_url').$seperator.'paymentType=$paymentType' );
	
		 /* Construct the parameter string that describes the PayPal payment
			the varialbes were set in the web form, and the resulting string
			is stored in $nvpstr
			*/
		  
		   $nvpstr="&Amt=".$paymentAmount."&PAYMENTACTION=".$paymentType."&ReturnUrl=".$returnURL."&CANCELURL=".$cancelURL ."&CURRENCYCODE=".$currencyCodeType;
		 
		 /* Make the call to PayPal to set the Express Checkout token
			If the API call succeded, then redirect the buyer to PayPal
			to begin to authorize payment.  If an error occured, show the
			resulting errors
			*/
		   $resArray=hash_call("SetExpressCheckout",$nvpstr);
		   $_SESSION['reshash']=$resArray;
	
		   $ack = strtoupper($resArray["ACK"]);
	
		   if($ack=="SUCCESS"){
			// Redirect to paypal.com here
			$token = urldecode($resArray["TOKEN"]);
			$payPalURL = PAYPAL_URL.$token;
			header("Location: ".$payPalURL);
		  	} else  {
			 //Redirecting to APIError.php to display errors. 
				$location = get_option('transact_url')."&act=error";
				header("Location: $location");
			}
			exit();
	} else {
	 /* At this point, the buyer has completed in authorizing payment
		at PayPal.  The script will now call PayPal with the details
		of the authorization, incuding any shipping information of the
		buyer.  Remember, the authorization is not a completed transaction
		at this state - the buyer still needs an additional step to finalize
		the transaction
		*/
	
	   $token =urlencode( $_REQUEST['token']);
	
	 /* Build a second API request to PayPal, using the token as the
		ID to get the details on the payment authorization
		*/
	   $nvpstr="&TOKEN=".$token;
	
	 /* Make the API call and store the results in an array.  If the
		call was a success, show the authorization details, and provide
		an action to complete the payment.  If failed, show the error
		*/
	   $resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
	   $_SESSION['reshash']=$resArray;
	   $ack = strtoupper($resArray["ACK"]);
	   if($ack=="SUCCESS"){			
			
/********************************************************
GetExpressCheckoutDetails.php

This functionality is called after the buyer returns from
PayPal and has authorized the payment.

Displays the payer details returned by the
GetExpressCheckoutDetails response and calls
DoExpressCheckoutPayment.php to complete the payment
authorization.

Called by ReviewOrder.php.

Calls DoExpressCheckoutPayment.php and APIError.php.

********************************************************/


session_start();

/* Collect the necessary information to complete the
   authorization for the PayPal payment
   */

$_SESSION['token']=$_REQUEST['token'];
$_SESSION['payer_id'] = $_REQUEST['PayerID'];

/*
$_SESSION['paymentAmount']=$_REQUEST['paymentAmount'];
$_SESSION['currCodeType']=$_REQUEST['currencyCodeType'];
$_SESSION['paymentType']=$_REQUEST['paymentType'];
*/

$resArray=$_SESSION['reshash'];

if(get_option('permalink_structure') != '')
{
$seperator ="?";
}
else
  {
  $seperator ="&";
  }

/* Display the  API response back to the browser .
   If the response from PayPal was a success, display the response parameters
   */
if(isset($_REQUEST['token']) && !isset($_REQUEST['PayerID'])){
$_SESSION['paypalExpressMessage']= '<h4>TRANSACTION CANCELED</h4>';
}else{
$output ="
           <table width='400' class='paypal_express_form'>
            <tr>
                <td align='left' class='firstcol'><b>Order Total:</b></td>
                <td align='left'>".$wpsc_cart->process_as_currency($_SESSION['paypalAmount']) ."</td>
            </tr>
			<tr>
			    <td align='left'><b>Shipping Address: </b></td>
			</tr>
            <tr>
                <td align='left' class='firstcol'>
                    Street 1:</td>
                <td align='left'>".$resArray['SHIPTOSTREET']."</td>

            </tr>
            <tr>
                <td align='left' class='firstcol'>
                    Street 2:</td>
                <td align='left'>".$resArray['SHIPTOSTREET2']."
                </td>
            </tr>
            <tr>
                <td align='left' class='firstcol'>
                    City:</td>

                <td align='left'>".$resArray['SHIPTOCITY']."</td>
            </tr>
            <tr>
                <td align='left' class='firstcol'>
                    State:</td>
                <td align='left'>".$resArray['SHIPTOSTATE']."</td>
            </tr>
            <tr>
                <td align='left' class='firstcol'>
                    Postal code:</td>

                <td align='left'>".$resArray['SHIPTOZIP']."</td>
            </tr>
            <tr>
                <td align='left' class='firstcol'>
                    Country:</td>
                <td align='left'>".$resArray['SHIPTOCOUNTRYNAME']."</td>
            </tr>
            <tr>
                <td>";
// 		$purchase_log = $wpdb->get_row("SELECT `id`,`billing_region` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= '".$wpdb->escape($_SESSION['paypalexpresssessionid']) ."' LIMIT 1", ARRAY_A) ;
// 		$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value, `".WPSC_TABLE_CHECKOUT_FORMS."`.`name`, `".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE  `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id']." ORDER BY `".WPSC_TABLE_CHECKOUT_FORMS."`.`order`";
// 
// 		$userinfo = $wpdb->get_results($usersql, ARRAY_A);
//                 
//  
$output .= "<form action=".get_option('transact_url')." method='post'>\n";
$output .= "	<input type='hidden' name='totalAmount' value='".wpsc_cart_total(false)."' />\n";
$output .= "	<input type='hidden' name='shippingStreet' value='".$resArray['SHIPTOSTREET']."' />\n";
$output .= "	<input type='hidden' name='shippingStreet2' value='".$resArray['SHIPTOSTREET2']."' />\n";
$output .= "	<input type='hidden' name='shippingCity' value='".$resArray['SHIPTOCITY']."' />\n";
$output .= "	<input type='hidden' name='shippingState' value='".$resArray['SHIPTOSTATE']."' />\n";
$output .= "	<input type='hidden' name='postalCode' value='".$resArray['SHIPTOZIP']."' />\n";
$output .= "	<input type='hidden' name='country' value='".$resArray['SHIPTOCOUNTRYNAME']."' />\n";
$output .= "	<input type='hidden' name='token' value='".$_SESSION['token']."' />\n";
$output .= "	<input type='hidden' name='PayerID' value='".$_SESSION['payer_id']."' />\n";
$output .= "	<input type='hidden' name='act' value='do' />\n";
$output .= "	<p>  <input name='usePayPal' type='submit' value='Make Payment' /></p>\n";
$output .= "</form>";

$output .= "<form action=".get_option('transact_url')." method='post'>\n";
$output .= "	<input type='hidden' name='totalAmount' value='".wpsc_cart_total(false)."' />\n";

// 		foreach((array)$userinfo as $key => $value){
// 			if(($value['unique_name']=='billingfirstname') && $value['value'] != ''){
// 				$data['SHIPTONAME']	= $value['value'];
// 			}
// 			if(($value['unique_name']=='billinglastname') && $value['value'] != ''){
// 				$data['SHIPTONAME']	.= " ".$value['value'];
// 			}
// 
// 			if(($value['unique_name']=='billingaddress') && $value['value'] != ''){
// 				$data['SHIPTOSTREET']	= $value['value'];
// 			}
// 			if(($value['unique_name']=='billingcity') && $value['value'] != ''){
// 				$data['SHIPTOCITY']	= $value['value'];
// 			}
// 			if(($value['unique_name']=='billingcountry') && $value['value'] != ''){
// 				$data['SHIPTOCOUNTRYCODE']	= $value['value'];
// 				$state =  $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` ='{$purchase_log['billing_region']}' LIMIT 1");
// 				if($purchase_log['billing_region'] > 0) {
// 					$data['SHIPTOSTATE'] = $state;
// 				}
// 			}
// 			if(($value['unique_name']=='billingpostcode') && $value['value'] != ''){
// 				$data['SHIPTOZIP']	= $value['value'];
// 			}
// 		}
// 
// $output .= "	<input type='text' name='shippingStreet' value='".$data['SHIPTOSTREET']."' />\n";
// $output .= "	<input type='text' name='shippingCity' value='".$data['SHIPTOCITY']."' />\n";
// $output .= "	<input type='text' name='shippingState' value='".$data['SHIPTOSTATE']."' />\n";
// $output .= "	<input type='text' name='postalCode' value='".$data['SHIPTOZIP']."' />\n";
// $output .= "	<input type='text' name='country' value='".$data['SHIPTOCOUNTRYCODE']."' />\n";
// $output .= "	<input type='text' name='token' value='".$_SESSION['token']."' />\n";
// $output .= "	<input type='text' name='PayerID' value='".$_SESSION['payer_id']."' />\n";
// $output .= "	<input type='hidden' name='act' value='do' />\n";
// $output .= "	<p>  <label for='useOther'>Use Previous Shipping Information:</label> <input name='useOther' type='submit' value='Make Payment' /></p>\n";
// $output .= "</form>";
$output .=" </td>
            </tr>
        </table>
    </center>
";
$_SESSION['paypalExpressMessage'] = $output;
		}
		  }
	}
}


}
function submit_paypal_certified()
  {  
  if($_POST['paypal_certified_apiuser'] != null)
    {
    update_option('paypal_certified_apiuser', $_POST['paypal_certified_apiuser']);
    }
    
  if($_POST['paypal_certified_apipass'] != null)
    {
    update_option('paypal_certified_apipass', $_POST['paypal_certified_apipass']);
    }
    
  if($_POST['paypal_curcode'] != null)
    {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
    }
    
  if($_POST['paypal_certified_apisign'] != null)
    {
    update_option('paypal_certified_apisign', $_POST['paypal_certified_apisign']);
    }

  if($_POST['paypal_certified_server_type'] != null) {
  	
  	update_option('paypal_certified_server_type', $_POST['paypal_certified_server_type']);
  	    //exit(get_option('paypal_certified_server_type').'<pre>'.print_r($_POST, true).'</pre>');
  } 
  foreach((array)$_POST['paypal_form'] as $form => $value) {
    update_option(('paypal_form_'.$form), $value);
	}
  return true;
  }

function form_paypal_certified()
  {
  global $wpdb, $wpsc_gateways;
  $select_currency[get_option('paypal_curcode')] = "selected='selected'";
  	if (get_option('paypal_certified_server_type') == 'sandbox'){
		$serverType1="checked='checked'";
	} elseif(get_option('paypal_certified_server_type') == 'production') {
		$serverType2 ="checked='checked'";
	}

  $output = "
  <tr>
      <td>API Username
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_certified_apiuser')."' name='paypal_certified_apiuser' />
      </td>
  </tr>
  <tr>
      <td>API Password
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_certified_apipass')."' name='paypal_certified_apipass' />
      </td>
  </tr>
  <tr>
     <td>API Signature
     </td>
     <td>
     <input type='text' size='70' value='".get_option('paypal_certified_apisign')."' name='paypal_certified_apisign' />
     </td>
  </tr>
  <tr>
     <td>Server Type
     </td>
     <td>
		<input $serverType1 type='radio' name='paypal_certified_server_type' value='sandbox' /> Sandbox (For testing)
		<input $serverType2 type='radio' name='paypal_certified_server_type' value='production' /> Production
	 </td>
  </tr>
  ";

	$store_currency_code = $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` IN ('".absint(get_option('currency_type'))."')");
	$current_currency = get_option('paypal_curcode');
	if($current_currency != $store_currency_code) {
		$output .= "
  <tr>
      <td colspan='2'><strong class='form_group'>".__('Currency Converter')."</td>
  </tr>
  <tr>
		<td colspan='2'>".__('If your website uses a currency not accepted by Paypal, select an accepted currency using the drop down menu bellow. Buyers on your site will still pay in your local currency however we will send the order through to Paypal using currency you choose below.')."</td>
		</tr>\n";
		
		$output .= "    <tr>\n";

		
		
		$output .= "    <td>Convert to </td>\n";
		$output .= "          <td>\n";
		$output .= "            <select name='paypal_curcode'>\n";

		$paypal_currency_list = $wpsc_gateways['paypal_multiple']['supported_currencies']['currency_list'];

		$currency_list = $wpdb->get_results("SELECT DISTINCT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `code` IN ('".implode("','",$paypal_currency_list)."')", ARRAY_A);
		foreach($currency_list as $currency_item) {
			$selected_currency = '';
			if($current_currency == $currency_item['code']) {
				$selected_currency = "selected='selected'";
			}
			$output .= "<option ".$selected_currency." value='{$currency_item['code']}'>{$currency_item['currency']}</option>";
		}
		$output .= "            </select> \n";
		$output .= "          </td>\n";
		$output .= "       </tr>\n";
	}
   
$output .= "
	<tr class='update_gateway' >
		<td colspan='2'>
			<div class='submit'>
			<input type='submit' value='Update &raquo;' name='updateoption'/>
		</div>
		</td>
	</tr>
	
    <tr style='background: none;'>
      <td colspan='2'>
				<h4>Forms Sent to Gateway</h4>
      </td>
		</tr>
   
    <tr>
      <td>
      First Name Field
      </td>
      <td>
      <select name='paypal_form[first_name]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_first_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Last Name Field
      </td>
      <td>
      <select name='paypal_form[last_name]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_last_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Address Field
      </td>
      <td>
      <select name='paypal_form[address]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_address'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      City Field
      </td>
      <td>
      <select name='paypal_form[city]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_city'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      State Field
      </td>
      <td>
      <select name='paypal_form[state]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_state'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Postal code/Zip code Field
      </td>
      <td>
      <select name='paypal_form[post_code]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_post_code'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Country Field
      </td>
      <td>
      <select name='paypal_form[country]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_country'))."
      </select>
      </td>
  </tr>
";
  return $output;
  }
  //paypalfunctions//
  /********************************************
	PayPal API Module
	 
	Defines all the global variables and the wrapper functions 
	********************************************/
	$PROXY_HOST = '127.0.0.1';
	$PROXY_PORT = '808';

	//'------------------------------------
	//' PayPal API Credentials 
	//'------------------------------------
	$API_UserName=get_option('paypal_certified_apiuser');
	$API_Password=get_option('paypal_certified_apipass');
	$API_Signature=get_option('paypal_certified_apisign');

	// BN Code 	is only applicable for partners
	$sBNCode = "PP-ECWizard";
	
	
	/*	
	' Define the PayPal Redirect URLs.  
	' 	This is the URL that the buyer is first sent to do authorize payment with their paypal account
	' 	change the URL depending if you are testing on the sandbox or the live PayPal site
	'
	' For the sandbox, the URL is       https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
	' For the live site, the URL is        https://www.paypal.com/webscr&cmd=_express-checkout&token=
	*/
	//$SandboxFlag = true;
	//exit(get_option('paypal_certified_server_type'));
  	if (get_option('paypal_certified_server_type') == 'sandbox'){
		$SandboxFlag=true;
	} elseif(get_option('paypal_certified_server_type') == 'production') {
		$SandboxFlag=false;
	}	
	if ($SandboxFlag == true) 
	{
		$API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
		$PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
	}
	else
	{
		$API_Endpoint = "https://api-3t.paypal.com/nvp";
		$PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
	}

	$USE_PROXY = false;
	$version="57.0";

	if (session_id() == "")
		session_start();

	/* An express checkout transaction starts with a token, that
	   identifies to PayPal your transaction
	   In this example, when the script sees a token, the script
	   knows that the buyer has already authorized payment through
	   paypal.  If no token was found, the action is to send the buyer
	   to PayPal to first authorize payment
	   */

	/*   
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
	' Inputs:  
	'		paymentAmount:  	Total value of the shopping cart
	'		currencyCodeType: 	Currency code value the PayPal API
	'		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
	'		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
	'		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	function CallShortcutExpressCheckout( $paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL) {
	global $wpdb;
		//------------------------------------------------------------------------------------------------------------------------------------
		// Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation
		//exit($cancelURL);
	$purchase_log = $wpdb->get_row("SELECT `id`,`billing_region` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= '".$wpdb->escape($_SESSION['paypalexpresssessionid']) ."' LIMIT 1", ARRAY_A) ;
	$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value, `".WPSC_TABLE_CHECKOUT_FORMS."`.`name`, `".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE  `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id']." ORDER BY `".WPSC_TABLE_CHECKOUT_FORMS."`.`order`";
		//exit($usersql);
		$userinfo = $wpdb->get_results($usersql, ARRAY_A);
//		print("<pre>".print_r($usersql,true)."</pre>");
//		print("<pre>".print_r($userinfo,true)."</pre>");

		$nvpstr="&Amt=". $paymentAmount;
		$nvpstr = $nvpstr . "&PAYMENTACTION=" . $paymentType;
		$nvpstr = $nvpstr . "&RETURNURL=" . $returnURL;
		$nvpstr = $nvpstr . "&CANCELURL=" . $cancelURL;
		$nvpstr = $nvpstr . "&CURRENCYCODE=" . $currencyCodeType;
		$data = array();
		
		foreach((array)$userinfo as $key => $value){
			if(($value['unique_name']=='billingfirstname') && $value['value'] != ''){
				$data['SHIPTONAME']	= $value['value'];
			}
			if(($value['unique_name']=='billinglastname') && $value['value'] != ''){
				$data['SHIPTONAME']	.= " ".$value['value'];
			}

			if(($value['unique_name']=='billingaddress') && $value['value'] != ''){
				$data['SHIPTOSTREET']	= $value['value'];
			}
			if(($value['unique_name']=='billingcity') && $value['value'] != ''){
				$data['SHIPTOCITY']	= $value['value'];
			}
			if(($value['unique_name']=='billingcountry') && $value['value'] != ''){
				$values = maybe_unserialize($value['value']);
				$data['SHIPTOCOUNTRYCODE']	= $values[0];
				$state =  $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` ='{$purchase_log['billing_region']}' LIMIT 1");
				if($purchase_log['billing_region'] > 0) {
					$data['SHIPTOSTATE'] = $state;
				}
			}
			if(($value['unique_name']=='billingpostcode') && $value['value'] != ''){
				$data['SHIPTOZIP']	= $value['value'];
			}
		}

	if(count($data) >= 4) {
		//$data['ADDROVERRIDE'] = 1;
		$temp_data = array();
		foreach($data as $key => $value) {
			$temp_data[] = $key."=".$value;
		}
		$nvpstr = $nvpstr . "&".implode("&",$temp_data);
	}
	
		//print("<pre>".print_r($data,true)."</pre>");
		//exit($nvpstr);
		$_SESSION["currencyCodeType"] = $currencyCodeType;	  
		$_SESSION["PaymentType"] = $paymentType;

		//'--------------------------------------------------------------------------------------------------------------- 
		//' Make the API call to PayPal
		//' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.  
		//' If an error occured, show the resulting errors
		//'---------------------------------------------------------------------------------------------------------------
	    $resArray=hash_call("SetExpressCheckout", $nvpstr);
		$ack = strtoupper($resArray["ACK"]);
		if($ack=="SUCCESS")
		{
			$token = urldecode($resArray["TOKEN"]);
			$_SESSION['TOKEN']=$token;
		}
		   
	    return $resArray;
	}

	/*   
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
	' Inputs:  
	'		paymentAmount:  	Total value of the shopping cart
	'		currencyCodeType: 	Currency code value the PayPal API
	'		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
	'		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
	'		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
	'		shipToName:		the Ship to name entered on the merchant's site
	'		shipToStreet:		the Ship to Street entered on the merchant's site
	'		shipToCity:			the Ship to City entered on the merchant's site
	'		shipToState:		the Ship to State entered on the merchant's site
	'		shipToCountryCode:	the Code for Ship to Country entered on the merchant's site
	'		shipToZip:			the Ship to ZipCode entered on the merchant's site
	'		shipToStreet2:		the Ship to Street2 entered on the merchant's site
	'		phoneNum:			the phoneNum  entered on the merchant's site
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	function CallMarkExpressCheckout( $paymentAmount, $currencyCodeType, $paymentType, $returnURL, 
									  $cancelURL, $shipToName, $shipToStreet, $shipToCity, $shipToState,
									  $shipToCountryCode, $shipToZip, $shipToStreet2, $phoneNum
									) 
	{
		//------------------------------------------------------------------------------------------------------------------------------------
		// Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation
		
		$nvpstr="&Amt=". $paymentAmount;
		$nvpstr = $nvpstr . "&PAYMENTACTION=" . $paymentType;
		$nvpstr = $nvpstr . "&ReturnUrl=" . $returnURL;
		$nvpstr = $nvpstr . "&CANCELURL=" . $cancelURL;
		$nvpstr = $nvpstr . "&CURRENCYCODE=" . $currencyCodeType;
		$nvpstr = $nvpstr . "&ADDROVERRIDE=1";
		$nvpstr = $nvpstr . "&SHIPTONAME=" . $shipToName;
		$nvpstr = $nvpstr . "&SHIPTOSTREET=" . $shipToStreet;
		$nvpstr = $nvpstr . "&SHIPTOSTREET2=" . $shipToStreet2;
		$nvpstr = $nvpstr . "&SHIPTOCITY=" . $shipToCity;
		$nvpstr = $nvpstr . "&SHIPTOSTATE=" . $shipToState;
		$nvpstr = $nvpstr . "&SHIPTOCOUNTRYCODE=" . $shipToCountryCode;
		$nvpstr = $nvpstr . "&SHIPTOZIP=" . $shipToZip;
		$nvpstr = $nvpstr . "&PHONENUM=" . $phoneNum;
		
		$_SESSION["currencyCodeType"] = $currencyCodeType;	  
		$_SESSION["PaymentType"] = $paymentType;

		//'--------------------------------------------------------------------------------------------------------------- 
		//' Make the API call to PayPal
		//' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.  
		//' If an error occured, show the resulting errors
		//'---------------------------------------------------------------------------------------------------------------
	    $resArray=hash_call("SetExpressCheckout", $nvpstr);
		$ack = strtoupper($resArray["ACK"]);
		if($ack=="SUCCESS")
		{
			$token = urldecode($resArray["TOKEN"]);
			$_SESSION['TOKEN']=$token;
		}
		   
	    return $resArray;
	}
	
	/*
	'-------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
	'
	' Inputs:  
	'		None
	' Returns: 
	'		The NVP Collection object of the GetExpressCheckoutDetails Call Response.
	'-------------------------------------------------------------------------------------------
	*/
	function GetShippingDetails( $token )
	{
		//'--------------------------------------------------------------
		//' At this point, the buyer has completed authorizing the payment
		//' at PayPal.  The function will call PayPal to obtain the details
		//' of the authorization, incuding any shipping information of the
		//' buyer.  Remember, the authorization is not a completed transaction
		//' at this state - the buyer still needs an additional step to finalize
		//' the transaction
		//'--------------------------------------------------------------
	   
	    //'---------------------------------------------------------------------------
		//' Build a second API request to PayPal, using the token as the
		//'  ID to get the details on the payment authorization
		//'---------------------------------------------------------------------------
	    $nvpstr="&TOKEN=" . $token;

		//'---------------------------------------------------------------------------
		//' Make the API call and store the results in an array.  
		//'	If the call was a success, show the authorization details, and provide
		//' 	an action to complete the payment.  
		//'	If failed, show the error
		//'---------------------------------------------------------------------------
	    $resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
	    $ack = strtoupper($resArray["ACK"]);
		if($ack == "SUCCESS")
		{	
			$_SESSION['payer_id'] =	$resArray['PAYERID'];
		} 
		return $resArray;
	}
	
	/*
	'-------------------------------------------------------------------------------------------------------------------------------------------
	' Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
	'
	' Inputs:  
	'		sBNCode:	The BN code used by PayPal to track the transactions from a given shopping cart.
	' Returns: 
	'		The NVP Collection object of the GetExpressCheckoutDetails Call Response.
	'--------------------------------------------------------------------------------------------------------------------------------------------	
	*/
	function ConfirmPayment( $FinalPaymentAmt )
	{
		/* Gather the information to make the final call to
		   finalize the PayPal payment.  The variable nvpstr
		   holds the name value pairs
		   */
		

		//Format the other parameters that were stored in the session from the previous calls	
		$token 				= urlencode($_SESSION['token']);
		$paymentType 		= urlencode($_SESSION['paymentType']);
		$currencyCodeType 	= urlencode($_SESSION['currencyCodeType']);
		$payerID 			= urlencode($_SESSION['payer_id']);

		$serverName 		= urlencode($_SERVER['SERVER_NAME']);

		$nvpstr  = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTACTION=' . $paymentType . '&AMT=' . $FinalPaymentAmt;
		$nvpstr .= '&CURRENCYCODE=' . $currencyCodeType . '&IPADDRESS=' . $serverName; 

		 /* Make the call to PayPal to finalize payment
		    If an error occured, show the resulting errors
		    */
		$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);

		/* Display the API response back to the browser.
		   If the response from PayPal was a success, display the response parameters'
		   If the response was an error, display the errors received using APIError.php.
		   */
		$ack = strtoupper($resArray["ACK"]);

		return $resArray;
	}

	/**
	  '-------------------------------------------------------------------------------------------------------------------------------------------
	  * hash_call: Function to perform the API call to PayPal using API signature
	  * @methodName is name of API  method.
	  * @nvpStr is nvp string.
	  * returns an associtive array containing the response from the server.
	  '-------------------------------------------------------------------------------------------------------------------------------------------
	*/
	function hash_call($methodName,$nvpStr)
	{
		//declaring of global variables
		global $API_Endpoint, $version, $API_UserName, $API_Password, $API_Signature;
		global $USE_PROXY, $PROXY_HOST, $PROXY_PORT;
		global $gv_ApiErrorURL;
		global $sBNCode;
		$version = 57;
		//setting the curl parameters.
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
	    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	   //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
		if($USE_PROXY)
			curl_setopt ($ch, CURLOPT_PROXY, $PROXY_HOST. ":" . $PROXY_PORT); 

		//NVPRequest for submitting to server
		$nvpreq="METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode($version) . "&PWD=" . urlencode($API_Password) . "&USER=" . urlencode($API_UserName) . "&SIGNATURE=" . urlencode($API_Signature) . $nvpStr . "&BUTTONSOURCE=" . urlencode($sBNCode);
		//exit('<pre>'.print_r($nvpreq,true).'</true>');
		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		//exit($nvpreq);
		//getting response from server
		$response = curl_exec($ch);
		//exit('<pre>'.print_r($response, true).'</pre>');
		//convrting NVPResponse to an Associative Array
		$nvpResArray=deformatNVP($response);
		$nvpReqArray=deformatNVP($nvpreq);
		$_SESSION['nvpReqArray']=$nvpReqArray;

		if (curl_errno($ch)) 
		{
			// moving to display page to display curl errors
			  $_SESSION['curl_error_no']=curl_errno($ch) ;
			  $_SESSION['curl_error_msg']=curl_error($ch);

			  //Execute the Error handling module to display errors. 
		} 
		else 
		{
			 //closing the curl
		  	curl_close($ch);
		}		return $nvpResArray;
	}

	/*'----------------------------------------------------------------------------------
	 Purpose: Redirects to PayPal.com site.
	 Inputs:  NVP string.
	 Returns: 
	----------------------------------------------------------------------------------
	*/
	function RedirectToPayPal ( $token )
	{
		global $PAYPAL_URL;
		
		// Redirect to paypal.com here
		$payPalURL = $PAYPAL_URL . $token;
		header("Location: ".$payPalURL);
	}

	
	/*'----------------------------------------------------------------------------------
	 * This function will take NVPString and convert it to an Associative Array and it will decode the response.
	  * It is usefull to search for a particular key and displaying arrays.
	  * @nvpstr is NVPString.
	  * @nvpArray is Associative Array.
	   ----------------------------------------------------------------------------------
	  */
function deformatNVP($nvpstr) {
	$intial=0;
	$nvpArray = array();

	while(strlen($nvpstr)) {
		//postion of Key
		$keypos= strpos($nvpstr,'=');
		//position of value
		$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

		/*getting the Key and Value values and storing in a Associative Array*/
		$keyval=substr($nvpstr,$intial,$keypos);
		$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
		//decoding the respose
		$nvpArray[urldecode($keyval)] =urldecode( $valval);
		$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	}
	return $nvpArray;
}
add_action('init', 'processingfunctions');
?>