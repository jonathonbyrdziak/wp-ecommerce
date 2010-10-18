<?php
/**
 * WP eCommerce checkout class
 *
 * These are the class for the WP eCommerce checkout
 * The checkout class handles dispaying the checkout form fields
 *
 * @package wp-e-commerce
 * @subpackage wpsc-checkout-classes 
*/

function wpsc_google_checkout_submit(){
	global $wpdb,  $wpsc_cart, $current_user;
	$wpsc_checkout = new wpsc_checkout();
	$purchase_log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('".$_SESSION['wpsc_sessionid']."') LIMIT 1") ;
	//$purchase_log_id = 1;
	get_currentuserinfo();
	//	exit('<pre>'.print_r($current_user, true).'</pre>');
	if($current_user->display_name != ''){
		foreach($wpsc_checkout->checkout_items as $checkoutfield){
		//	exit(print_r($checkoutfield,true));
			if($checkoutfield->unique_name == 'billingfirstname'){
				$checkoutfield->value = $current_user->display_name;
			}
		}	
	}
	if($current_user->user_email != ''){
		foreach($wpsc_checkout->checkout_items as $checkoutfield){
		//	exit(print_r($checkoutfield,true));
			if($checkoutfield->unique_name == 'billingemail'){
				$checkoutfield->value = $current_user->user_email;
			}
		}	
	}

	$wpsc_checkout->save_forms_to_db($purchase_log_id);
	$wpsc_cart->save_to_db($purchase_log_id);
	$wpsc_cart->submit_stock_claims($purchase_log_id);

}
function wpsc_display_tax_label($checkout = false){
	global $wpsc_cart;
	if(wpsc_tax_isincluded()){
		if($checkout){
			return  sprintf(__('Tax Included (%s%%)', 'wpsc'), $wpsc_cart->tax_percentage); 
		}else{
			return __('Tax Included', 'wpsc');
		}
	}else{
		return __('Tax', 'wpsc');
	}

}
function wpsc_have_checkout_items() {
	global $wpsc_checkout;
	return $wpsc_checkout->have_checkout_items();
}

function wpsc_the_checkout_item() {
	global $wpsc_checkout;
	return $wpsc_checkout->the_checkout_item();
}

function wpsc_is_shipping_details(){
	global $wpsc_checkout;
	if($wpsc_checkout->checkout_item->unique_name == 'delivertoafriend' && get_option('shippingsameasbilling') == '1'){
		return true;
	}else{
		return false;
	}
	
}


/*
 * wpsc_has_shipping_form function 
 * Checks to see if shipping is set to be the same as billing, and there is no shipping section
 * If so, it disables the shipping select box.
 * 
 */

function wpsc_has_shipping_form(){
	global $wpsc_checkout;
	if($wpsc_checkout == null) {
		$wpsc_checkout = new wpsc_checkout();
	}
	
	//echo "<pre>".print_r($wpsc_checkout, true)."</pre>";
	$unique_name_list = array();
	foreach($wpsc_checkout->checkout_items as $checkout_item) {
		$unique_name_list[] = $checkout_item->unique_name;
	}
	if(in_array('shippingcountry', $unique_name_list) && (get_option('use_billing_unless_is_shipping') != '1')){
		return true;
	}else{
		return false;
	}
	
}



function wpsc_shipping_details(){
	global $wpsc_checkout;
	if(stristr($wpsc_checkout->checkout_item->unique_name, 'shipping') != false){
		return ' wpsc_shipping_forms';
	}else{
		return "";
	}
	
}
function wpsc_the_checkout_item_error_class($as_attribute = true) {
	global $wpsc_checkout;
	if($_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id] != '') {
	  $class_name = 'validation-error';
	}
	if(($as_attribute == true)){
	 $output = "class='".$class_name.wpsc_shipping_details()." wpsc_checkout_field".$wpsc_checkout->checkout_item->id."'";
	} else {
		$output = $class_name;
	}
	return $output;
}

