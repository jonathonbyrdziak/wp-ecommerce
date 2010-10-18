<?php
/**
 * WP eCommerce AJAX and Init functions
 *
 * These are the WPSC AJAX and Init functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 if(!isset($_REQUEST['wpsc_ajax_action'])){
 	$_REQUEST['wpsc_ajax_action'] = '';
 }
 if(!isset($_REQUEST['wpsc_ajax_actions'])){
 	$_REQUEST['wpsc_ajax_actions'] = '';
 }

 if(!isset($_REQUEST['wpsc_action'])){
 	$_REQUEST['wpsc_action'] = '';
 }

 function wpsc_special_widget(){
 	global $wpdb; 

 	wpsc_add_to_cart();
 	//exit();
 }
if($_REQUEST['wpsc_ajax_action'] == 'special_widget' || $_REQUEST['wpsc_ajax_action'] == 'donations_widget') {
	add_action('init', 'wpsc_special_widget');
}


// function wpsc_api_key_finder(){
//  	global $wpdb; 
//  	$transactid = $wpdb->escape($_POST['wpsc_transaction_id']);
//  	$sql = 'SELECT `id` FROM `'.WPSC_TABLE_PURCHASE_LOGS.'` WHERE `transactid`='.$transactid;
//  	$id = $wpdb->get_var($sql);
//  	$sql = 'SELECT `name`, `key`, `first_name` FROM `'.$wpdb->prefix.'api_keys` WHERE `purchase_id`='.$id;
//  	$api_info = $wpdb->get_results($sql);
//  	$_SESSION['api_info'] = $api_info;
// 	//exit('ID<pre>'.print_r($id, true).'</pre>');
//  	exit();
// }
// 
// if($_REQUEST['wpsc_ajax_action'] == 'api_key_finder') {
// 	add_action('init', 'wpsc_api_key_finder');
// }

/**
	* add_to_cart function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_add_to_cart() {
  global $wpdb, $wpsc_cart, $wpsc_theme_path;
  /// default values
	$default_parameters['variation_values'] = null;
	$default_parameters['quantity'] = 1;
	$default_parameters['provided_price'] = null;
	$default_parameters['comment'] =null;
	$default_parameters['time_requested']= null;
	$default_parameters['custom_message'] = null;
	$default_parameters['file_data'] = null;
	$default_parameters['is_customisable'] = false;
	$default_parameters['meta'] = null;
  
  
  /// sanitise submitted values
  $product_id = (int)$_REQUEST['product_id'];
  foreach((array)$_POST['variation'] as $key => $variation) {
    $provided_parameters['variation_values'][(int)$key] = (int)$variation;
  }
//exit('<pre>'.print_r($_POST, true).'</pre>');
  if($_POST['quantity'] > 0 && (!isset($_POST['wpsc_quantity_update']))) {
		$provided_parameters['quantity'] = (int)$_POST['quantity'];
  } else if (isset($_POST['wpsc_quantity_update'])) {
		$wpsc_cart->remove_item($_POST['key']);
		if(is_numeric($_POST['wpsc_quantity_update'])){
			$provided_parameters['quantity'] = (int)$_POST['wpsc_quantity_update'];
		}else{
			$provided_parameters['quantity'] = (int)$_POST['wpsc_quantity_update'][$_POST['product_id']];
		}
  }
//  exit('<pre>'.print_r($_POST, true).'</pre>');
  if($_POST['is_customisable'] == 'true') {
		$provided_parameters['is_customisable'] = true;
		
		if(isset($_POST['custom_text'])) {
			$provided_parameters['custom_message'] = $_POST['custom_text'];
		}
		if(isset($_FILES['custom_file'])) {
			$provided_parameters['file_data'] = $_FILES['custom_file'];
		}
	}
	if(((float)$_POST['donation_price'] > 0)) {
		$provided_parameters['provided_price'] = (float)$_POST['donation_price'];
	}
 
  $parameters = array_merge($default_parameters, (array)$provided_parameters);
  //echo "/*\n\r".print_r($parameters,true)."*/\n\r";
	$state = $wpsc_cart->set_item($product_id,$parameters); 
	
	$product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$product_id."' LIMIT 1",ARRAY_A);
//   exit('<pre>'.print_r($product,true).'</pre>');
  if($state == true) {
		$cart_messages[] = str_replace("[product_name]", stripslashes($product['name']), __('You just added "[product_name]" to your cart.', 'wpsc'));
	} else {
	  if($parameters['quantity'] <= 0) {
	    $cart_messages[] = __('Sorry, but you cannot add zero items to your cart', 'wpsc');
	  } else if($wpsc_cart->get_remaining_quantity($product_id,$parameters['variation_values'], $parameters['quantity']) > 0) {
			$cart_messages[] = str_replace("[number]", $wpsc_cart->get_remaining_quantity($product_id,$parameters['variation_values'], $parameters['quantity']), __('Sorry, but there are only [number] of this item in stock.', 'wpsc'));
	  } else {
	    $cart_messages[] = str_replace("[product_name]", $product['name'], __('Sorry, but the item "[product_name]" is out of stock.', 'wpsc'));
	  }
	}
	
  $cart_messages = apply_filters('wpsc_cart_messages',$cart_messages);
  if($_GET['ajax'] == 'true') {
		if(($product_id != null) &&(get_option('fancy_notifications') == 1)) {
			echo "if(jQuery('#fancy_notification_content')) {\n\r";
			echo "  jQuery('#fancy_notification_content').html(\"".str_replace(array("\n","\r") , array('\n','\r'), addslashes(fancy_notification_content($cart_messages))). "\");\n\r";
			echo "  jQuery('#loading_animation').css('display', 'none');\n\r";
			echo "  jQuery('#fancy_notification_content').css('display', 'block');\n\r";
			echo "}\n\r";
			$error_messages = array();
		}
		
		ob_start();
		$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
		include_once($cur_wpsc_theme_folder."/cart_widget.php");
	  $output = ob_get_contents();
		ob_end_clean();
		$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
    echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";

    
		if(get_option('show_sliding_cart') == 1)	{
			if((wpsc_cart_item_count() > 0) || (count($cart_messages) > 0)) {
				$_SESSION['slider_state'] = 1;
				echo "
					jQuery('#sliding_cart').slideDown('fast',function(){
						jQuery('#fancy_collapser').attr('src', (WPSC_URL+'/images/minus.png'));
					});
			";
			
			} else {
				$_SESSION['slider_state'] = 0;
				echo "
					jQuery('#sliding_cart').slideUp('fast',function(){
						jQuery('#fancy_collapser').attr('src', (WPSC_URL+'/images/plus.png'));
					});
			";
			}
		}

		do_action('wpsc_alternate_cart_html', $cart_messages);
		exit();
  }
}
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'add_to_cart') {
	add_action('init', 'wpsc_add_to_cart');
}


