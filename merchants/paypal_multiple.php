<?php
$nzshpcrt_gateways[$num]['name'] = 'Paypal Payments Standard';
$nzshpcrt_gateways[$num]['internalname'] = 'paypal_multiple';
$nzshpcrt_gateways[$num]['function'] = 'gateway_paypal_multiple';
$nzshpcrt_gateways[$num]['form'] = "form_paypal_multiple";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_paypal_multiple";
$nzshpcrt_gateways[$num]['payment_type'] = "paypal";
$nzshpcrt_gateways[$num]['supported_currencies']['currency_list'] = array('USD', 'CAD', 'AUD', 'EUR', 'GBP', 'JPY', 'NZD', 'CHF', 'HKD', 'SGD', 'SEK', 'HUF', 'DKK', 'PLN', 'NOK', 'CZK', 'MXN');
$nzshpcrt_gateways[$num]['supported_currencies']['option_name'] = 'paypal_curcode';


function gateway_paypal_multiple($seperator, $sessionid) {
  global $wpdb, $wpsc_cart;
  $purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;

	if ($purchase_log['totalprice']==0) {
		header("Location: ".get_option('transact_url').$seperator."sessionid=".$sessionid);
		exit();
	}
	
  $cart_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='".$purchase_log['id']."'";
  $cart = $wpdb->get_results($cart_sql,ARRAY_A) ;
  //written by allen
  //exit("<pre>".print_r($cart,true)."</pre>");
  $member_subtype = get_product_meta($cart[0]['prodid'],'is_permenant',true);
  $status = get_product_meta($cart[0]['prodid'],'is_membership',true);
  $is_member = $status;
  $is_perm = $member_subtype;
  //end of written by allen
  $transact_url = get_option('transact_url');
  // paypal connection variables
  $data['business'] = get_option('paypal_multiple_business');
  $data['return'] = urlencode($transact_url.$seperator."sessionid=".$sessionid."&gateway=paypal");
  $data['cancel_return'] = urlencode($transact_url);
  $data['notify_url'] =urlencode(get_option('siteurl')."/?ipn_request=true");
  $data['rm'] = '2';
  //data['bn'] = 'Instinct-WP-e-commerce_ShoppingCart_EC';
  
  // look up the currency codes and local price

  $currency_code = $wpdb->get_results("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A);
  $local_currency_code = $currency_code[0]['code'];
  $paypal_currency_code = get_option('paypal_curcode');
  
  if($paypal_currency_code == '') {
		$paypal_currency_code = 'US';
  }
//exit(get_option('currency_type'). " ".$paypal_currency_code);

  // Stupid paypal only accepts payments in one of 5 currencies. Convert from the currency of the users shopping cart to the curency which the user has specified in their paypal preferences.
  $curr=new CURRENCYCONVERTER();
  
  $data['currency_code'] = $paypal_currency_code;
//   $data['lc'] = 'US';
  $data['lc'] = $paypal_currency_code;
  $data['bn'] = 'wp-e-commerce';
  if(get_option('address_override') == 1) {
		$data['address_override'] = '1';
  }
  if((int)(bool)get_option('paypal_ship') == '1'){
    $data['no_shipping'] = '0';
	$data['address_override'] = '1';
  }

  $data['no_note'] = '1';
  
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
  
  $i = 1;
  
  $all_donations = true;
  $all_no_shipping = true;
  
  
	$total = $wpsc_cart->calculate_total_price();

	$discount = $wpsc_cart->coupons_amount;
	//exit($discount);
	if(($discount > 0)) {
		if($paypal_currency_code != $local_currency_code) {
			$paypal_currency_productprice = $curr->convert( $wpsc_cart->calculate_total_price(),$paypal_currency_code,$local_currency_code);
			$paypal_currency_shipping = $curr->convert($local_currency_shipping,$paypal_currency_code,$local_currency_code);
			 $base_shipping = $curr->convert($wpsc_cart->calculate_total_shipping(),$paypal_currency_code, $local_currency_code);
			 $tax_price = $curr->convert($item['tax_charged'],$paypal_currency_code, $local_currency_code);
		} else {
			$paypal_currency_productprice =  $wpsc_cart->calculate_total_price();
			$paypal_currency_shipping = $local_currency_shipping;
			$base_shipping = $wpsc_cart->calculate_total_shipping();
			 $tax_price = $item['tax_charged'];
		}
		$data['item_name_'.$i] = "Your Shopping Cart";
		$data['amount_'.$i] = number_format(sprintf("%01.2f",$paypal_currency_productprice),$decimal_places,'.','');
		$data['quantity_'.$i] = 1;
		// $data['item_number_'.$i] = 0;
		$data['shipping_'.$i] = 0;
		$data['shipping2_'.$i] = 0;
		$data['handling_'.$i] = 0;
		$i++;
	} else {
		foreach((array)$cart as $item) {
			$product_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$item['prodid']."' LIMIT 1",ARRAY_A);
			$product_data = $product_data[0];
			if ((float)$item['price'] == 0 ) {
				continue;
			}
			$variation_count = count($product_variations);
			$local_currency_productprice = $item['price'];
			$local_currency_shipping = $item['pnp']/$item['quantity'];
			
			if($paypal_currency_code != $local_currency_code) {
				$paypal_currency_productprice = $curr->convert($local_currency_productprice,$paypal_currency_code,$local_currency_code);
				$paypal_currency_shipping = $curr->convert($local_currency_shipping,$paypal_currency_code,$local_currency_code);
			//	exit($paypal_currency_productprice . " " . $paypal_currency_shipping.' '.$local_currency_productprice . " " . $local_currency_code);
				 $base_shipping = $curr->convert($wpsc_cart->calculate_base_shipping(),$paypal_currency_code, $local_currency_code);
				 	//exit($paypal_currency_productprice.' Local>'.$local_currency_productprice.' Base shp'.$base_shipping);
				 $tax_price = $curr->convert($item['tax_charged'],$paypal_currency_code, $local_currency_code);
			} else {
				$paypal_currency_productprice = $local_currency_productprice;
				$paypal_currency_shipping = $local_currency_shipping;
				$base_shipping = $wpsc_cart->calculate_base_shipping();
				 $tax_price = $item['tax_charged'];
			}
			//exit("<pre>".print_r(, true).'</pre>');
			$data['item_name_'.$i] = urlencode(stripslashes($item['name']));
			$data['amount_'.$i] = number_format(sprintf("%01.2f", $paypal_currency_productprice),$decimal_places,'.','');
			$data['tax_'.$i] = number_format(sprintf("%01.2f",$tax_price),$decimal_places,'.','');
			$data['quantity_'.$i] = $item['quantity'];
			$data['item_number_'.$i] = $product_data['id'];
			if($item['donation'] !=1) {
				$all_donations = false;
				$data['shipping_'.$i] = number_format($paypal_currency_shipping,$decimal_places,'.','');
				$data['shipping2_'.$i] = number_format($paypal_currency_shipping,$decimal_places,'.','');      
			} else {
				$data['shipping_'.$i] = number_format(0,$decimal_places,'.','');
				$data['shipping2_'.$i] = number_format(0,$decimal_places,'.','');
			}
					
			if($product_data['no_shipping'] != 1) {
				$all_no_shipping = false;
			}
			
			$data['handling_'.$i] = '';
			$i++;
		}
	}
  $data['tax'] = '';
 	
  //exit($base_shipping);
  if(($base_shipping > 0) && ($all_donations == false) && ($all_no_shipping == false)) {
    $data['handling_cart'] = number_format($base_shipping,$decimal_places,'.','');
	}
  
  $data['custom'] = '';
  $data['invoice'] = $sessionid;
  
  // User details   
  
  if($_POST['collected_data'][get_option('paypal_form_first_name')] != '') {
    $data['first_name'] = urlencode($_POST['collected_data'][get_option('paypal_form_first_name')]);
	}
    
  if($_POST['collected_data'][get_option('paypal_form_last_name')] != '') {   
    $data['last_name'] = urlencode($_POST['collected_data'][get_option('paypal_form_last_name')]);
	}
    
  if($_POST['collected_data'][get_option('paypal_form_address')] != '') {   
    $address_rows = explode("\n\r",$_POST['collected_data'][get_option('paypal_form_address')]);
    $data['address1'] = urlencode(str_replace(array("\n", "\r"), '', $address_rows[0]));
    unset($address_rows[0]);    
    if($address_rows != null) {
			$data['address2'] = implode(", ",$address_rows);
    } else {
			$data['address2'] = '';
    }
	}
  
	if($_POST['collected_data'][get_option('paypal_form_city')] != '') {
		$data['city'] = urlencode($_POST['collected_data'][get_option('paypal_form_city')]); 
	}
	
     
    if($_POST['collected_data'][get_option('paypal_form_state')] != '') {   
    	if(!is_array($_POST['collected_data'][get_option('paypal_form_state')])){
    		$data['state'] =  urlencode($_POST['collected_data'][get_option('paypal_form_state')]); 
    	}
    }   
      if($_POST['collected_data'][get_option('paypal_form_country')] != '') {
    	if(is_array($_POST['collected_data'][get_option('paypal_form_country')])) {
    	  $country = $_POST['collected_data'][get_option('paypal_form_country')][0];
    	  $id = $_POST['collected_data'][get_option('paypal_form_country')][1];
    	  $state = wpsc_get_state_by_id($id, 'code');
    	} else {
				$country = $_POST['collected_data'][get_option('paypal_form_country')];
    	}
   		$data['country'] = urlencode($country);
   		if($state != ''){
   			$data['state'] = $state;
   		}
	}  
    if(is_numeric($_POST['collected_data'][get_option('paypal_form_post_code')])) {
    	$data['zip'] =  urlencode($_POST['collected_data'][get_option('paypal_form_post_code')]); 
    }
  // Change suggested by waxfeet@gmail.com, if email to be sent is not there, dont send an email address        
  	$email_data = $wpdb->get_results("SELECT `id`,`type` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1'",ARRAY_A);
    foreach((array)$email_data as $email) {
    	$data['email'] = $_POST['collected_data'][$email['id']];
	}
    
    if(($_POST['collected_data'][get_option('email_form_field')] != null) && ($data['email'] == null)) {
    	$data['email'] = $_POST['collected_data'][get_option('email_form_field')];
	}
	
  $data['upload'] = '1';
  $data['cmd'] = "_ext-enter";
  $data['redirect_cmd'] = "_cart";
  $data = apply_filters('wpsc_paypal_standard_post_data',$data);

  $datacount = count($data);
  $num = 0;
//  exit('<pre>'.print_r($data,true).'</pre>');
  foreach($data as $key=>$value) {
    $amp = '&';
    $num++;
    if($num == $datacount) {
      $amp = '';
      }
    //$output .= $key.'='.urlencode($value).$amp;
    $output .= $key.'='.$value.$amp;
	}
  if(get_option('paypal_ipn') == 0) { //ensures that digital downloads still work for people without IPN, less secure, though
    //$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '2' WHERE `sessionid` = ".$sessionid." LIMIT 1");
    }
	//written by allen
  if ($is_member == '1') {
		$membership_length = get_product_meta($cart[0]['prodid'],'membership_length',true);
		if ($is_perm == '1'){
			$permsub = '&src=1';
		} else {
			$permsub = '';
		}
			$output = 'cmd=_xclick-subscriptions&currency_code='.urlencode($data['currency_code']).'&lc='.urlencode($data['lc']).'&business='.urlencode($data['business']).'&no_note=1&item_name='.urlencode($data['item_name_1']).'&return='.urlencode($data['return']).'&cancel_return='.urlencode($data['cancel_return']).$permsub.'&a3='.urlencode($data['amount_1']).'&p3='.urlencode($membership_length['length']).'&t3='.urlencode(strtoupper($membership_length['unit']));
	}
	if(defined('WPSC_ADD_DEBUG_PAGE') and (WPSC_ADD_DEBUG_PAGE == true) ) {
  	echo "<a href='".get_option('paypal_multiple_url')."?".$output."'>Test the URL here</a>";
  	echo "<pre>".print_r($data,true)."</pre>";
		// 	echo "<pre>".print_r($_POST,true)."</pre>";
  	exit();
	}
  header("Location: ".get_option('paypal_multiple_url')."?".$output);
  exit();
}
  