function wpsc_the_checkout_item_error() {
	global $wpsc_checkout;
	$output = false;
	if($_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id] != '') {
	  $output = $_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id];
	}
	
	return $output;
}
function wpsc_the_checkout_CC_validation(){
	$output = '';
	//exit('<pre>'.print_r($_SESSION['wpsc_gateway_error_messages'],true).'</pre>');
	if ($_SESSION['wpsc_gateway_error_messages']['card_number'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['card_number'];
	//	$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';
	}
	return $output;
}
function wpsc_the_checkout_CC_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['card_number'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;

}
function wpsc_the_checkout_CCexpiry_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['expdate'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;

}
function wpsc_the_checkout_CCexpiry_validation(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['expdate'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['expdate'];
	//	$_SESSION['wpsc_gateway_error_messages']['expdate'] = '';
	}
	return $output;

}
function wpsc_the_checkout_CCcvv_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['card_code'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;

}
function wpsc_the_checkout_CCcvv_validation(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['card_code'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['card_code'];
	//	$_SESSION['wpsc_gateway_error_messages']['card_code'] = '';
	}
	return $output;

}
function wpsc_the_checkout_CCtype_validation_class(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['cctype'] != ''){
		$output = 'class="validation-error"';
	}
	return $output;
}
function wpsc_the_checkout_CCtype_validation(){
	$output = '';
	if ($_SESSION['wpsc_gateway_error_messages']['cctype'] != ''){
		$output = $_SESSION['wpsc_gateway_error_messages']['cctype'];
		//$_SESSION['wpsc_gateway_error_messages']['cctype'] ='';
	}
	return $output;

}
function wpsc_checkout_form_is_header() {
	global $wpsc_checkout;
	if($wpsc_checkout->checkout_item->type == 'heading') {
	  $output = true;
	} else {
	  $output = false;
	}
	return $output;
}


function wpsc_checkout_form_name() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_name();
}
function wpsc_checkout_form_element_id() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_element_id();
}

function wpsc_checkout_form_field() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_field();
}


function wpsc_shipping_region_list($selected_country, $selected_region, $shippingdetails = false){
global $wpdb;
  
		//$region_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_REGION_TAX."` WHERE country_id='136'",ARRAY_A);
	$region_data = $wpdb->get_results("SELECT `regions`.* FROM `".WPSC_TABLE_REGION_TAX."` AS `regions` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `country`.`id` = `regions`.`country_id` WHERE `country`.`isocode` IN('".$wpdb->escape($selected_country)."')",ARRAY_A);
	$js = '';
	if(!$shippingdetails){
		$js = "onchange='submit_change_country();'";
	}
	if (count($region_data) > 0) {
		$output .= "<select name='region'  id='region' ".$js." >";
		foreach ($region_data as $region) {
			$selected ='';
			if($selected_region == $region['id']) {
				$selected = "selected='selected'";
			}
			$output .= "<option $selected value='{$region['id']}'>".htmlspecialchars($region['name'])."</option>";
		}
		$output .= "";
		
		$output .= "</select>";
	} else {
		$output .= " ";
	}
	return $output;
}

function wpsc_shipping_country_list($shippingdetails = false) {
	global $wpdb, $wpsc_shipping_modules;
	$js='';
	if(!$shippingdetails){
		$output = "<input type='hidden' name='wpsc_ajax_actions' value='update_location' />";
		$js ="  onchange='submit_change_country();'";
	}
	$selected_country = $_SESSION['wpsc_delivery_country'];
	$selected_region = $_SESSION['wpsc_delivery_region'];
	if($selected_country == null) {
		$selected_country = get_option('base_country');
	}
	if($selected_region == null) {
		$selected_region = get_option('base_region');
	}
	$country_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
	$output .= "<select name='country' id='current_country' ".$js." >";
	foreach ($country_data as $country) {
	// 23-02-09 fix for custom target market by jeffry
	// recon this should be taken out and put into a function somewhere maybe,,,
	 if($country['visible'] == '1'){
			$selected ='';
			if($selected_country == $country['isocode']) {
				$selected = "selected='selected'";
			}
			$output .= "<option value='".$country['isocode']."' $selected>".htmlspecialchars($country['country'])."</option>";
		}
	}

	$output .= "</select>";
	
	$output .= wpsc_shipping_region_list($selected_country, $selected_region, $shippingdetails);

	if($_POST['wpsc_update_location'] == 'true') {
	  $_SESSION['wpsc_update_location'] = true;
	} else {
		$_SESSION['wpsc_update_location'] = false;
	}
	
	if(isset($_POST['zipcode'])) {
		if ($_POST['zipcode']=='') {
			$zipvalue = '';
			$_SESSION['wpsc_zipcode'] = '';
		} else {
			$zipvalue = $_POST['zipcode'];
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
		}
	} else if(isset($_SESSION['wpsc_zipcode']) && ($_SESSION['wpsc_zipcode'] != '')) {
		$zipvalue = $_SESSION['wpsc_zipcode'];
	} else {
		$zipvalue = '';
		$_SESSION['wpsc_zipcode'] = '';
	}
	
	if(($zipvalue != '') && ($zipvalue != 'Your Zipcode')) {
		$color = '#000';
	} else {
		$zipvalue = 'Your Zipcode';
		$color = '#999';
	}
	
	$uses_zipcode = false;
	$custom_shipping = get_option('custom_shipping_options');
	foreach((array)$custom_shipping as $shipping) {
		if($wpsc_shipping_modules[$shipping]->needs_zipcode == true) {
			$uses_zipcode = true;
		}
	}
	
	if($uses_zipcode == true) {
		$output .= " <input type='text' style='color:".$color.";' onclick='if (this.value==\"Your Zipcode\") {this.value=\"\";this.style.color=\"#000\";}' onblur='if (this.value==\"\") {this.style.color=\"#999\"; this.value=\"Your Zipcode\"; }' value='".$zipvalue."' size='10' name='zipcode' id='zipcode'>";
	}
	return $output;
}