function wpsc_get_cart() {
  global $wpdb, $wpsc_cart, $wpsc_theme_path;
	ob_start();
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	include_once($cur_wpsc_theme_folder."/cart_widget.php");
	$output = ob_get_contents();
	ob_end_clean();
	$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
	echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";

	
	if(get_option('show_sliding_cart') == 1)	{
		if((wpsc_cart_item_count() > 0) || (count($cart_messages) > 0)) {
			$_SESSION['slider_state'] = 1;
			echo "
				jQuery('#sliding_cart').slideDown('fast',function(){
					jQuery('#fancy_collapser').attr('src', (WPSC_URL+'/images/minus.png'));
				});
		";

		} else {
			$_SESSION['slider_state'] = 0;
			echo "
				jQuery('#sliding_cart').slideUp('fast',function(){
					jQuery('#fancy_collapser').attr('src', (WPSC_URL+'/images/plus.png'));
				});
		";
		}
	}

	
	do_action('wpsc_alternate_cart_html', '');
	exit();
}

if($_REQUEST['wpsc_ajax_action'] == 'get_cart') {
	add_action('init', 'wpsc_get_cart');
}


/**
	* empty cart function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_empty_cart() {
  global $wpdb, $wpsc_cart, $wpsc_theme_path;
  $wpsc_cart->empty_cart(false);
  
  if($_REQUEST['ajax'] == 'true') {
		ob_start();
		$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
		include_once($cur_wpsc_theme_folder."/cart_widget.php");

		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
    echo "jQuery('div.shopping-cart-wrapper').html('$output');";
		do_action('wpsc_alternate_cart_html');
		
		if(get_option('show_sliding_cart') == 1)	{
			$_SESSION['slider_state'] = 0;
			echo "
				jQuery('#sliding_cart').slideUp('fast',function(){
					jQuery('#fancy_collapser').attr('src', (WPSC_URL+'/images/plus.png'));
				});
		";
		}

		exit();
  }

  // this if statement is needed, as this function also runs on returning from the gateway
  if($_REQUEST['wpsc_ajax_action'] == 'empty_cart') { 
		wp_redirect(remove_query_arg(array('wpsc_ajax_action','ajax')));
		exit();
	}
}


// execute on POST and GET
if(($_REQUEST['wpsc_ajax_action'] == 'empty_cart') || (isset($_GET['sessionid']) && $_GET['sessionid'] > 0)) {
	add_action('init', 'wpsc_empty_cart');
}


/**
	* coupons price, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_coupon_price($currCoupon = '') {
  global $wpdb, $wpsc_cart, $wpsc_coupons;
  if(isset($_POST['coupon_num']) && $_POST['coupon_num'] != ''){
  	  $coupon = $wpdb->escape($_POST['coupon_num']);
	  $_SESSION['coupon_numbers'] = $coupon;
	  $wpsc_coupons = new wpsc_coupons($coupon);
	  
	  if ($wpsc_coupons->validate_coupon()){
	  	$discountAmount = $wpsc_coupons->calculate_discount();
	  	$wpsc_cart->apply_coupons($discountAmount, $coupon);
	  	$wpsc_coupons->errormsg = false;
	  } else {
	  	$wpsc_coupons->errormsg = true;
	  	$wpsc_cart->coupons_amount = 0;
	  	$wpsc_cart->coupons_name = '';
	  }

  } else if ($_POST['coupon_num'] == '' && $currCoupon == ''){
   		$wpsc_cart->coupons_amount = 0;
  		$wpsc_cart->coupons_name = '';
  } else if ($currCoupon != '') {
  	  $coupon = $wpdb->escape($currCoupon);
	  $_SESSION['coupon_numbers'] = $coupon;
	  $wpsc_coupons = new wpsc_coupons($coupon);
	  
	  if($wpsc_coupons->validate_coupon()){
		 
	  	$discountAmount = $wpsc_coupons->calculate_discount();
	  	$wpsc_cart->apply_coupons($discountAmount, $coupon);
	  	$wpsc_coupons->errormsg = false;
	  }	
  }
  
	
 }
// execute on POST and GET
if(isset($_POST['coupon_num'])) {
	add_action('init', 'wpsc_coupon_price');
}


/**
	* update quantity function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_item_quantity() {
  global $wpdb, $wpsc_cart, $wpsc_theme_path;
 
  if(is_numeric($_POST['key'])) {
    $key = (int)$_POST['key'];
		if($_POST['quantity'] > 0) {
		  // if the quantity is greater than 0, update the item;
		  $parameters['quantity'] = (int)$_POST['quantity'];
			$wpsc_cart->edit_item($key, $parameters);
		} else {
		  // if the quantity is 0, remove the item.
			$wpsc_cart->remove_item($key);
		}
		wpsc_coupon_price($_SESSION['coupon_numbers']);
  }
  
 if($_REQUEST['ajax'] == 'true') {
	ob_start();
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	include_once($cur_wpsc_theme_folder."/cart_widget.php");
	$output = ob_get_contents();
	ob_end_clean();
	$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
		
	echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";
	do_action('wpsc_alternate_cart_html');
	
	
	exit();
 }
	
}
  
// execute on POST and GET
if(isset($_REQUEST['wpsc_update_quantity']) && $_REQUEST['wpsc_update_quantity'] == 'true') {
	add_action('init', 'wpsc_update_item_quantity');
}



function wpsc_update_product_rating() {
	global $wpdb;
	//exit("<pre>".print_r($_POST, true)."</pre>");
	$nowtime = time();
	$product_id = absint($_POST['product_id']);
	$ip_number = $wpdb->escape($_SERVER['REMOTE_ADDR']);
	$rating = absint($_POST['product_rating']);

	$cookie_data = explode(",",$_COOKIE['voting_cookie'][$product_id]);

	if(is_numeric($cookie_data[0]) && ($cookie_data[0] > 0)) {
		$vote_id = absint($cookie_data[0]);
		$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_RATING."` SET `rated` = '".$rating."' WHERE `id` ='".$vote_id."' LIMIT 1 ;");
	} else {
		$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_RATING."` ( `ipnum`  , `productid` , `rated`, `time`) VALUES ( '".$ip_number."', '".$product_id."', '".$rating."', '".$nowtime."');");
		
		$data = $wpdb->get_results("SELECT `id`,`rated` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `ipnum`='".$ip_number."' AND `productid` = '".$product_id."'  AND `rated` = '".$rating."' AND `time` = '".$nowtime."' ORDER BY `id` DESC LIMIT 1",ARRAY_A) ;

		$vote_id = $data[0]['id'];
		setcookie("voting_cookie[$prodid]", ($vote_id.",".$rating),time()+(60*60*24*360));
	}
	
	do_action('wpsc_rate_product', $product_id);
	
	if($_POST['ajax'] == 'true') {
	  
		exit();
	}
}
  
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'rate_product') {
	add_action('init', 'wpsc_update_product_rating');
}


/**
	* update_shipping_price function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_shipping_price() {
  global $wpdb, $wpsc_cart;
 	$quote_shipping_method = $_POST['key1'];
 	$quote_shipping_option = $_POST['key'];

	//If no $_POST key variable means it is activated through shipping same as billing
 	if($quote_shipping_method == '' && $quote_shipping_option == ''){
 		$quote_shipping_method = $wpsc_cart->selected_shipping_method;
 		$i=0;
		//if it is from shipping same as billing then per item shipping may need to be calculated...
		foreach($wpsc_cart->cart_items as $cart_item){
			$product_ship = $cart_item->calculate_shipping($quote_shipping_method);
			
			echo "jQuery('#shipping_".$i."').html('".htmlspecialchars(nzshpcrt_currency_display($product_ship, false,true))."');\n\r";
			$i++;
		}
 	}else{
 		$wpsc_cart->update_shipping($quote_shipping_method, $quote_shipping_option);
 		echo "jQuery('.pricedisplay.checkout-shipping').html('".wpsc_cart_shipping()."');\n\r";
		echo "jQuery('.pricedisplay.checkout-total').html('".wpsc_cart_total()."');\n\r";
		exit();	
 	}
	
	//if(count($wpsc_cart->shipping_quotes) > 0 && $_POST['key1'] == ''  && $_POST['key'] == ''){
		while (wpsc_have_shipping_methods()) : wpsc_the_shipping_method(); 
		 	if (!wpsc_have_shipping_quotes()) { continue; } // Don't display shipping method if it doesn't have at least one quote 
		 		
				$output .="<tr><td class='shipping_header' colspan='5'>".wpsc_shipping_method_name().__('- Choose a Shipping Rate', 'wpsc')."</td></tr>";
				while (wpsc_have_shipping_quotes()) : wpsc_the_shipping_quote();	
				$output .="<tr class='shipping_quotes'>";
				$output .="<td colspan='3'>";
				$output .="<label for='".wpsc_shipping_quote_html_id()."'>".wpsc_shipping_quote_name()."</label>";
				$output .="</td>";
				$output .="<td style='text-align:center;'>";
				$output .="<label for='".wpsc_shipping_quote_html_id()."'>".wpsc_shipping_quote_value()."</label>
								</td>
								<td style='text-align:center;'>";
				 	if(wpsc_have_morethanone_shipping_methods_and_quotes()): 
						$output .="<input type='radio' id='".wpsc_shipping_quote_html_id()."'".wpsc_shipping_quote_selected_state()."  onclick='switchmethod(\"".wpsc_shipping_quote_name()."\", \"".wpsc_shipping_method_internal_name()."\")' value='". wpsc_shipping_quote_value(true)."' name='shipping_method' />";
					else:
						$output .="	<input ". wpsc_shipping_quote_selected_state()." disabled='disabled' type='radio' id='". wpsc_shipping_quote_html_id()."'  value='". wpsc_shipping_quote_value(true)."' name='shipping_method' />";
							wpsc_update_shipping_single_method();
					endif;
				$output .="</td>";
				$output .="</tr>";
				endwhile;
		endwhile; 
	$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
	echo "var shipping =jQuery('td.shipping_header').parent('tr');";
	echo "shipping.nextAll('tr').remove();\n\r";
	echo "shipping.parent().append(\"".$output."\");\n\r";
	echo "shipping.empty();";
//	}

	$wpsc_cart->update_shipping($quote_shipping_method, $quote_shipping_option);
	
	if(wpsc_cart_tax(false) > 0){
		echo  "jQuery(\"tr.total_tax\").show();\n\r";
		echo  "jQuery('#checkout_tax').html(\"<span class='pricedisplay'>".wpsc_cart_tax()."</span>\");\n\r";
	}
	echo "jQuery('.pricedisplay.checkout-shipping').html('".wpsc_cart_shipping()."');\n\r";
	echo "jQuery('.pricedisplay.checkout-total').html('".wpsc_cart_total()."');\n\r";
	exit();
}
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'update_shipping_price') {
	add_action('init', 'wpsc_update_shipping_price');
}


/**
	* update_shipping_price function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_get_rating_count() {
  global $wpdb, $wpsc_cart;
  $prodid = $_POST['product_id'];
	$data = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `productid` = '".$prodid."'",ARRAY_A) ;
	echo $data[0]['count'].",".$prodid;
	exit();
}
// execute on POST and GET
if(isset($_REQUEST['get_rating_count']) && ($_REQUEST['get_rating_count'] == 'true') && is_numeric($_POST['product_id'])) {
	add_action('init', 'wpsc_get_rating_count');
}


/**
	* update_product_page_price function, used through ajax with variations
	* No parameters, returns nothing
*/
function wpsc_update_product_price() {
  global $wpdb, $wpsc_cart;
	foreach((array)$_POST['variation'] as $variation) {
		if(is_numeric($variation)) {
			$variations[] = (int)$variation;
		}
	}
	$pm=$_POST['pm'];
	echo "product_id=".(int)$_POST['product_id'].";\n";
	
	echo "price=\"".nzshpcrt_currency_display(calculate_product_price((int)$_POST['product_id'], $variations,'stay',$extras), $notax, true)."\";\n";
	echo "numeric_price=\"".number_format(calculate_product_price((int)$_POST['product_id'], $variations,'stay',$extras), 2)."\";\n";
	exit();
}
// execute on POST and GET
if(isset($_REQUEST['update_product_price']) && ($_REQUEST['update_product_price'] == 'true') && is_numeric($_POST['product_id'])) {
	add_action('init', 'wpsc_update_product_price');
}