function nzshpcrt_paypal_ipn() {
  global $wpdb;
  // needs to execute on page start
  // look at page 36
  //exit(WPSC_GATEWAY_DEBUG );
  if(($_GET['ipn_request'] == 'true') && (get_option('paypal_ipn') == 1)) {
    // read the post from PayPal system and add 'cmd'
    $fields = 'cmd=_notify-validate';
    $message = "";
    foreach ($_POST as $key => $value) {
      $value = urlencode(stripslashes($value));
      $fields .= "&$key=$value";
		}
    
    
    // post back to PayPal system to validate
    $results = '';
    if(function_exists('curl_init')) {
      $ch=curl_init(); 
			curl_setopt($ch, CURLOPT_URL, get_option('paypal_multiple_url'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_USERAGENT, "WP e-Commerce ".WPSC_PRESENTABLE_VERSION);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$results = curl_exec($ch);
			curl_close($ch);
    } else {
			$replace_strings[0] = 'http://';
			$replace_strings[1] = 'https://';
			$replace_strings[2] = '/cgi-bin/webscr';
			
			$paypal_url = str_replace($replace_strings, "",get_option('paypal_multiple_url'));
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			$fp = fsockopen($paypal_url, 80, $errno, $errstr, 30);
			if($fp) {
				fputs ($fp, $header . $fields);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					$results .= $fields;
				}
				fclose ($fp);
			}
    }
    
    
    // assign posted variables to local variables
    $sessionid = $_POST['invoice'];
    $transaction_id = $_POST['txn_id'];
    $verification_data['item_name'] = $_POST['item_name'];
    $verification_data['item_number'] = $_POST['item_number'];
    $verification_data['payment_status'] = $_POST['payment_status'];
    $verification_data['payment_amount'] = $_POST['mc_gross'];
    $verification_data['payment_currency'] = $_POST['mc_currency'];
    $verification_data['txn_id'] = $_POST['txn_id'];
    $verification_data['receiver_email'] = $_POST['receiver_email'];
    $verification_data['payer_email'] = $_POST['payer_email'];

		if(strcmp ($results, "VERIFIED") == 0){
			switch($verification_data['payment_status']) {
				case 'Processed': // I think this is mostly equivalent to Completed
				case 'Completed':
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '2' WHERE `sessionid` = ".$sessionid." LIMIT 1");
				transaction_results($sessionid, false, $transaction_id);
				break;

				case 'Failed': // if it fails, delete it
				$log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`='$sessionid' LIMIT 1");
				$delete_log_form_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$log_id'";
				$cart_content = $wpdb->get_results($delete_log_form_sql,ARRAY_A);
				foreach((array)$cart_content as $cart_item) {
					$cart_item_variations = $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id` = '".$cart_item['id']."'", ARRAY_A);
				}
				$wpdb->query("DELETE FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$log_id'");
				$wpdb->query("DELETE FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` IN ('$log_id')");
				$wpdb->query("DELETE FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='$log_id' LIMIT 1");
				break;

				case 'Pending':      // need to wait for "Completed" before processing
				$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `transactid` = '".$transaction_id."', `date` = '".time()."'  WHERE `sessionid` = ".$sessionid." LIMIT 1";
				$wpdb->query($sql) ;
				break;

				default: // if nothing, do nothing, safest course of action here.
				break;
			}
		} else if (strcmp ($results, "INVALID") == 0) {
			// Its already logged, not much need to do more
		}

		/*
		* Detect use of sandbox mode, if sandbox mode is present, send debugging email.
		*/
		if(stristr(get_option('paypal_multiple_url'), "sandbox") || (defined('WPSC_ADD_DEBUG_PAGE') and (WPSC_ADD_DEBUG_PAGE == true)) ) {
			$message = "This is a debugging message sent because it appears that you are using sandbox mode.\n\rIt is only sent if the paypal URL contains the word \"sandbox\"\n\r\n\r";
			$message .= "RESULTS:\n\r".print_r($results,true)."\n\r\n\r";
			$message .= "OUR_POST:\n\r".print_r($header . $req,true)."\n\r\n\r";
			$message .= "THEIR_POST:\n\r".print_r($_POST,true)."\n\r\n\r";
			$message .= "GET:\n\r".print_r($_GET,true)."\n\r\n\r";
			$message .= "SERVER:\n\r".print_r($_SERVER,true)."\n\r\n\r";
			//$wpdb->query("INSERT INTO `paypal_log` ( `id` , `text` , `date` ) VALUES ( '', '$message', NOW( ) );");
			mail(get_option('purch_log_email'), "IPN Data", $message);
		}    
		exit();
	}
}
  