/**
 * The WPSC Checkout class
 */
class wpsc_checkout {
	// The checkout loop variables
	var $checkout_items = array();
	var $checkout_item;
	var $checkout_item_count = 0;
	var $current_checkout_item = -1;
	var $in_the_loop = false;

	//the ticket additions
	var $additional_fields = array();
	var $formfield_count =0;
   
	/**
	* wpsc_checkout method, gets the tax rate as a percentage, based on the selected country and region
	* @access public
	*/
  function wpsc_checkout($checkout_set = 0) {
    global $wpdb;
    $this->checkout_items = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'  AND `checkout_set`='".$checkout_set."' ORDER BY `order`;");
    
	$category_list = wpsc_cart_item_categories(true);
	$additional_form_list = array();
	foreach($category_list as $category_id) {
		$additional_form_list[] =  wpsc_get_categorymeta($category_id, 'use_additonal_form_set');
	}
	if(function_exists('wpsc_get_ticket_checkout_set')){
	
		$checkout_form_fields_id = array_search(wpsc_get_ticket_checkout_set(),$additional_form_list);
		unset($additional_form_list[$checkout_form_fields_id]);
	}
	
	if(count($additional_form_list) > 0) {
		$this->category_checkout_items = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'  AND `checkout_set` IN ('".implode("','", $additional_form_list)."') ORDER BY `checkout_set`, `order`;");
		$this->checkout_items = array_merge((array)$this->checkout_items,(array)$this->category_checkout_items);
	}

    if(function_exists('wpsc_get_ticket_checkout_set')){
    	$sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'  AND `checkout_set`='".wpsc_get_ticket_checkout_set()."' ORDER BY `order`;";
    	$this->additional_fields = $wpdb->get_results($sql);
    	//exit('<pre>'.print_r($this->additional_fields, true).'</pre>');
    	$count = wpsc_ticket_checkoutfields();
    	$j = 1;
    	$fields = $this->additional_fields;
    	$this->formfield_count = count($fields) + $this->checkout_item_count;
    	while($j < $count){
    		$this->additional_fields = array_merge((array)$this->additional_fields, (array)$fields);
    		$j++;
    	}
    	//exit($sql.'<pre>'.print_r($this->additional_fields, true).'</pre>'.$count);
    	if(wpsc_ticket_checkoutfields() >0){
    		$this->checkout_items = array_merge((array)$this->checkout_items,(array)$this->additional_fields);
    	}
    }

    $this->checkout_item_count = count($this->checkout_items);
  }
  
  function form_name() {
		if($this->form_name_is_required() && ($this->checkout_item->type != 'heading')){
			return stripslashes($this->checkout_item->name).': * ';
		}else{
			return stripslashes($this->checkout_item->name).': ';
		}
  }  
   