/**
	* update location function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_location() {
  global $wpdb, $wpsc_cart;
	if($_POST['country'] != null) {
		$_SESSION['wpsc_delivery_country'] = $_POST['country'];
		if($_SESSION['wpsc_selected_country'] == null) {
			$_SESSION['wpsc_selected_country'] = $_POST['country'];
		}
		if($_POST['region'] != null) {
			$_SESSION['wpsc_delivery_region'] = $_POST['region'];
			if($_SESSION['wpsc_selected_region'] == null) {
				$_SESSION['wpsc_selected_region'] = $_POST['region'];
			}
		} else if($_SESSION['wpsc_selected_region'] == '') {
			$_SESSION['wpsc_delivery_region'] = get_option('base_region');
			$_SESSION['wpsc_selected_region'] = get_option('base_region');
		}
		
		if($_SESSION['wpsc_delivery_region'] == '') {
			$_SESSION['wpsc_delivery_region'] = $_SESSION['wpsc_selected_region'];
		}
	}
	
	if($_POST['zipcode'] != '') {
		$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
	}

	$delivery_region_count = $wpdb->get_var("SELECT COUNT(`regions`.`id`) FROM `".WPSC_TABLE_REGION_TAX."` AS `regions` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `country`.`id` = `regions`.`country_id` WHERE `country`.`isocode` IN('".$wpdb->escape($_SESSION['wpsc_delivery_country'])."')");
	if($delivery_region_count < 1) {
		$_SESSION['wpsc_delivery_region'] = null;
	}
	
	$selected_region_count = $wpdb->get_var("SELECT COUNT(`regions`.`id`) FROM `".WPSC_TABLE_REGION_TAX."` AS `regions` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `country`.`id` = `regions`.`country_id` WHERE `country`.`isocode` IN('".$wpdb->escape($_SESSION['wpsc_selected_country'])."')");
	if($selected_region_count < 1) {
		$_SESSION['wpsc_selected_region'] = null;
	}
	
	$wpsc_cart->update_location();
	$wpsc_cart->get_shipping_method();
	$wpsc_cart->get_shipping_option();
//	echo $wpsc_cart->shipping_method.'<br />';
	if($wpsc_cart->selected_shipping_method != '') {
		$wpsc_cart->update_shipping($wpsc_cart->selected_shipping_method, $wpsc_cart->selected_shipping_option);
	//echo "<pre>".print_r($wpsc_cart, true)."</pre>";
	}

	//exit();
	if($_REQUEST['ajax'] == 'true') {
		exit();
	}
}
  
// execute on POST and GET

if($_REQUEST['wpsc_ajax_actions'] == 'update_location') {
	add_action('init', 'wpsc_update_location',110);

}




function wpsc_cart_html_page() {
	require_once(WPSC_FILE_PATH."/wpsc-includes/shopping_cart_container.php");
	exit();
}

// execute on POST and GET
if($_REQUEST['wpsc_action'] == 'cart_html_page') {
	add_action('init', 'wpsc_cart_html_page', 110);
}






/**
	* submit checkout function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_submit_checkout() {
  global $wpdb, $wpsc_cart, $user_ID,$nzshpcrt_gateways, $wpsc_shipping_modules, $wpsc_gateways;
  //echo "break redirect";
//
   	do_action('wpsc_before_submit_checkout');

	$_SESSION['wpsc_checkout_misc_error_messages'] = array();
	$wpsc_checkout = new wpsc_checkout();
	//exit('coupons:'.$wpsc_cart->coupons_name);
	$selected_gateways = get_option('custom_gateway_options');
	$submitted_gateway = $_POST['custom_gateway'];

	$options = get_option('custom_shipping_options');

	$form_validity = $wpsc_checkout->validate_forms();
	
	extract($form_validity); // extracts $is_valid and $error_messages
	
	if (get_option('do_not_use_shipping') == 0 && ($wpsc_cart->selected_shipping_method == null || $wpsc_cart->selected_shipping_option == null) && $wpsc_cart->uses_shipping) {
		$_SESSION['wpsc_checkout_misc_error_messages'][] = __('You must select a shipping method, otherwise we cannot process your order.', 'wpsc');
		$is_valid = false;
   	}
		
	if($_POST['agree'] != 'yes') {
		$_SESSION['wpsc_checkout_misc_error_messages'][] = __('Please agree to the terms and conditions, otherwise we cannot process your order.', 'wpsc');
		$is_valid = false;		
	}

	
	$selectedCountry = $wpdb->get_results("SELECT id, country FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE isocode='".$wpdb->escape($_SESSION['wpsc_delivery_country'])."'", ARRAY_A);


	foreach($wpsc_cart->cart_items as $cartitem){
		//	exit('<pre>'.print_r($cartitem, true).'</pre>');
		$categoriesIDs = $wpdb->get_col("SELECT category_id FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE product_id=".$cartitem->product_id);
		
		foreach((array)$categoriesIDs as $catid){
			if(is_array($catid)){
				$sql ="SELECT `countryid` FROM `".WPSC_TABLE_CATEGORY_TM."` WHERE `visible`=0 AND `categoryid`=".$catid[0];
			}else{
				$sql ="SELECT `countryid` FROM `".WPSC_TABLE_CATEGORY_TM."` WHERE `visible`=0 AND `categoryid`=".$catid;
			}
			$countries = $wpdb->get_col($sql);
			if(in_array($selectedCountry[0]['id'], (array)$countries)){
				$errormessage =sprintf(__('Oops the product : %s cannot be shipped to %s. To continue with your transaction please remove this product from the list above.', 'wpsc'), $cartitem->product_name, $selectedCountry[0]['country']);
				$_SESSION['categoryAndShippingCountryConflict']= $errormessage;
				$is_valid = false;
			}
		}
		//count number of items, and number of items using shipping
		$num_items ++;
		if($cartitem->uses_shipping != 1){
				$disregard_shipping ++;
		}else{
				$use_shipping ++;
		}
	}
  
 // exit('valid >'.$is_valid);
  if(array_search($submitted_gateway,$selected_gateways) !== false) {
		$_SESSION['wpsc_previous_selected_gateway'] = $submitted_gateway;
  } else {
		$is_valid = false;
  }
  	if((get_option('do_not_use_shipping') != 1) && (in_array('ups', (array)$options)) && $_SESSION['wpsc_zipcode'] == '')	{
		//exit('Not being called');
		if($num_items != $disregard_shipping){  //<-- new line of code
			$_SESSION['categoryAndShippingCountryConflict'] = __('Please enter a Zipcode and click calculate to proceed');
			$is_valid = false;		
		}
	}
	if($is_valid == true || $_GET['gateway'] == 'noca') {
		$_SESSION['categoryAndShippingCountryConflict']= '';
		// check that the submitted gateway is in the list of selected ones
		$sessionid = (mt_rand(100,999).time());
		$_SESSION['wpsc_sessionid'] = $sessionid;
		$subtotal = $wpsc_cart->calculate_subtotal();
		if($wpsc_cart->has_total_shipping_discount() == false) {
			$base_shipping= $wpsc_cart->calculate_base_shipping();
		} else {
			$base_shipping = 0;
		}
		if(isset($_POST['how_find_us'])){
			$find_us = $_POST['how_find_us'];
		}else{
			$find_us = '';
		}

		$tax = $wpsc_cart->calculate_total_tax();
		$total = $wpsc_cart->calculate_total_price();
		// Make sure delivery and selected region are onlly saved if the country does have regions 
		// Im unsure how this would effect countries that HAVE regions, i.e if you select Canada as country,, will your 			// region be alabama if no region was selected?
		$wpsc_cart->update_location();
		if(!wpsc_has_regions($wpsc_cart->selected_country)){
			$wpsc_cart->selected_region = '';
		}
		if(!wpsc_has_regions($wpsc_cart->delivery_country)){
			$wpsc_cart->delivery_region = '';
		}

		$sql = "INSERT INTO `".WPSC_TABLE_PURCHASE_LOGS."` (`totalprice`,`statusno`, `sessionid`, `user_ID`, `date`, `gateway`, `billing_country`,`shipping_country`, `billing_region`, `shipping_region`, `base_shipping`,`shipping_method`, `shipping_option`, `plugin_version`, `discount_value`, `discount_data`,`find_us`) VALUES ('$total' ,'0', '{$sessionid}', '".(int)$user_ID."', UNIX_TIMESTAMP(), '{$submitted_gateway}', '{$wpsc_cart->selected_country}', '{$wpsc_cart->delivery_country}','{$wpsc_cart->selected_region}', '{$wpsc_cart->delivery_region}', '{$base_shipping}', '{$wpsc_cart->selected_shipping_method}', '{$wpsc_cart->selected_shipping_option}', '".WPSC_VERSION."', '{$wpsc_cart->coupons_amount}','{$wpsc_cart->coupons_name}', '{$find_us}')";
		
		//exit($sql);
		$wpdb->query($sql);
		
		
		$purchase_log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('{$sessionid}') LIMIT 1") ;
		//exit('PurchLog id'.$purchase_log_id);
		$wpsc_checkout->save_forms_to_db($purchase_log_id);
		$wpsc_cart->save_to_db($purchase_log_id);
		$wpsc_cart->submit_stock_claims($purchase_log_id);

		if(get_option('wpsc_also_bought') == 1) {
			wpsc_populate_also_bought_list();
		}
		wp_get_current_user();
		$our_user_id = $user_ID;
		do_action('wpsc_submit_checkout', array("purchase_log_id" => $purchase_log_id, "our_user_id" => $our_user_id));
	
		if(get_option('permalink_structure') != '') {
			$seperator = "?";
		} else {
			$seperator = "&";
		}


		if($total <= 0){
			$transaction_url_with_sessionid = add_query_arg('sessionid', $session_id, get_option('transact_url'));
			wp_redirect($transaction_url_with_sessionid);
		}
		
		/// submit to gateway
		
		$current_gateway_data = &$wpsc_gateways[$submitted_gateway];
		if($current_gateway_data['api_version'] >= 2.0) {
			$merchant_instance = new $current_gateway_data['class_name']($purchase_log_id);
			$merchant_instance->construct_value_array();
			$merchant_instance->submit();
			//print_r($merchant_instance);
		} else {
			if(($current_gateway_data['internalname'] == $submitted_gateway) && ($current_gateway_data['internalname'] != 'google')) {
				$gateway_used = $current_gateway_data['internalname'];
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `gateway` = '".$gateway_used."' WHERE `id` = '".$log_id."' LIMIT 1 ;");
				$current_gateway_data['function']($seperator, $sessionid);
				//break;
			} else if ($_POST['custom_gateway'] == 'google'){
				$gateway_used = $current_gateway_data['internalname'];
				$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `gateway` = '".$gateway_used."' WHERE `id` = '".$log_id."' LIMIT 1 ;");
				$_SESSION['gateway'] = 'google';
				header('Location: '.get_option('shopping_cart_url'));
				exit();
				//break;
			}
		}

		if(isset($_GET['gateway']) && $_GET['gateway'] == 'noca'){
			//exit('HERE2');
			echo transaction_results($sessionid, true);
		}else{
			//exit('HERE');
		}
} else {
	
	}
}

// execute on POST and GET
if($_REQUEST['wpsc_action'] == 'submit_checkout') {
	add_action('init', 'wpsc_submit_checkout');
}

if($_REQUEST['wpsc_action'] == 'gateway_notification') {
	add_action('init', 'wpsc_gateway_notification');
}


function wpsc_product_rss() {
	global $wpsc_query, $wpdb;
	header("Content-Type: application/xml; charset=UTF-8");
	header('Content-Disposition: inline; filename="e-Commerce_Product_List.rss"');
  //echo "<pre>".print_r($wpsc_query,true)."</pre>";
	require_once(WPSC_FILE_PATH.'/wpsc-includes/rss_template.php');
	exit();
}


if($_REQUEST['wpsc_action'] == "rss") {
	add_action('template_redirect', 'wpsc_product_rss', 80);
}



function wpsc_gateway_notification() {
	global $wpdb, $wpsc_gateways;
	$gateway_name = $_GET['gateway'];
	// work out what gateway we are getting the request from, run the appropriate code.
	if(($gateway_name != null) && isset($wpsc_gateways[$gateway_name]['class_name'])) {
		$merchant_class = $wpsc_gateways[$gateway_name]['class_name'];
		$merchant_instance = new $merchant_class(null, true);
		$merchant_instance->process_gateway_notification();
	}
	exit();
}

// execute on POST and GET
if($_REQUEST['wpsc_action'] == 'gateway_notification') {
	add_action('init', 'wpsc_gateway_notification');
}

if(isset($_GET['termsandconds']) && $_GET['termsandconds'] === 'true'){
	echo stripslashes(get_option('terms_and_conditions'));
	exit();

}



/**
	* wpsc_change_tax function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_change_tax() {
  global $wpdb, $wpsc_cart, $wpsc_theme_path;
  $form_id = absint($_POST['form_id']);

	$wpsc_selected_country = $wpsc_cart->selected_country;
	$wpsc_selected_region = $wpsc_cart->selected_region;

	$wpsc_delivery_country = $wpsc_cart->delivery_country;
	$wpsc_delivery_region = $wpsc_cart->delivery_region;
  

	$previous_country = $_SESSION['wpsc_selected_country'];
	if(isset($_POST['billing_country'])){
		$wpsc_selected_country = $wpdb->escape($_POST['billing_country']);
		$_SESSION['wpsc_selected_country'] = $wpsc_selected_country;
	}
	
	if(isset($_POST['billing_region'])){
		$wpsc_selected_region = absint($_POST['billing_region']);
		$_SESSION['wpsc_selected_region'] = $wpsc_selected_region;
	}

	//if(!wpsc_has_shipping_form()) {
	//	$_POST['shipping_country'] = $wpsc_selected_country;
	//	$_POST['shipping_region'] = $wpsc_selected_region;
	//}
	
	
	$check_country_code = $wpdb->get_var(" SELECT `country`.`isocode` FROM `".WPSC_TABLE_REGION_TAX."` AS `region` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `region`.`country_id` = `country`.`id` WHERE `region`.`id` = '".$_SESSION['wpsc_selected_region']."' LIMIT 1");
	
	if($_SESSION['wpsc_selected_country'] != $check_country_code) {
		$wpsc_selected_region = null;
	}
	
	if(isset($_POST['shipping_country'])){
		$wpsc_delivery_country = $wpdb->escape($_POST['shipping_country']);
		$_SESSION['wpsc_delivery_country'] = $wpsc_delivery_country;
	}
	if(isset($_POST['shipping_region'])){
		$wpsc_delivery_region = absint($_POST['shipping_region']);
		$_SESSION['wpsc_delivery_region'] = $wpsc_delivery_region;
	}
	
	$check_country_code = $wpdb->get_var(" SELECT `country`.`isocode` FROM `".WPSC_TABLE_REGION_TAX."` AS `region` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `region`.`country_id` = `country`.`id` WHERE `region`.`id` = '".$wpsc_delivery_region."' LIMIT 1");
	
	if($wpsc_delivery_country != $check_country_code) {
		$wpsc_delivery_region = null;
	}

	
	$wpsc_cart->update_location();
	$wpsc_cart->get_shipping_method();
	$wpsc_cart->get_shipping_option();
	if($wpsc_cart->selected_shipping_method != '') {
		$wpsc_cart->update_shipping($wpsc_cart->selected_shipping_method, $wpsc_cart->selected_shipping_option);
	}


	

  
  $tax = $wpsc_cart->calculate_total_tax();
  $total = wpsc_cart_total();
	ob_start();
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	include_once($cur_wpsc_theme_folder."/cart_widget.php");
	$output = ob_get_contents();
	ob_end_clean();
	//exit("/*<pre>".print_r($wpsc_cart,true)."</pre>*/");
	$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
	if(get_option('lock_tax') == 1){
		//echo "jQuery('#region').val(".$_SESSION['wpsc_delivery_region']."); \n";	
		echo "jQuery('#current_country').val('".$_SESSION['wpsc_delivery_country']."'); \n";
		if($_SESSION['wpsc_delivery_country']== 'US' && get_option('lock_tax') == 1){
			//exit('<pre>'.print_r($_SESSION, true).'</pre>');
			$output = wpsc_shipping_region_list($_SESSION['wpsc_delivery_country'], $_SESSION['wpsc_delivery_region']);
			//	echo 'jQuery("#change_country").append(\''.$output.'\');\n\r';
			$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
			echo "jQuery('#region').remove();\n\r";
			echo "jQuery('#change_country').append(\"".$output."\");\n\r";
		}
	}

	
	foreach($wpsc_cart->cart_items as $key => $cart_item) {
			echo "jQuery('#shipping_$key').html(\"".$wpsc_cart->process_as_currency($cart_item->shipping)."\");\n\r";

	}

	echo "jQuery('#checkout_shipping').html(\"".wpsc_cart_shipping()."\");\n\r";
	
	echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";
	if(get_option('lock_tax') == 1){
		echo "jQuery('.shipping_country').val('".$_SESSION['wpsc_delivery_country']."') \n";  
		$sql ="SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".$_SESSION['wpsc_selected_country']."'";
		$country_name = $wpdb->get_var($sql);
		echo "jQuery('.shipping_country_name').html('".$country_name."') \n";
	}
	
	
	$form_selected_country = null;
	$form_selected_region = null;
	$onchange_function = null;
	
	if(($_POST['billing_country'] != 'undefined') && !isset($_POST['shipping_country'])) {
		$form_selected_country = $wpsc_selected_country;
		$form_selected_region = $wpsc_selected_region;
		$onchange_function = 'set_billing_country';
	  	$title = 'billingregion';
	} else if(($_POST['shipping_country'] != 'undefined') && !isset($_POST['billing_country'])) {
		$form_selected_country = $wpsc_delivery_country;
		$form_selected_region = $wpsc_delivery_region;
		$onchange_function = 'set_shipping_country';
	  	$title = 'shippingregion';
	}
	
	if(($form_selected_country != null) && ($onchange_function != null)) {
		$region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$form_selected_country."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
		if($region_list != null) {
		
			$output = "<select title='{$title}' name='collected_data[".$form_id."][1]' class='current_region' onchange='$onchange_function(\"region_country_form_$form_id\", \"$form_id\");'>\n\r";
		
			foreach($region_list as $region) {
				if($form_selected_region == $region['id']) {
					$selected = "selected='selected'";
				} else {
					$selected = "";
				}
				$output .= "  <option value='".$region['id']."' $selected>".htmlspecialchars($region['name'])."</option>\n\r";
			}
			$output .= "</select>\n\r";
			
			$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
			echo  "jQuery('#region_select_$form_id').html(\"".$output."\");\n\r";
		
		} else {
			if(get_option('lock_tax') == 1){
				echo "jQuery('#region').hide();";
			}
			echo  "jQuery('#region_select_$form_id').html('');\n\r";
		}
 	}
	
	
	
	
		
	if($tax > 0) {
		echo  "jQuery(\"tr.total_tax\").show();\n\r";
	} else {
		echo  "jQuery(\"tr.total_tax\").hide();\n\r";
	}
	echo  "jQuery('#checkout_tax').html(\"<span class='pricedisplay'>".wpsc_cart_tax()."</span>\");\n\r";
	echo  "jQuery('#checkout_total').html(\"<span class='pricedisplay'>{$total}</span><input id='shopping_cart_total_price' type='hidden' value='{$total}' />\");\n\r";
	//echo "\n\r/*\n\r{$wpsc_cart->tax_percentage}\n\r*/\n\r";
	exit();
}

