<?php
function transaction_results($sessionid, $echo_to_screen = true, $transaction_id = null) {
	global $wpdb,$wpsc_cart, $wpsc_shipping_modules;

	//$curgateway = get_option('payment_gateway');
	$curgateway = $wpdb->get_var("SELECT gateway FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE sessionid='$sessionid'");
	$errorcode = 0;
	$order_status= 2;
	/*
	 * {Notes} Double check that $Echo_To_Screen is a boolean value
	 */
	$echo_to_screen=(((!is_bool($echo_to_screen)))?((true)):(($echo_to_screen)));
	//exit('triggered but with errors?'.$echo_to_screen);
	if(is_numeric($sessionid)) {
		
		if ( $echo_to_screen ) {
			echo apply_filters( 'wpsc_pre_transaction_results', '' );
		}
		
		$purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A) ;
		$thepurchlogitem = new wpsc_purchaselogs_items((int)$purchase_log['id']);

		if(($purchase_log['gateway'] == "testmode") && ($purchase_log['processed'] < 2))  {
			$message = stripslashes(get_option('wpsc_email_receipt'));
			$message_html = $message;
		} else {
			$message = stripslashes(get_option('wpsc_email_receipt'));
			$message_html = $message;
		}
		$order_url = site_url("/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php&amp;purchcaseid=".$purchase_log['id']);
		if(($_GET['ipn_request'] != 'true') and (get_option('paypal_ipn') == 1)) {
			if($purchase_log == null) {
				echo __('We&#39;re Sorry, your order has not been accepted, the most likely reason is that you have insufficient funds.', 'wpsc');
				if((get_option('purch_log_email') != null) && ($purchase_log['email_sent'] != 1)) {
					wp_mail(get_option('purch_log_email'), __('New pending order', 'wpsc'), __('There is a new order awaiting processing:', 'wpsc').$order_url, "From: ".get_option('return_email')."");
				}
				return false;
			} else if ($purchase_log['processed'] < 2) {  //added by Thomas on 20/6/2007
				echo __('Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc') . "<p style='margin: 1em 0px 0px 0px;' >".nl2br(stripslashes(get_option('payment_instructions')))."</p>";
				/*if($purchase_log['gateway'] != 'testmode') {
					if((get_option('purch_log_email') != null) && ($purchase_log['email_sent'] != 1)) {
						mail(get_option('purch_log_email'), __('New pending order', 'wpsc'), __('There is a new order awaiting processing:', 'wpsc').$order_url, "From: ".get_option('return_email')."");
					}
					return false;
				}*/
			}
		}
		if(isset($_GET['ssl_result_message']) && $_GET['ssl_result_message'] == 'APPROVAL'){
			$order_status= 2;
			$purchase_log['processed'] = 2;
		}
		$cart = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='{$purchase_log['id']}'",ARRAY_A);
		
		if($purchase_log['shipping_country'] != '') {
			$billing_country = $purchase_log['billing_country'];
			$shipping_country = $purchase_log['shipping_country'];
		} else {
			$country = $wpdb->get_var("SELECT `value` FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$purchase_log['id']." AND `form_id` = '".get_option('country_form_field')."' LIMIT 1");
			$billing_country = $country;
			$shipping_country = $country;
		}
	
		$email_form_field = $wpdb->get_results("SELECT `id`,`type` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
		$email = $wpdb->get_var("SELECT `value` FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$purchase_log['id']." AND `form_id` = '".$email_form_field[0]['id']."' LIMIT 1");
		$stock_adjusted = false;
		$previous_download_ids = array(0); 
		$product_list='';
		
		if(($cart != null) && ($errorcode == 0)) {
				foreach($cart as $row) {
				$link = "";
				$product_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$row['prodid']}' LIMIT 1", ARRAY_A) ;
				if($purchase_log['email_sent'] != 1) {
					$wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `active`='1' WHERE (`fileid` = '{$product_data['file']}' OR `cartid` = '{$row['id']}' ) AND `purchid` = '{$purchase_log['id']}'");
				}

				do_action('wpsc_transaction_result_cart_item', array("purchase_id" =>$purchase_log['id'], "cart_item"=>$row, "purchase_log"=>$purchase_log));

				if (($purchase_log['processed'] >= 2)) {
					$download_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."`
					 INNER JOIN `".WPSC_TABLE_PRODUCT_FILES."`
					  ON `".WPSC_TABLE_DOWNLOAD_STATUS."`.`fileid` = `".WPSC_TABLE_PRODUCT_FILES."`.`id`
					  WHERE `".WPSC_TABLE_DOWNLOAD_STATUS."`.`active`='1'
					  AND `".WPSC_TABLE_DOWNLOAD_STATUS."`.`purchid`='".$purchase_log['id']."'
					  AND (
						`".WPSC_TABLE_DOWNLOAD_STATUS."`.`cartid` = '".$row['id']."'
							OR (
								`".WPSC_TABLE_DOWNLOAD_STATUS."`.`cartid` IS NULL
								AND `".WPSC_TABLE_DOWNLOAD_STATUS."`.`fileid` = '{$product_data['file']}'
							)
						)
						AND `".WPSC_TABLE_DOWNLOAD_STATUS."`.`id` NOT IN ('".implode("','",$previous_download_ids)."')",ARRAY_A);
					$link=array();
						//exit('IM HERE'.$errorcode.'<pre>'.print_r($download_data).'</pre>');
					if(sizeof($download_data) != 0) {
						foreach($download_data as $single_download){
							if($single_download['uniqueid'] == null){// if the uniqueid is not equal to null, its "valid", regardless of what it is
								$link[] = array("url"=>site_url("?downloadid=".$single_download['id']), "name" =>$single_download["filename"]);	
							}	else {
								$link[] = array("url"=>site_url("?downloadid=".$single_download['uniqueid']), "name" =>$single_download["filename"]);
							}
						}
						//$order_status= 4;
					}else{
							$order_status= $purchase_log['processed'];
					}
					$previous_download_ids[] = $download_data['id'];
					do_action('wpsc_confirm_checkout', $purchase_log['id']);
				}
			//	do_action('wpsc_confirm_checkout', $purchase_log['id']);
				$shipping = $row['pnp'];
				$total_shipping += $shipping;
		
				if($product_data['special']==1) {
					$price_modifier = $product_data['special_price'];
				} else {
					$price_modifier = 0;
				}
		
				$total+=($row['price']*$row['quantity']);
				$message_price = nzshpcrt_currency_display(($row['price']*$row['quantity']), $product_data['notax'], true);

				$shipping_price = nzshpcrt_currency_display($shipping, 1, true);
				
				$variation_values = $wpdb->get_col("SELECT `value_id`  FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='{$row['id']}'"); 

				//echo "<pre>".print_r($product_data,true)."</pre>";
				
				$variation_count = count($variation_values);
				
				
		
				if($purchase['gateway'] != 'testmode') {
					if($gateway['internalname'] == $purch_data[0]['gateway'] ) {
						$gateway_name = $gateway['name'];
					}
				} else {
					$gateway_name = "Manual Payment";
				}
				//echo "<pre>".print_r($variation_values,true)."</pre>";
				
				$variation_list = '';
				if($variation_count > 0) {
					$value_names = $wpdb->get_col("SELECT `name` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variation_values)."')");
					$variation_list = " (".stripslashes(implode(", ",$value_names)).")";
				}
			
				if($link != '' && (!empty($link))) {
				  $additional_content = apply_filters('wpsc_transaction_result_content', array("purchase_id" =>$purchase_log['id'], "cart_item"=>$row, "purchase_log"=>$purchase_log));
					if(!is_string($additional_content)) {
				    $additional_content = '';
				  }

				  
					//$product_list .= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price ." ".__('Click to download', 'wpsc').":\n\r $link\n\r".$additional_content;
					//$product_list_html .= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price ."&nbsp;&nbsp;<a href='$link'>".__('Click to download', 'wpsc')."</a>\n". $additional_content;

					$product_list .= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price;
					$product_list_html .= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price;
					foreach($link as $single_link){
						$product_list .= "\n\r ".$single_link["name"].": ".$single_link["url"]."\n\r";
						$product_list_html .= "<a href='".$single_link["url"]."'>".$single_link["name"]."</a>\n";
						$report_product_list .="\n\r ".$single_link["name"].": ".$single_link["url"]."\n\r";
					//	$report_product_list .="<a href='".$single_link["url"]."'>".$single_link["name"]."</a>\n";
					}
					$product_list .= $additional_content;
					$product_list_html .= $additional_content;
				} else {
					$plural = '';
					if($row['quantity'] > 1) {
						$plural = "s";
						}
					$product_list.= $row['quantity']." - ". $product_data['name'].stripslashes($variation_list )."  ". $message_price ."\n\r";
					if ($shipping > 0) $product_list .= " - ". __('Shipping', 'wpsc').":".$shipping_price ."\n\r";
					$product_list_html.= $row['quantity']." -  ". $product_data['name'].stripslashes($variation_list )."  ". $message_price ."\n\r";
					if ($shipping > 0) $product_list_html .= " &nbsp; ". __('Shipping', 'wpsc').":".$shipping_price ."\n\r";

					$report_product_list.= $row['quantity']." - ". $product_data['name'] .stripslashes($variation_list)."  ".$message_price ."\n\r";
				}
				$report = get_option('wpsc_email_admin');
				
			}
			
				// Decrement the stock here
				if (($purchase_log['processed'] >= 2)) {
					wpsc_decrement_claimed_stock($purchase_log['id']);
				}
				
				if($purchase_log['discount_data'] != '') {
					$coupon_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` WHERE coupon_code='".$wpdb->escape($purchase_log['discount_data'])."' LIMIT 1",ARRAY_A);
					if($coupon_data['use-once'] == 1) {
						$wpdb->query("UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `active`='0', `is-used`='1' WHERE `id`='".$coupon_data['id']."' LIMIT 1");
					}
				}
				//$wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `active`='1' WHERE `fileid`='".$product_data['file']."' AND `purchid` = '".$purchase_log['id']."' LIMIT 1");
				//if (!isset($_SESSION['quote_shipping']))
					//$total_shipping = nzshpcrt_determine_base_shipping($total_shipping, $shipping_country);
			  $total_shipping += $purchase_log['base_shipping'];
					
				$total = $purchase_log['totalprice'];
				// echo $total;
				// $message.= "\n\r";
				$product_list.= "Your Purchase No.: ".$purchase_log['id']."\n\r";
				if($purchase_log['discount_value'] > 0) {
					$discount_email.= __('Discount', 'wpsc')."\n\r: ";
					$discount_email .=$purchase_log['discount_data'].' : '.nzshpcrt_currency_display($purchase_log['discount_value'], 1, true)."\n\r";
				}
				$total_shipping_email.= __('Total Shipping', 'wpsc').": ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
				$total_price_email.= __('Total', 'wpsc').": ".nzshpcrt_currency_display($total,1,true)."\n\r";
				$product_list_html.= "Your Purchase No.: ".$purchase_log['id']."\n\n\r";
				if($purchase_log['discount_value'] > 0) {
					$report.= $discount_email."\n\r";
					$total_shipping_html.= __('Discount', 'wpsc').": ".nzshpcrt_currency_display($purchase_log['discount_value'], 1, true)."\n\r";
				}
				$total_shipping_html.= __('Total Shipping', 'wpsc').": ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
				$total_price_html.= __('Total', 'wpsc').": ".nzshpcrt_currency_display($total, 1,true)."\n\r";
				if(isset($_GET['ti'])) {
					$message.= "\n\r".__('Your Transaction ID', 'wpsc').": " . $_GET['ti'];
					$message_html.= "\n\r".__('Your Transaction ID', 'wpsc').": " . $_GET['ti'];
					$report.= "\n\r".__('Transaction ID', 'wpsc').": " . $_GET['ti'];
				} else {
					$report_id = "Purchase # ".$purchase_log['id']."\n\r";
				}
        
        
				//echo "<pre>".print_r($purchase_log,true)."</pre>";
        $message = str_replace('%product_list%',$product_list,$message);
        $message = str_replace('%total_shipping%',$total_shipping_email,$message);
        $message = str_replace('%total_price%',$total_price_email,$message);
        $message = str_replace('%shop_name%',get_option('blogname'),$message);
        $message = str_replace('%find_us%', $purchase_log['find_us'], $message);
        //$message = str_replace('%order_status%',get_option('blogname'),$message);
        
        $report = str_replace('%product_list%',$report_product_list,$report);
        $report = str_replace('%total_shipping%',$total_shipping_email,$report);
        $report = str_replace('%total_price%',$total_price_email,$report);
        $report = str_replace('%shop_name%',get_option('blogname'),$report);
        $report = str_replace('%find_us%', $purchase_log['find_us'], $report);

        $message_html = str_replace('%product_list%',$product_list_html,$message_html);
        $message_html = str_replace('%total_shipping%',$total_shipping_html,$message_html);
        $message_html = str_replace('%total_price%',$total_price_email,$message_html);
        $message_html = str_replace('%shop_name%',get_option('blogname'),$message_html);
        $message_html = str_replace( '%find_us%', $purchase_log['find_us'], $message_html );
        //$message_html = str_replace('%order_status%',get_option('blogname'),$message_html);

				if(($email != '') && ($purchase_log['email_sent'] != 1)) {
				
 					add_filter('wp_mail_from', 'wpsc_replace_reply_address', 0);
 					add_filter('wp_mail_from_name', 'wpsc_replace_reply_name', 0);
 					
					if($purchase_log['processed'] < 2) {
						$payment_instructions = strip_tags(get_option('payment_instructions'));
						$message = __('Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc') . "\n\r" . $payment_instructions ."\n\r". $message;
						wp_mail($email, __('Order Pending: Payment Required', 'wpsc'), $message);
					} else {
						wp_mail($email, __('Purchase Receipt', 'wpsc'), $message);
					}
				}
				remove_filter('wp_mail_from_name', 'wpsc_replace_reply_name');
 				remove_filter('wp_mail_from', 'wpsc_replace_reply_address');
				$report_user = __('Customer Details', 'wpsc')."\n\r";
						$report_user .= "Billing Info \n\r";
			foreach((array)$thepurchlogitem->userinfo as $userinfo){
				if($userinfo['unique_name'] != 'billingcountry'){
					$report_user .= "".$userinfo['name'].": ".$userinfo['value']."\n";
				}else{
					$userinfo['value'] = maybe_unserialize($userinfo['value']);
					if(is_array($userinfo['value'] )){
						if(!empty($userinfo['value'][1]) && !is_numeric($userinfo['value'][1])){
							$report_user .= "State: ".$userinfo['value'][1]."\n";
						}elseif(is_numeric($userinfo['value'][1])){
							$report_user .= "State: ".wpsc_get_state_by_id($userinfo['value'][1],'name')."\n";
						}
						if(!empty($userinfo['value'][0])){
							$report_user .= "Country: ".$userinfo['value'][0]."\n";
						}
					}else{
						$report_user .= "".$userinfo['name'].": ".$userinfo['value']."\n";	
					}
				}
			}
			
			$report_user .= "\n\rShipping Info \n\r";
			foreach((array)$thepurchlogitem->shippinginfo as $userinfo){
				if($userinfo['unique_name'] != 'shippingcountry' && $userinfo['unique_name'] != 'shippingstate'){
					$report_user .= "".$userinfo['name'].": ".$userinfo['value']."\n";
				}elseif($userinfo['unique_name'] == 'shippingcountry'){
					$userinfo['value'] = maybe_unserialize($userinfo['value']);
					if(is_array($userinfo['value'] )){
						if(!empty($userinfo['value'][1]) && !is_numeric($userinfo['value'][1])){
							$report_user .= "State: ".$userinfo['value'][1]."\n";
						}elseif(is_numeric($userinfo['value'][1])){
							$report_user .= "State: ".wpsc_get_state_by_id($userinfo['value'][1],'name')."\n";
						}
						if(!empty($userinfo['value'][0])){
							$report_user .= "Country: ".$userinfo['value'][0]."\n";
						}
					}else{
						$report_user .= "".$userinfo['name'].": ".$userinfo['value']."\n";	
					}
				}elseif($userinfo['unique_name'] == 'shippingstate'){
					if(!empty($userinfo['value']) && !is_numeric($userinfo['value'])){
						$report_user .= "".$userinfo['name'].": ".$userinfo['value']."\n";
					}elseif(is_numeric($userinfo['value'])){
							$report_user .= "State: ".wpsc_get_state_by_id($userinfo['value'],'name')."\n";
					}
				}
			}
			$report_user .= "\n\r";
/*
				$form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase_log['id']."'";
				$form_data = $wpdb->get_results($form_sql,ARRAY_A);
					
				if($form_data != null) {
				
					foreach($form_data as $form_field) {
						$form_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `id` = '".$form_field['form_id']."' LIMIT 1", ARRAY_A);

						switch($form_data['type']) {
							case "country":
							$report_user .= $form_data['name'].": ".wpsc_get_country($form_field['value'])."\n";
							$report_user .= __('State', 'wpsc').": ".wpsc_get_region($purchase_log['billing_region'])."\n";
							break;
							
							case "delivery_country":
							$report_user .= $form_data['name'].": ".wpsc_get_country($form_field['value'])."\n";
							$report_user .= __('Delivery State', 'wpsc').": ".wpsc_get_region($purchase_log['shipping_region'])."\n";
							break;
							
							default:
							$report_user .= wp_kses($form_data['name'], array()).": ".$form_field['value']."\n";
							break;
						}
					}
				}
	
				$report_user .= "\n\r";
*/
				$report = $report_user. $report_id . $report;
				
				if($stock_adjusted == true) {
					$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `stock_adjusted` = '1' WHERE `sessionid` = ".$sessionid." LIMIT 1") ;
				}
	
				if((get_option('purch_log_email') != null) && ($purchase_log['email_sent'] != 1)) {
				
					wp_mail(get_option('purch_log_email'), __('Purchase Report', 'wpsc'), $report);
					
				}

				if($purchase_log['processed'] < 2) {
					echo "<br />" . nl2br(str_replace("$",'\$',$message_html));
					return;
				}

				/// Empty the cart
				$wpsc_cart->submit_stock_claims($purchase_log['id']);
				$wpsc_cart->empty_cart();

				if(true === $echo_to_screen) {
					echo '<div class="wrap">';
					if($sessionid != null) {
						echo __('The Transaction was successful', 'wpsc')."<br />";
						echo "<br />" . nl2br(str_replace("$",'\$',$message_html));
					}
					echo '</div>';
				}
			} else {
				if(true === $echo_to_screen) {
					echo '<div class="wrap">';
					echo __('Oops, there is nothing in your cart.', 'wpsc') . "<a href='".get_option("product_list_url")."'>" . __('Please visit our shop', 'wpsc') . "</a>";
					echo '</div>';
				}
			}

		if(($purchase_log['email_sent'] != 1) and ($sessionid != '')) {
			if(preg_match("/^[\w\s._,-]+$/",$transaction_id)) {
				$transact_id_sql = "`transactid` = '".$transaction_id."',";
			}
			$update_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET $transact_id_sql `email_sent` = '1', `processed` = '$order_status' WHERE `sessionid` = ".$sessionid." LIMIT 1";
			$wpdb->query($update_sql) ;
		}
	}
}
?>