	function form_name_is_required(){
		if($this->checkout_item->mandatory == 0){
			return false;
		}else{
			return true;
		}
	}
	/**
	* form_element_id method, returns the form html ID
	* @access public
	*/
  function form_element_id() {
		return 'wpsc_checkout_form_'.$this->checkout_item->id;
	}  
	/**
	* get_checkout_options, returns the form field options
	* @access public
	*/
  function get_checkout_options($id){
  		global $wpdb;
  		$sql = 'SELECT `options` FROM `'.WPSC_TABLE_CHECKOUT_FORMS.'` WHERE `id`='.$id;
  		$options = $wpdb->get_var($sql);
  		$options = unserialize($options);
  		return $options;
  }
	/**
	* form_field method, returns the form html
	* @access public
	*/
  function form_field() {
		global $wpdb, $user_ID;
//		exit('<pre>'.print_r($_SESSION['wpsc_checkout_saved_values'], true).'</pre>');
		
		if((count($_SESSION['wpsc_checkout_saved_values']) <= 0) && ($user_ID > 0)) {
			//$_SESSION['wpsc_checkout_saved_values'] = get_usermeta($user_ID, 'wpshpcrt_usr_profile');
		}
		if(is_array($_SESSION['wpsc_checkout_saved_values'][$this->checkout_item->id])){
			if(function_exists('wpsc_get_ticket_checkout_set')){
				if($this->checkout_item->checkout_set == wpsc_get_ticket_checkout_set()){
					if(!isset($_SESSION['wpsc_tickets_saved_values_count'])){
						$_SESSION['wpsc_tickets_saved_values_count'] = 0;
						$count = $_SESSION['wpsc_tickets_saved_values_count'];
					}else{
						$count = $_SESSION['wpsc_tickets_saved_values_count']-1;
					}
					$saved_form_data = htmlentities(stripslashes($_SESSION['wpsc_checkout_saved_values'][$this->checkout_item->id][$count]), ENT_QUOTES, 'UTF-8');
				}
			}							
		}else{
			$saved_form_data = htmlentities(stripslashes($_SESSION['wpsc_checkout_saved_values'][$this->checkout_item->id]), ENT_QUOTES, 'UTF-8');
		}
		//make sure tickets are arrays for multiple ticket holders
		$an_array = '';
		if(function_exists('wpsc_get_ticket_checkout_set')){
			if($this->checkout_item->checkout_set == wpsc_get_ticket_checkout_set()){
				$an_array = '[]';
			}
		}
		switch($this->checkout_item->type) {
			case "address":
			case "delivery_address":
			case "textarea":
				
			$output = "<textarea title='".$this->checkout_item->unique_name."' class='text' id='".$this->form_element_id()."' name='collected_data[{$this->checkout_item->id}]".$an_array."' rows='3' cols='40' >".$saved_form_data."</textarea>";
			break;
			
			case "checkbox":
				$options = $this->get_checkout_options($this->checkout_item->id);	
				if($options != ''){
					$i = mt_rand();
					$j=0;
					foreach($options as $label=>$value){
						$output .= "<input type='hidden' title='".$this->checkout_item->unique_name."' id='".$this->form_element_id().$j."' value='-1' name='collected_data[{$this->checkout_item->id}][".$i."][".$j."]'/><input type='checkbox' title='".$this->checkout_item->unique_name."' id='".$this->form_element_id()."' value='".$value."' name='collected_data[{$this->checkout_item->id}][".$i."][".$j."]'/> ";
						$output .= "<label for='".$this->form_element_id().$j."'>".$label."</label><br />";
						$j++;
					}
				}
			break;
			
			case "country":
			$output = wpsc_country_region_list($this->checkout_item->id , false, $_SESSION['wpsc_selected_country'], $_SESSION['wpsc_selected_region'], $this->form_element_id());
			break;

			case "delivery_country":  
				if(wpsc_uses_shipping() && (get_option('use_billing_unless_is_shipping') != 1)) { 
					$country_name = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".$_SESSION['wpsc_delivery_country']."' LIMIT 1");
					$output = "<input title='".$this->checkout_item->unique_name."' type='hidden' id='".$this->form_element_id()."' class='shipping_country' name='collected_data[{$this->checkout_item->id}]' value='".$_SESSION['wpsc_delivery_country']."' /><span class='shipping_country_name'>".$country_name."</span> ";
				} else if(get_option('use_billing_unless_is_shipping') == 1) {
					$output = wpsc_shipping_country_list();
				} else {
					$checkoutfields = true;
					//$output = wpsc_shipping_country_list($checkoutfields);
					$output = wpsc_country_region_list($this->checkout_item->id , false, $_SESSION['wpsc_selected_country'], $_SESSION['wpsc_selected_region'], $this->form_element_id(), $checkoutfields);
				}
			break;
						
			case "select":
				$options = $this->get_checkout_options($this->checkout_item->id);
				if($options != ''){
				$output = "<select name='collected_data[{$this->checkout_item->id}]".$an_array."'>";
				$output .= "<option value='-1'>Select an Option</option>";
				foreach((array)$options as $label => $value){
					$value = str_replace(' ', '',$value);
					if($saved_form_data == $value){
						$selected = 'selected="selected"';
					}else{
						$selected = '';
					}
					$output .="<option ".$selected. " value='".$value."'>".$label."</option>\n\r";
				}
				$output .="</select>";
				}

			break;
			case "radio":
				$options = $this->get_checkout_options($this->checkout_item->id);			
				if($options != ''){	
					$i = mt_rand();
					foreach((array)$options as $label => $value){
					$output .= "<input type='radio' title='".$this->checkout_item->unique_name."' id='".$this->form_element_id()."'value='".$value."' name='collected_data[{$this->checkout_item->id}][".$i."]'/> ";
					$output .= "<label for='".$this->form_element_id()."'>".$label."</label>";
					
					}
				}
			break;
			case "text":
			case "city":
			case "delivery_city":
			case "email":
			case "coupon":
			default:
			  $country_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN('".$_SESSION['wpsc_delivery_country']."') LIMIT 1",ARRAY_A);
				if($this->checkout_item->unique_name == 'shippingstate'){
					if(wpsc_uses_shipping()&&($country_data['has_regions'] == 1)){
					$region_name = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id`='".$_SESSION['wpsc_delivery_region']."' LIMIT 1");
				$output = "<input title='".$this->checkout_item->unique_name."' type='hidden' id='".$this->form_element_id()."' class='shipping_region' name='collected_data[{$this->checkout_item->id}]' value='".$_SESSION['wpsc_delivery_region']."' size='4' /><span class='shipping_region_name'>".$region_name."</span> ";
					
					}else{
						$output = "<input class='shipping_region' title='".$this->checkout_item->unique_name."' type='text' id='".$this->form_element_id()."' class='text' value='".$saved_form_data."' name='collected_data[{$this->checkout_item->id}]".$an_array."' />";
				
					}
				}else{
					$output = "<input title='".$this->checkout_item->unique_name."' type='text' id='".$this->form_element_id()."' class='text' value='".$saved_form_data."' name='collected_data[{$this->checkout_item->id}]".$an_array."' />";
				
				}
				
			break;
		}
		return $output;
	}
  
	/**
	* validate_forms method, validates the input from the checkout page
	* @access public
	*/
  function validate_forms() {
   global $wpdb, $current_user, $user_ID;
   $any_bad_inputs = false;

   // Credit Card Number Validation for Paypal Pro and maybe others soon
   if(wpsc_cart_total(false) != 0){

   if(isset($_POST['card_number'])){
   		if($_POST['card_number'] != ''){/*

   			$ccregex='/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/';
   			if(!preg_match($ccregex, $_POST['card_number'])){
   				$any_bad_inputs = true;
				$bad_input = true;
				$_SESSION['wpsc_gateway_error_messages']['card_number'] = __('Please enter a valid', 'wpsc') . " " . strtolower('card number') . ".";
				$_SESSION['wpsc_checkout_saved_values']['card_number'] = '';
   			}else{
   				$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';
   			}   		
*/
   		}else{


   			$any_bad_inputs = true;
			$bad_input = true;
			$_SESSION['wpsc_gateway_error_messages']['card_number'] = __('Please enter a valid', 'wpsc') . " " . strtolower('card number') . ".";
			$_SESSION['wpsc_checkout_saved_values']['card_number'] = '';

   		}   	
   }else{
   		$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';
   }
   if(isset($_POST['card_number1']) && isset($_POST['card_number2']) && isset($_POST['card_number3']) && isset($_POST['card_number4'])){
   		if($_POST['card_number1'] != '' && $_POST['card_number2'] != '' && $_POST['card_number3'] != '' && $_POST['card_number4'] != '' && is_numeric($_POST['card_number1']) && is_numeric($_POST['card_number2']) && is_numeric($_POST['card_number3']) && is_numeric($_POST['card_number4'])){
      		$_SESSION['wpsc_gateway_error_messages']['card_number'] = '';	
	   	}else{
	   	
	   			$any_bad_inputs = true;
				$bad_input = true;
				$_SESSION['wpsc_gateway_error_messages']['card_number'] = __('Please enter a valid', 'wpsc') . " " . strtolower('card number') . ".";
				$_SESSION['wpsc_checkout_saved_values']['card_number'] = '';
	
	   	}
   	}
    if(isset($_POST['expiry'])){
	   	if(($_POST['expiry']['month'] != '') && ($_POST['expiry']['month'] != '') && is_numeric($_POST['expiry']['month']) && is_numeric($_POST['expiry']['year'])){
	   		$_SESSION['wpsc_gateway_error_messages']['expdate'] = '';
	   	}else{
			$any_bad_inputs = true;
			$bad_input = true;
			$_SESSION['wpsc_gateway_error_messages']['expdate'] = __('Please enter a valid', 'wpsc') . " " . strtolower('Expiry Date') . ".";
			$_SESSION['wpsc_checkout_saved_values']['expdate'] = '';
	   	}
   }
   if(isset($_POST['card_code'])){
   	if(($_POST['card_code'] == '') || (!is_numeric($_POST['card_code']))){
   		$any_bad_inputs = true;
		$bad_input = true;
		$_SESSION['wpsc_gateway_error_messages']['card_code'] = __('Please enter a valid', 'wpsc') . " " . strtolower('CVV') . ".";
		$_SESSION['wpsc_checkout_saved_values']['card_code'] = '';
   	}else{
   		$_SESSION['wpsc_gateway_error_messages']['card_code'] = '';
   	}
   
   }
   if(isset($_POST['cctype'])){
   	if($_POST['cctype'] == ''){
   	   	$any_bad_inputs = true;
		$bad_input = true;
		$_SESSION['wpsc_gateway_error_messages']['cctype'] = __('Please enter a valid', 'wpsc') . " " . strtolower('CVV') . ".";
		$_SESSION['wpsc_checkout_saved_values']['cctype'] = '';
   	}else{
		$_SESSION['wpsc_gateway_error_messages']['cctype'] = '';
   	}
   
   }
   }//closes main bracket
   	if(isset($_POST['log']) || isset($_POST['pwd']) || isset($_POST['user_email']) ) {
			$results = wpsc_add_new_user($_POST['log'], $_POST['pwd'], $_POST['user_email']);
			$_SESSION['wpsc_checkout_user_error_messages'] = array();
			if(is_callable(array($results, "get_error_code")) && $results->get_error_code()) {
				foreach ( $results->get_error_codes() as $code ) {
					foreach ( $results->get_error_messages($code) as $error ) {
						$_SESSION['wpsc_checkout_user_error_messages'][] = $error;
					}
				
					$any_bad_inputs = true;
				}
			}
			//exit('<pre>'.print_r($results, true).'</pre>');
				if($results->ID > 0) {
					$our_user_id = $results->ID;
				} else {
					$any_bad_inputs = true;		
				}
	}
	if($our_user_id < 1) {
	  $our_user_id = $user_ID;
	}
	// check we have a user id
	if( $our_user_id > 0 ){
		$user_ID = $our_user_id;
	}
		//exit('<pre>'.print_r($_POST['collected_data'],true).'</pre>');
    		//Basic Form field validation for billing and shipping details
  		foreach($this->checkout_items as $form_data) {
			$value = $_POST['collected_data'][$form_data->id];
		  	$value_id = (int)$value_id;
			$_SESSION['wpsc_checkout_saved_values'][$form_data->id] = $value;
			$bad_input = false;
			if(($form_data->mandatory == 1) || ($form_data->type == "coupon")) {
				switch($form_data->type) {
					case "email":
					if(!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/",$value)) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;

					case "delivery_country":
					case "country":
					case "heading":
					break;
					case "select":
					case 'checkbox':						
						if(is_array($value)){
							$select_bad_input = 0;
							foreach($value as $v){
								if($v == '-1'){
									$select_bad_input++;				
								}else{
									$valid_select_input++;
								}
							}
							if(count($value) == $select_bad_input){
								$any_bad_inputs = true;
								$bad_input = true;					
							}
						}else{
							if($value == '-1'){
								$any_bad_inputs = true;
								$bad_input = true;					
							}
						}
					break;
					default:
						if(is_array($value)){
							$select_bad_input = 0;
							foreach($value as $v){
								if($v == ''){
									$select_bad_input++;				
								}else{
									$valid_select_input++;
								}
							}
							if(count($value) == $select_bad_input){
								$any_bad_inputs = true;
								$bad_input = true;					
							}
						}else{
							if($value == ''){
								$any_bad_inputs = true;
								$bad_input = true;					
							}
						}
					break;
				}
				if($bad_input === true) {
					$_SESSION['wpsc_checkout_error_messages'][$form_data->id] = __('Please enter a valid', 'wpsc') . " " . strtolower($form_data->name) . ".";
					$_SESSION['wpsc_checkout_saved_values'][$form_data->id] = '';
				}
			}
		}
	

 		//exit('UserID >><pre>'.print_r($user_ID, true).'</pre>');
		if(($any_bad_inputs == false) && ($user_ID > 0)) {
			$saved_data_sql = "SELECT * FROM `".$wpdb->usermeta."` WHERE `user_id` = '".$user_ID."' AND `meta_key` = 'wpshpcrt_usr_profile';";
			$saved_data = $wpdb->get_row($saved_data_sql,ARRAY_A);
			//echo "<pre>".print_r($meta_data,true)."</pre>";
			$new_meta_data = serialize($_POST['collected_data']);
			if($saved_data != null) {
				$sql ="UPDATE `".$wpdb->usermeta."` SET `meta_value` =  '$new_meta_data' WHERE `user_id` IN ('$user_ID') AND `meta_key` IN ('wpshpcrt_usr_profile');";
				$wpdb->query($sql);
				$changes_saved = true;
				//exit($sql);
			} else {
				$sql = "INSERT INTO `".$wpdb->usermeta."` ( `user_id` , `meta_key` , `meta_value` ) VALUES ( ".$user_ID.", 'wpshpcrt_usr_profile', '$new_meta_data');";
				$wpdb->query($sql);
				$changes_saved = true;
				//exit($sql);
			}
		}

		return array('is_valid' => !$any_bad_inputs, 'error_messages' => $bad_input_message);
  }
  
	/**
	* validate_forms method, validates the input from the checkout page
	* @access public
	*/
  function save_forms_to_db($purchase_id) {
   global $wpdb;
   		$count = $this->get_count_checkout_fields()+1;
  	//	exit($count.'<pre>'.print_r( $_POST['collected_data'], true).'</pre>');
		$i = 0;
		foreach( $this->checkout_items as $form_data) {
		
		  $value = $_POST['collected_data'][$form_data->id];
		  if($value == ''){
		  	$value = $form_data->value;
		  }	
   	      if($form_data->type != 'heading') {
		 	// echo '<pre>'.print_r($form_data,true).'</pre>';
		  	if(is_array($value) &&($form_data->type == 'country' ||$form_data->type == 'delivery_country') ){
			  	$value = serialize($value);
			  		$prepared_query = $wpdb->query($wpdb->prepare("INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( %d, %d, %s)", $purchase_id, $form_data->id, $value));
			} else if(is_array($value)) {
				//	echo('<pre>'.print_r($value, true).'</pre>');
			  	foreach((array)$value as $v){
			  		if(is_array($v)){
			  			$options = array();
						// exit('<pre>'.print_r($v, true).'</pre>');
			  			foreach($v as $option){
			  				if($option != '-1'){
			  					$options[] = $option;
			  				}
			  			}
			  			
			  			$v = maybe_serialize($options);
			  			$v = implode(',', $options);
			  		}
			  		$prepared_query = $wpdb->query($wpdb->prepare("INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( %d, %d, %s)", $purchase_id, $form_data->id, $v));
			  	}
			} else {
					$prepared_query = $wpdb->query($wpdb->prepare("INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( %d, %d, %s)", $purchase_id, $form_data->id, $value));
			
			}
		
 		  } 
 		  if($i > $count){
		  	break;
		  }

 			$i++;
		 
		}
  }
  /**
   * Function that checks how many checkout fields are stored in checkout form fields table
   */
   function get_count_checkout_fields(){
   		global $wpdb;
   		$sql = "SELECT COUNT(*) FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` !='heading' AND `active`='1'";
   		$count = $wpdb->get_var($sql);
   		return $count;
   
   }
  /**
	 * checkout loop methods
	*/ 
  
  function next_checkout_item() {
		$this->current_checkout_item++;
		$this->checkout_item = $this->checkout_items[$this->current_checkout_item];
		return $this->checkout_item;
	}

  
  function the_checkout_item() {
		$this->in_the_loop = true;
		$this->checkout_item = $this->next_checkout_item();
		if ( $this->current_checkout_item == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_checkout_items() {
		if ($this->current_checkout_item + 1 < $this->checkout_item_count) {
			return true;
		} else if ($this->current_checkout_item + 1 == $this->checkout_item_count && $this->checkout_item_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_checkout_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_checkout_items() {
	  $_SESSION['wpsc_checkout_error_messages'] = array();
		$this->current_checkout_item = -1;
		if ($this->checkout_item_count > 0) {
			$this->checkout_item = $this->checkout_items[0];
		}
	}    
  
}


/**
 * The WPSC Gateway functions
 */


function wpsc_gateway_count() {
	global $wpsc_gateway;
	return $wpsc_gateway->gateway_count;
}

function wpsc_have_gateways() {
	global $wpsc_gateway;
	return $wpsc_gateway->have_gateways();
}

function wpsc_the_gateway() {
	global $wpsc_gateway;
	return $wpsc_gateway->the_gateway();
}

function wpsc_gateway_name() {
	global $wpsc_gateway;
	$payment_gateway_names = get_option('payment_gateway_names');
	if($payment_gateway_names[$wpsc_gateway->gateway['internalname']] != '') {
		$display_name = $payment_gateway_names[$wpsc_gateway->gateway['internalname']];					    
	} else {
		switch($selected_gateway_data['payment_type']) {
			case "paypal";
				$display_name = "PayPal";
			break;
			
			case "manual_payment":
				$display_name = "Manual Payment";
			break;
			
			case "google_checkout":
				$display_name = "Google Checkout";
			break;
			
			case "credit_card":
			default:
				$display_name = "Credit Card";
			break;
		}
	}
	return $display_name;
}

function wpsc_gateway_internal_name() {
	global $wpsc_gateway;
	return $wpsc_gateway->gateway['internalname'];
}

function wpsc_gateway_is_checked() {
	global $wpsc_gateway;
	$is_checked = false;
	if(isset($_SESSION['wpsc_previous_selected_gateway'])) {
	  if($wpsc_gateway->gateway['internalname'] == $_SESSION['wpsc_previous_selected_gateway']) {
	    $is_checked = true;	  
	  }
	} else {
	  if($wpsc_gateway->current_gateway == 0 || ($wpsc_gateway->gateway['internalname'] == 'paypal_certified')) {
	    $is_checked = true;
	  }
	}
	if($is_checked == true) {
	  $output = 'checked="checked"';
	} else {
		$output = '';
	}
	return $output;
}
function wpsc_gateway_cc_check(){


}
function wpsc_gateway_form_fields() {
	global $wpsc_gateway, $gateway_checkout_form_fields;
	//sprintf on paypalpro module
	if($wpsc_gateway->gateway['internalname'] == 'paypal_pro'){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate'],
						   wpsc_the_checkout_CCcvv_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_code'],	
						   wpsc_the_checkout_CCtype_validation_class(), $_SESSION['wpsc_gateway_error_messages']['cctype']
		);
		return $output;
	}
	if($wpsc_gateway->gateway['internalname'] == 'authorize' || $wpsc_gateway->gateway['internalname'] == 'paypal_payflow'){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate'],
						   wpsc_the_checkout_CCcvv_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_code']
		);
		return $output;
	}

	if($wpsc_gateway->gateway['internalname'] == 'eway' || $wpsc_gateway->gateway['internalname'] == 'bluepay' ){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate']
		);
		return $output;
	}
	if($wpsc_gateway->gateway['internalname'] == 'linkpoint'){
		$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] ,wpsc_the_checkout_CC_validation_class(), $_SESSION['wpsc_gateway_error_messages']['card_number'],
						   wpsc_the_checkout_CCexpiry_validation_class(), $_SESSION['wpsc_gateway_error_messages']['expdate']
		);
		return $output;
	}
	//$output = sprintf($gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']] , $size['width'], $size['height']);
	return $gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']];

}

function wpsc_gateway_form_field_style() {
	global $wpsc_gateway;
	$is_checked = false;
	if(isset($_SESSION['wpsc_previous_selected_gateway'])) {
	  if($wpsc_gateway->gateway['internalname'] == $_SESSION['wpsc_previous_selected_gateway']) {
	    $is_checked = true;	  
	  }
	} else {
	  if($wpsc_gateway->current_gateway == 0) {
	    $is_checked = true;
	  }
	}
	if($is_checked == true) {
	  $output = 'checkout_forms';
	} else {
		$output = 'checkout_forms_hidden';
	}
	return $output;
}

/**
 * The WPSC Gateway class
 */

class wpsc_gateways {
  var $wpsc_gateways;
	var $gateway;
	var $gateway_count = 0;
	var $current_gateway = -1;
	var $in_the_loop = false;
  
  function wpsc_gateways() {
		global $nzshpcrt_gateways;
		
		$gateway_options = get_option('custom_gateway_options');
		foreach($nzshpcrt_gateways as $gateway) {
			if(array_search($gateway['internalname'], (array)$gateway_options) !== false) {
				$this->wpsc_gateways[] = $gateway;
			}		
		}
		$this->gateway_count = count($this->wpsc_gateways);
  }

  /**
	 * checkout loop methods
	*/ 
  
  function next_gateway() {
		$this->current_gateway++;
		$this->gateway = $this->wpsc_gateways[$this->current_gateway];
		return $this->gateway;
	}

  
  function the_gateway() {
		$this->in_the_loop = true;
		$this->gateway = $this->next_gateway();
		if ( $this->current_gateway == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_gateways() {
		if ($this->current_gateway + 1 < $this->gateway_count) {
			return true;
		} else if ($this->current_gateway + 1 == $this->gateway_count && $this->gateway_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_gateways();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_gateways() {
		$this->current_gateway = -1;
		if ($this->gateway_count > 0) {
			$this->gateway = $this->wpsc_gateways[0];
		}
	}    

}


?>