// execute on POST and GET
if(($_REQUEST['wpsc_ajax_action'] == 'change_tax')) {
	add_action('init', 'wpsc_change_tax');
}




      


function nzshpcrt_download_file() {
  global $wpdb,$user_level,$wp_rewrite; 
  get_currentuserinfo();  

  
  if(isset($_GET['downloadid'])) {
    // strip out anything that isnt 'a' to 'z' or '0' to '9'
    //ini_set('max_execution_time',10800);
    $downloadid = preg_replace("/[^a-z0-9]+/i",'',strtolower($_GET['downloadid']));
    
		$download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `uniqueid` = '".$downloadid."' AND `downloads` > '0' AND `active`='1' LIMIT 1",ARRAY_A);
		
		if(($download_data == null) && is_numeric($downloadid)) {
		  $download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `id` = '".$downloadid."' AND `downloads` > '0' AND `active`='1' AND `uniqueid` IS NULL LIMIT 1",ARRAY_A);
		}
		
		if((get_option('wpsc_ip_lock_downloads') == 1) && ($_SERVER['REMOTE_ADDR'] != null)) {
		  $ip_number = $_SERVER['REMOTE_ADDR'];
		  if($download_data['ip_number'] == '') {
		    // if the IP number is not set, set it
		    $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `ip_number` = '{$ip_number}' WHERE `id` = '{$download_data['id']}' LIMIT 1");
		  } else if($ip_number != $download_data['ip_number']) {
		    // if the IP number is set but does not match, fail here.
				exit(_e('This download is no longer valid, Please contact the site administrator for more information.','wpsc'));
		  }
		}
    if($download_data != null) {
			if($download_data['fileid'] > 0) {
				$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
      } else {
				$old_file_data = $wpdb->get_row("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
				$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
			}
      
      if((int)$download_data['downloads'] >= 1) {
        $download_count = (int)$download_data['downloads'] - 1;
      } else {
        $download_count = 0;
      }
          $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `downloads` = '{$download_count}' WHERE `id` = '{$download_data['id']}' LIMIT 1");
	  $cart_contents = $wpdb->get_results('SELECT `'.WPSC_TABLE_CART_CONTENTS.'`.*,`'.WPSC_TABLE_PRODUCT_LIST.'`.`file` FROM `'.WPSC_TABLE_CART_CONTENTS.'` LEFT JOIN `'.WPSC_TABLE_PRODUCT_LIST.'` ON `'.WPSC_TABLE_CART_CONTENTS.'`.`prodid`= `'.WPSC_TABLE_PRODUCT_LIST.'`.`id` WHERE `purchaseid` ='.$download_data['purchid'], ARRAY_A);
	    $dl = 0;

      foreach($cart_contents as $cart_content) {
      	if($cart_content['file'] == 1) {
      		$dl++;
      	}
      }
      if(count($cart_contents) == $dl) {
    //  	exit('called');
         $wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '4' WHERE `id` = '".$download_data['purchid']."' LIMIT 1");
      }

	  //exit('<pre>'.print_r($cart_contents,true).'</pre>');
   
      if(is_file(WPSC_FILE_DIR.$file_data['idhash'])) {
        header('Content-Type: '.$file_data['mimetype']);      
        header('Content-Length: '.filesize(WPSC_FILE_DIR.$file_data['idhash']));
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.stripslashes($file_data['filename']).'"');
        if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != '')) {
          /*
          There is a bug in how IE handles downloads from servers using HTTPS, this is part of the fix, you may also need:
            session_cache_limiter('public');
            session_cache_expire(30);
          At the start of your index.php file or before the session is started
          */
          header("Pragma: public");
          header("Expires: 0");      
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: public"); 
				} else {
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');       
				}        
        $filename = WPSC_FILE_DIR.$file_data['idhash'];
        // destroy the session to allow the file to be downloaded on some buggy browsers and webservers
        session_destroy();
        wpsc_readfile_chunked($filename);   
        exit();
			}
		} else {
			exit(_e('This download is no longer valid, Please contact the site administrator for more information.','wpsc'));
		}
	} else {
		if(($_GET['admin_preview'] == "true") && is_numeric($_GET['product_id']) && current_user_can('edit_plugins')) {
			$product_id = $_GET['product_id'];
			$product_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
			if(is_numeric($product_data[0]['file']) && ($product_data[0]['file'] > 0)) {
				$file_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data[0]['file']."' LIMIT 1",ARRAY_A) ;
				$file_data = $file_data[0];
				if(is_file(WPSC_FILE_DIR.$file_data['idhash'])) {
					header('Content-Type: '.$file_data['mimetype']);
					header('Content-Length: '.filesize(WPSC_FILE_DIR.$file_data['idhash']));
					header('Content-Transfer-Encoding: binary');
					if($_GET['preview_track'] != 'true') {
						header('Content-Disposition: attachment; filename="'.$file_data['filename'].'"');
					} else {
						header('Content-Disposition: inline; filename="'.$file_data['filename'].'"');
					}
					if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != '')) {
						header("Pragma: public");
						header("Expires: 0");      
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Cache-Control: public"); 
					} else {
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');       
					}             
					$filename = WPSC_FILE_DIR.$file_data['idhash'];  
					session_destroy();
					wpsc_readfile_chunked($filename);   
					exit();
				}            
			}
    }
  }
}

add_action('init', 'nzshpcrt_download_file');
?>