function submit_paypal_multiple(){
  if($_POST['paypal_multiple_business'] != null) {
    update_option('paypal_multiple_business', $_POST['paypal_multiple_business']);
	}
    
  if($_POST['paypal_multiple_url'] != null) {
    update_option('paypal_multiple_url', $_POST['paypal_multiple_url']);
	}
    
  if($_POST['paypal_curcode'] != null) {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
	}
    
  if($_POST['paypal_curcode'] != null) {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
	}
    
  if($_POST['paypal_ipn'] != null) {
    update_option('paypal_ipn', (int)$_POST['paypal_ipn']);
	}

  if($_POST['address_override'] != null) {
    update_option('address_override', (int)$_POST['address_override']);
	}
  if($_POST['paypal_ship'] != null) {
    update_option('paypal_ship', (int)$_POST['paypal_ship']);
	}  
    
  foreach((array)$_POST['paypal_form'] as $form => $value) {
    update_option(('paypal_form_'.$form), $value);
	}
	
  return true;
}

function form_paypal_multiple() {
  global $wpdb, $wpsc_gateways;
  $output = "
  <tr>
      <td>Username:
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_business')."' name='paypal_multiple_business' />
      </td>
  </tr>
  <tr>
      <td>Url:
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_url')."' name='paypal_multiple_url' /> <br />
   
      </td>
  </tr>
  ";
  
  
	$paypal_ipn = get_option('paypal_ipn');
	$paypal_ipn1 = "";
	$paypal_ipn2 = "";
	switch($paypal_ipn) {
		case 0:
		$paypal_ipn2 = "checked ='checked'";
		break;
		
		case 1:
		$paypal_ipn1 = "checked ='checked'";
		break;
	}
	$paypal_ship = get_option('paypal_ship');
	$paypal_ship1 = "";
	$paypal_ship2 = "";	
	switch($paypal_ship){
		case 1:
		$paypal_ship1 = "checked='checked'";
		break;
		
		case 0:
		default:
		$paypal_ship2 = "checked='checked'";
		break;
	
	}
	$address_override = get_option('address_override');
	$address_override1 = "";
	$address_override2 = "";
	switch($address_override) {
		case 1:
		$address_override1 = "checked ='checked'";
		break;
		
		case 0:
		default:
		$address_override2 = "checked ='checked'";
		break;
	}
	$output .= "
   <tr>
     <td>IPN :
     </td>
     <td>
       <input type='radio' value='1' name='paypal_ipn' id='paypal_ipn1' ".$paypal_ipn1." /> <label for='paypal_ipn1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='paypal_ipn' id='paypal_ipn2' ".$paypal_ipn2." /> <label for='paypal_ipn2'>".__('No', 'wpsc')."</label>
     </td>
  </tr>
  <tr>
     <td style='padding-bottom: 0px;'>Send shipping details:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='paypal_ship' id='paypal_ship1' ".$paypal_ship1." /> <label for='paypal_ship1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='paypal_ship' id='paypal_ship2' ".$paypal_ship2." /> <label for='paypal_ship2'>".__('No', 'wpsc')."</label>

  	</td>
  </tr>
  <tr>
  	<td colspan='2'>
  	<span  class='wpscsmall description'>
  	Note: If your checkout page does not have a shipping details section, or if you don't want to send Paypal shipping information. You should change Send shipping details option to No.</span>
  	</td>
  </tr>
  <tr>
     <td style='padding-bottom: 0px;'>
      Address Override:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='address_override' id='address_override1' ".$address_override1." /> <label for='address_override1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='address_override' id='address_override2' ".$address_override2." /> <label for='address_override2'>".__('No', 'wpsc')."</label>
     </td>
   </tr>
   <tr>
  	<td colspan='2'>
  	<span  class='wpscsmall description'>
  	This setting affects your PayPal purchase log. If your customers already have a PayPal account PayPal will try to populate your PayPal Purchase Log with their PayPal address. This setting tries to replace the address in the PayPal purchase log with the Address customers enter on your Checkout page.
  	</span>
  	</td>
   </tr>\n";



	$store_currency_data = $wpdb->get_row("SELECT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` IN ('".absint(get_option('currency_type'))."')", ARRAY_A);
	$current_currency = get_option('paypal_curcode');
	if(($current_currency == '') && in_array($store_currency_data['code'], $wpsc_gateways['paypal_multiple']['supported_currencies']['currency_list'])) {
		update_option('paypal_curcode', $store_currency_data['code']);
		$current_currency = $store_currency_data['code'];
	}

		//	exit($current_currency.'<br />'.$store_currency_data['code']);
	if($current_currency != $store_currency_data['code']) {
		$output .= "
  <tr>
      <td colspan='2'><strong class='form_group'>".__('Currency Converter')."</td>
  </tr>
  <tr>
		<td colspan='2'>".sprintf(__('Your website uses <strong>%s</strong>. This currency is not supported by PayPal, please  select a currency using the drop down menu below. Buyers on your site will still pay in your local currency however we will send the order through to Paypal using the currency you choose below.', 'wpsc'), $store_currency_data['currency'])."</td>
		</tr>\n";
		
		$output .= "    <tr>\n";

		
		
		$output .= "    <td>Select Currency:</td>\n";
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
			<input type='submit' value='".__('Update &raquo;', 'wpsc')."' name='updateoption'/>
		</div>
		</td>
	</tr>
	
	<tr class='firstrowth'>
		<td style='border-bottom: medium none;' colspan='2'>
			<strong class='form_group'>Forms Sent to Gateway</strong>
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
  </tr> ";
  
  return $output;
}
  
  
add_action('init', 'nzshpcrt_paypal_ipn');
?>
