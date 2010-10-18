<?php
/**
 * WP eCommerce Cart and Cart Item classes
 *
 * These are the classes for the WP eCommerce Cart and Cart Items,
 * The Cart class handles adding, removing and adjusting items in the cart, and totaling up the cost of the items in the cart.
 * The Cart Items class handles the same, but for cart items themselves.
 *
 *
 * @package wp-e-commerce
 * @since 3.7
 * @subpackage wpsc-cart-classes 
*/
/**
 * The WPSC Cart API for templates
 */


/**
* tax is included function, no parameters
* * @return boolean true or false depending on settings>general page
*/
function wpsc_tax_isincluded() {
	if(get_option('tax_inprice') == false || get_option('tax_inprice') == '0'){
		return false;
	}elseif(get_option('tax_inprice')=='1'){
		return true;
	}
}

/**
* cart item count function, no parameters
* * @return integer the item count
*/
function wpsc_cart_item_count() {
	global $wpsc_cart;
	$count = 0;
	foreach((array)$wpsc_cart->cart_items as $cart_item) {
		$count += $cart_item->quantity;
	}
	return $count;
}


/**
* coupon amount function, no parameters
* * @return integer the item count
*/
function wpsc_coupon_amount($forDisplay=true) {
	global $wpsc_cart;
	if($forDisplay == true) {
	  $output = $wpsc_cart->process_as_currency($wpsc_cart->coupons_amount);
	} else {
		$output = $wpsc_cart->coupons_amount;
	}
	return $output;
}
/**
* cart total function, no parameters
* @return string the total price of the cart, with a currency sign
*/
function wpsc_cart_total($forDisplay=true) {
	global $wpsc_cart;  
	$total = $wpsc_cart->calculate_subtotal();
//	echo 'cart shipping total'.$wpsc_cart->calculate_total_shipping();
	$total += $wpsc_cart->calculate_total_shipping();
	$total -= $wpsc_cart->coupons_amount;
	if(wpsc_tax_isincluded() == false){
		$total += $wpsc_cart->calculate_total_tax();
	}


	if($forDisplay){
//	exit('abksd'.get_option('add_plustax'));
		return $wpsc_cart->process_as_currency($total);
	}else{
		return $total;
	}
}

/**
 * Cart Total Widget
 *
 * Can be used to display the cart total excluding shipping, tax or coupons.
 *
 * @since 3.7.6.2
 *
 * @return string The subtotal price of the cart, with a currency sign.
 */
function wpsc_cart_total_widget( $shipping = true, $tax = true, $coupons = true ) {
	
	global $wpsc_cart; 
	
	$total = $wpsc_cart->calculate_subtotal();
	
	if ( $shipping ) {
		$total += $wpsc_cart->calculate_total_shipping();
	}
	if ( $tax && wpsc_tax_isincluded() == false ) {
		$total += $wpsc_cart->calculate_total_tax();
	}
	if ( $coupons ) {
		$total -= $wpsc_cart->coupons_amount;
	}
	
	if ( get_option( 'add_plustax' ) == 1 ) {
		return $wpsc_cart->process_as_currency( $wpsc_cart->calculate_subtotal() );
	} else {
		return $wpsc_cart->process_as_currency( $total );
	}
	
}

/**
* nzshpcrt_overall_total_price function, no parameters
* @return string the total price of the cart, with a currency sign
*/
function nzshpcrt_overall_total_price() {
	global $wpsc_cart;
	$total = $wpsc_cart->calculate_subtotal();
	$total += $wpsc_cart->calculate_total_shipping();
	if(wpsc_tax_isincluded() == false){
		$total += $wpsc_cart->calculate_total_tax();
	}
	$total -= $wpsc_cart->coupons_amount;
  return $total;
}

/**
* cart total weight function, no parameters
* @return float the total weight of the cart
*/
function wpsc_cart_weight_total() {
	global $wpsc_cart;
	if(is_object($wpsc_cart)) {
		return $wpsc_cart->calculate_total_weight();
	} else {
		return 0;
	}
}

/**
* tax total function, no parameters
* @return float the total weight of the cart
*/
function wpsc_cart_tax($forDisplay = true) {
	global $wpsc_cart;
	if($forDisplay){
	    if(wpsc_tax_isincluded() == false){
			return $wpsc_cart->process_as_currency($wpsc_cart->calculate_total_tax());
		}else{
			return '('.$wpsc_cart->process_as_currency($wpsc_cart->calculate_total_tax()).')';
		}

	}else{
		return $wpsc_cart->calculate_total_tax();
	}
}


/**
* wpsc_cart_show_plus_postage function, no parameters
* For determining whether to show "+ Postage & tax" after the total price
* @return boolean true or false, for use with an if statement
*/
function wpsc_cart_show_plus_postage() {
	global $wpsc_cart;
//		exit($_SESSION['wpsc_has_been_to_checkout'] ."get_option('add_plustax')".get_option('add_plustax'));	
	if(($_SESSION['wpsc_has_been_to_checkout'] == null ) && (get_option('add_plustax') == 1)) {

		return true;

	} else {
		return false;
	}
}

/**
* uses shipping function, no parameters
* @return boolean if true, all items in the cart do use shipping
*/
function wpsc_uses_shipping() {
	global $wpsc_cart;
	$shippingoptions = get_option('custom_shipping_options');
	if( (!((get_option('shipping_discount')== 1) && (get_option('shipping_discount_value') <= $wpsc_cart->calculate_subtotal()))) && count($shippingoptions) >= 1 && $shippingoptions[0] != '' && get_option('do_not_use_shipping') == 0) {
		$status = $wpsc_cart->uses_shipping();
	} else {
	  $status = false;
	}
	return $status;
}
  
/**
* cart has shipping function, no parameters
* @return boolean true for yes, false for no
*/
function wpsc_cart_has_shipping() {
	global $wpsc_cart;
	if($wpsc_cart->calculate_total_shipping() > 0) {
		$output = true;
	} else {
		$output = false;
	}
	return $output;
}

/**
* cart shipping function, no parameters
* @return string the total shipping of the cart, with a currency sign
*/
function wpsc_cart_shipping() {
	global $wpsc_cart;
	return $wpsc_cart->process_as_currency($wpsc_cart->calculate_total_shipping());
}


/**
* cart item categories function, no parameters
* @return array array of the categories
*/
function wpsc_cart_item_categories($get_ids = false) {
	global $wpsc_cart;
	if(is_object($wpsc_cart)) {
		if($get_ids == true) {
			return $wpsc_cart->get_item_category_ids();
		} else {
			return $wpsc_cart->get_item_categories();
		}
	} else {
		return array();
	}
}

/**
* have cart items function, no parameters
* @return boolean true if there are cart items left
*/
function wpsc_have_cart_items() {
	global $wpsc_cart;
	return $wpsc_cart->have_cart_items();
}

function wpsc_the_cart_item() {
	global $wpsc_cart;
	return $wpsc_cart->the_cart_item();
}
 
 
 
/**
* cart item key function, no parameters
* @return integer - the cart item key from the array in the cart object
*/
function wpsc_the_cart_item_key() {
	global $wpsc_cart;
	return $wpsc_cart->current_cart_item;
}
 
 /**
* cart item name function, no parameters
* @return string the cart item name
*/
function wpsc_cart_item_name() {
	global $wpsc_cart;
	return htmlentities(stripslashes($wpsc_cart->cart_item->product_name), ENT_QUOTES, "UTF-8");
}
 /**
* cart item quantity function, no parameters
* @return string the selected quantity of items
*/
function wpsc_cart_item_product_id() {
	global $wpsc_cart;
	return $wpsc_cart->cart_item->product_id;
} 
 /**
* cart item quantity function, no parameters
* @return string the selected quantity of items
*/
function wpsc_cart_item_quantity() {
	global $wpsc_cart;
	return $wpsc_cart->cart_item->quantity;
}

function wpsc_cart_item_quantity_single_prod($id) {
	global $wpsc_cart;
	//exit('<pre>'.print_r($wpsc_cart, true).'</pre>');
	return $wpsc_cart;
}
/**
* cart item price function, no parameters
* @return string the cart item price multiplied by the quantity, with a currency sign
*/
function wpsc_cart_item_price($forDisplay = true) {
	global $wpsc_cart;
	if($forDisplay){
		return $wpsc_cart->process_as_currency($wpsc_cart->cart_item->total_price);
	}else{
		return $wpsc_cart->cart_item->total_price;
	}
}

/**
* cart item shipping function, no parameters
* @return string the cart item price multiplied by the quantity, with a currency sign
*/
function wpsc_cart_item_shipping($forDisplay = true) {
	global $wpsc_cart;
	if($forDisplay){
		return $wpsc_cart->process_as_currency($wpsc_cart->cart_item->shipping);
	}else{
		return $wpsc_cart->cart_item->shipping;
	}
}



/**
* cart item url function, no parameters
* @return string the cart item url
*/
function wpsc_cart_item_url() {
	global $wpsc_cart;
	return $wpsc_cart->cart_item->product_url;
}

/**
* cart item image function
* returns the url to the to the cart item thumbnail image, if a width and height is specified, it resizes the thumbnail image to that size using the preview code (which caches the thumbnail also)
* @param integer width
* @param integer height
* @return string url to the to the cart item thumbnail image
*/
function wpsc_cart_item_image($width = null, $height = null) {
	global $wpsc_cart;
	
	if(($width > 0) && ($height > 0)) {
		$image_path = "index.php?image_id=".$wpsc_cart->cart_item->image_id."&amp;thumbnail=true&amp;width=".$width."&amp;height=".$height."";
	} else {
		$image_path = WPSC_THUMBNAIL_URL.$wpsc_cart->cart_item->thumbnail_image;	
		if(is_ssl()) {
			$image_path = str_replace("http://", "https://", $image_path);
		}
	}	
	return $image_path;
}

/**
 * cart all shipping quotes, used for google checkout
 * returns all the quotes for a selected shipping method
 * @access public
 *
 * @return array of shipping options
 */
function wpsc_selfURL() {
	$s = empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$protocol = wpsc_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}

function wpsc_strleft($s1, $s2) {
	$values = substr($s1, 0, strpos($s1, $s2));
	return  $values;
}
function wpsc_google_checkout(){
	$currpage = wpsc_selfURL();
	//exit('<pre>'.print_r(get_option('custom_gateway_options'), true).'</pre>');
	if (array_search("google",(array)get_option('custom_gateway_options')) !== false && $currpage != get_option('shopping_cart_url')) {
		global $nzshpcrt_gateways;
		foreach($nzshpcrt_gateways as $gateway) {
			if($gateway['internalname'] == 'google' ) {
				$gateway_used = $gateway['internalname'];
				$gateway['function'](true);
			}
		}
	}
}
function wpsc_empty_google_logs(){
	global $wpdb;
	$sql="DELETE FROM  `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`=".$_SESSION['wpsc_sessionid'];
	$wpdb->query($sql);
	unset($_SESSION['wpsc_sessionid']);
	
}
/**
* have shipping methods function, no parameters
* @return boolean
*/
function wpsc_have_shipping_methods() {
	global $wpsc_cart;
	return $wpsc_cart->have_shipping_methods();
}
/**
* the shipping method function, no parameters
* @return boolean
*/
function wpsc_the_shipping_method() {
	global $wpsc_cart;
	return $wpsc_cart->the_shipping_method();
}
/**
* the shipping method name function, no parameters
* @return string shipping method name
*/
function wpsc_shipping_method_name(){
	global $wpsc_cart, $wpsc_shipping_modules;
	if(is_object($wpsc_shipping_modules[$wpsc_cart->shipping_method])){
		return $wpsc_shipping_modules[$wpsc_cart->shipping_method]->getName();
	}
}


/**
* the shipping method  internal name function, no parameters
* @return string shipping method internal name
*/
function wpsc_shipping_method_internal_name() {
	global $wpsc_cart, $wpsc_shipping_modules;
	return $wpsc_cart->shipping_method;
}


/**
* have shipping quotes function, no parameters
* @return string the cart item url
*/
function wpsc_have_shipping_quotes() {
	global $wpsc_cart;
	return $wpsc_cart->have_shipping_quotes();
}

/**
* the shipping quote function, no parameters
* @return string the cart item url
*/
function wpsc_the_shipping_quote() {
	global $wpsc_cart;
	return $wpsc_cart->the_shipping_quote();
}

/**
* the shipping quote name function, no parameters
* @return string shipping quote name
*/
function wpsc_shipping_quote_name() {
	global $wpsc_cart;
	return $wpsc_cart->shipping_quote['name'];
}

/**
* the shipping quote value function, no parameters
* @return string shipping quote value
*/
function wpsc_shipping_quote_value($numeric = false) {
	global $wpsc_cart;
	//echo 'Shipping value'.$wpsc_cart->shipping_quote['value'];
	if($numeric == true) {
		return $wpsc_cart->shipping_quote['value'];
	} else {
		return $wpsc_cart->process_as_currency($wpsc_cart->shipping_quote['value']);
	}
}

/**
* the shipping quote html ID function, no parameters
* @return string shipping quote html ID
*/
function wpsc_shipping_quote_html_id() {
	global $wpsc_cart;
	return $wpsc_cart->shipping_method."_".$wpsc_cart->current_shipping_quote;
}

/**
* the shipping quote selected state function, no parameters
* @return string true or false
*/
function wpsc_shipping_quote_selected_state() {
	global $wpsc_cart;
	
	if(($wpsc_cart->selected_shipping_method == $wpsc_cart->shipping_method) && ($wpsc_cart->selected_shipping_option == $wpsc_cart->shipping_quote['name']) ) { 
		$wpsc_cart->selected_shipping_amount = $wpsc_cart->base_shipping;
		return "checked='checked'";
	} else {
		return "";
	}
}
function wpsc_have_morethanone_shipping_quote(){
	global $wpsc_cart;

    // if it's fixed rate shipping, and all the prices are the same, then there aren't really options.
    if (count($wpsc_cart->shipping_methods) == 1 && $wpsc_cart->shipping_methods[0] == 'flatrate') {
        $last_price = false;
        $first_quote_name = false;
        foreach ((array)$wpsc_cart->shipping_quotes as $name => $quote) {
            if (!$first_quote_name) $first_quote_name = $name;
            if ($last_price !== false && $quote != $last_price) return true;
            $last_price = $quote;
        }
        $wpsc_cart->rewind_shipping_methods();
        $wpsc_cart->update_shipping('flatrate', $name);
        return false;

    }

    return true;
}

function wpsc_have_morethanone_shipping_methods_and_quotes(){
	global $wpsc_cart;
		
	if(count($wpsc_cart->shipping_quotes) > 1 || count($wpsc_cart->shipping_methods) > 1 || count($wpsc_cart->shipping_quotes) == $wpsc_cart->shipping_quote_count){
		//$wpsc_cart->update_shipping($wpsc_cart->shipping_method, $wpsc_cart->selected_shipping_option);
		return true;
	}else{
		return false;
	}
}
/**
 * Whether or not there is a valid shipping quote/option available to the customer when checking out
 *
 * @return bool
 */
function wpsc_have_shipping_quote(){
	global $wpsc_cart;
	if ($wpsc_cart->shipping_quote_count > 0 || count($wpsc_cart->shipping_quotes) > 0) {
		return true;
	}
	return false;
}
function wpsc_update_shipping_single_method(){
	global $wpsc_cart;
	//exit('<pre>'.print_r($wpsc_cart->shipping_method, true).'</pre>');
	if(!empty($wpsc_cart->shipping_method)) {
		$wpsc_cart->update_shipping($wpsc_cart->shipping_method, $wpsc_cart->selected_shipping_option);
	}
}
function wpsc_update_shipping_multiple_methods(){
	global $wpsc_cart;
//exit('<pre>'.print_r($wpsc_cart->shipping_method, true).'</pre>'.$wpsc_cart->shipping_methods[0]);
	if(!empty($wpsc_cart->selected_shipping_method)) {
		$wpsc_cart->update_shipping($wpsc_cart->selected_shipping_method, $wpsc_cart->selected_shipping_option);
	}
}

/**
 * The WPSC Cart class
 */
class wpsc_cart {
  var $delivery_country;
	var $selected_country;
	var $delivery_region;
	var $selected_region;
	
	var $selected_shipping_method = null;
// 	var $shipping_quotes = null;
	var $selected_shipping_option = null;
	
	var $selected_shipping_amount = null;
	
	var $coupon;
	var $tax_percentage;
	var $unique_id;
	var $errors;
	
	
	// caching of frequently used values, these are wiped when the cart is modified and then remade when needed
	var $total_tax = null;
	var $base_shipping = null;
	var $total_item_shipping = null;
	var $total_shipping = null;
	var $subtotal = null;
	var $total_price = null;
	var $uses_shipping = null;
	 
	var $is_incomplete = true;
	
	// The cart loop variables
	var $cart_items = array();
	var $cart_item;
	var $cart_item_count = 0;
	var $current_cart_item = -1;
	var $in_the_loop = false;
   
	// The shipping method loop variables
	var $shipping_methods = array();
	var $shipping_method;
	var $shipping_method_count = 0;
	var $current_shipping_method = -1;
	var $in_the_method_loop = false;
	
	// The shipping quote loop variables
	var $shipping_quotes = array();
	var $shipping_quote;
	var $shipping_quote_count = 0;
	var $current_shipping_quote = -1;
	var $in_the_quote_loop = false;
	
	//coupon variable
	var $coupons_name = '';
	var $coupons_amount = 0;
	
	//currency values
	var $currency_conversion = 0;
	var $use_currency_converter = false;
	var $selected_currency_code = '';

  function wpsc_cart() {
    global $wpdb, $wpsc_shipping_modules;
    $coupon = 'percentage'; 
    // this is here to stop extremely bizzare errors with $wpsc_cart somehow not ending up as a global variable, yet certain code being run from it that eventually expects it to be one
    if(!is_object($GLOBALS['wpsc_cart'])) {
// 			$GLOBALS['wpsc_cart'] =& $this;
    }
	  $this->update_location();
	  $this->get_tax_rate();
	  $this->unique_id = sha1(uniqid(rand(),true));
	  
	  $this->get_shipping_method();
  }
  
    
  /**
	* update_location method, updates the location
	* @access public
	*/
	function update_location() {
	
		if(get_option('lock_tax') == 1){
			$_SESSION['wpsc_selected_country'] =& $_SESSION['wpsc_delivery_country'];
			$_SESSION['wpsc_selected_region'] =& $_SESSION['wpsc_delivery_region'];
			//exit('<pre>'.print_r($_SESSION, true).'</pre>');
		}
		if(!isset($_SESSION['wpsc_selected_country']) && !isset($_SESSION['wpsc_delivery_country'])) {
			$_SESSION['wpsc_selected_country'] = get_option('base_country');
			$_SESSION['wpsc_delivery_country'] = get_option('base_country');   
		} else {
			if(!isset($_SESSION['wpsc_selected_country'])) {
				$_SESSION['wpsc_selected_country'] = $_SESSION['wpsc_delivery_country'];
			} else if(!isset($_SESSION['wpsc_delivery_country'])) {
				$_SESSION['wpsc_delivery_country'] = $_SESSION['wpsc_selected_country'];     
			}   
		}
		
		if(!isset($_SESSION['wpsc_selected_region']) && !isset($_SESSION['wpsc_delivery_region'])) {
			$_SESSION['wpsc_selected_region'] = get_option('base_region');
			$_SESSION['wpsc_delivery_region'] = get_option('base_region');   
		}
		
		$this->delivery_country =& $_SESSION['wpsc_delivery_country'];
		$this->selected_country =& $_SESSION['wpsc_selected_country'];
		$this->delivery_region =& $_SESSION['wpsc_delivery_region'];
		$this->selected_region =& $_SESSION['wpsc_selected_region'];
		
		$this->get_tax_rate();
	}
	
	
  /**
	* get_shipping_rates method, gets the shipping rates
	* @access public
	*/
  function get_shipping_method() {
		global $wpdb, $wpsc_shipping_modules;
		// Reset all the shipping data in case the destination has changed
		$this->selected_shipping_method = null;
		$this->selected_shipping_option = null;
		$this->shipping_option = null;
		$this->shipping_method = null;
		$this->shipping_methods = array();
		$this->shipping_quotes = array();
		$this->shipping_quote = null;
		$this->shipping_method_count = 0;
	  // set us up with a shipping method.
	  $custom_shipping = get_option('custom_shipping_options');
	  
	  $this->shipping_methods = get_option('custom_shipping_options');
	  $this->shipping_method_count = count($this->shipping_methods);

		if((get_option('do_not_use_shipping') != 1) && (count($this->shipping_methods) > 0)  ) {
			if(array_search($this->selected_shipping_method, (array)$this->shipping_methods) === false) {
				//unset($this->selected_shipping_method);
			}
			
			$shipping_quotes = null;
			if($this->selected_shipping_method != null) {
				// use the selected shipping module
				if(is_callable(array(& $wpsc_shipping_modules[$this->selected_shipping_method], "getQuote"  ))) {
					$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
				}
			} else {

				// otherwise select the first one with any quotes
				foreach((array)$custom_shipping as $shipping_module) {
					// if the shipping module does not require a weight, or requires one and the weight is larger than zero
					$this->selected_shipping_method = $shipping_module;
					if(is_callable(array(& $wpsc_shipping_modules[$this->selected_shipping_method], "getQuote"  ))) {
						$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
					}
					if(count($this->shipping_quotes) >  $shipping_quote_count) { // if we have any shipping quotes, break the loop.
						break;
					}
				}
				
			}
		}

		}
  
  /**
	* get_shipping_option method, gets the shipping option from the selected method and associated quotes
	* @access public
	*/
  function get_shipping_option() {
    global $wpdb, $wpsc_shipping_modules;
       
		if((count($this->shipping_quotes) < 1) && is_callable(array($wpsc_shipping_modules[$this->selected_shipping_method], "getQuote"  ))) {
			$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
		}
    
    
   		if(count($this->shipping_quotes) < 1) {
			$this->selected_shipping_option = '';
		}
		
		if(($this->shipping_quotes != null) && (array_search($this->selected_shipping_option, array_keys($this->shipping_quotes)) === false)) {
			$this->selected_shipping_option = array_pop(array_keys(array_slice($this->shipping_quotes,0,1)));
		}
  }
  

  /**
	* update_shipping method, updates the shipping
	* @access public
	*/
  function update_shipping($method, $option) {
    global $wpdb, $wpsc_shipping_modules;
    
//     if($method == 'weightrate') {
//      exit("<pre>".print_r(debug_backtrace(),true)."</pre>");
//     }
		$this->selected_shipping_method = $method;
		//if(is_callable(array($wpsc_shipping_modules[$this->selected_shipping_method]), "getQuote"  )) {
			$this->shipping_quotes = $wpsc_shipping_modules[$method]->getQuote();
		//}
		//exit('<pre>'.print_r($this->shipping_quotes,true).'</pre> quotes');
		$this->selected_shipping_option = $option;
		
		foreach($this->cart_items as $key => $cart_item) {
			$this->cart_items[$key]->refresh_item();
		}
		$this->clear_cache();
		$this->get_shipping_option();	
	}
  
  
	/**
	* get_tax_rate method, gets the tax rate as a percentage, based on the selected country and region
	* @access public
	*/
  function get_tax_rate() {
    global $wpdb;
    $country_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
		$add_tax = false;

		
		if($this->selected_country == get_option('base_country')) {
		  // Tax rules for various countries go here, if your countries tax rules deviate from this, please supply code to add your region
		  switch($this->selected_country) {
		  	case 'US': // USA!
					$tax_region = get_option('base_region');
					if($this->selected_region == get_option('base_region') && (get_option('lock_tax_to_shipping') != '1')) {
						// if they in the state, they pay tax
						$add_tax = true;
					} else if($this->delivery_region == get_option('base_region')) {
		
						// if they live outside the state, but are delivering to within the state, they pay tax also
						$add_tax = true;
					}
		  	break;

		  	case 'CA': // Canada!
		  	  // apparently in canada, the region that you are in is used for tax purposes
		  	  if($this->selected_region != null) {
						$tax_region = $this->selected_region;
					} else {
						$tax_region = get_option('base_region');
					}
					$add_tax = true;
		  	break;

		  	default: // Everywhere else!
					$tax_region = get_option('base_region');
					if($country_data['has_regions'] == 1) {
						if(get_option('base_region') == $region ) {
							$add_tax = true;
						}
					} else {
						$add_tax = true;
					}
		  	break;
		  }
		}
		
		if($add_tax == true) {
			if(($country_data['has_regions'] == 1)) {
				$region_data = $wpdb->get_row("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."` WHERE `".WPSC_TABLE_REGION_TAX."`.`country_id` IN('".$country_data['id']."') AND `".WPSC_TABLE_REGION_TAX."`.`id` IN('".$tax_region."') ",ARRAY_A) ;
				$tax_percentage =  $region_data['tax'];
			} else {
				$tax_percentage =  $country_data['tax'];
			}
		} else {
		  // no tax charged = tax equal to 0%
			$tax_percentage = 0;
		}
		if($this->tax_percentage != $tax_percentage ) {
			$this->clear_cache();
			$this->tax_percentage = $tax_percentage;
		
			foreach($this->cart_items as $key => $cart_item) {
				$this->cart_items[$key]->refresh_item();
			}
		}
  }


	/**
	 * Set Item method, requires a product ID and the parameters for the product
	 * @access public
	 *
	 * @param integer the product ID
	 * @param array parameters
	 * @return boolean true on sucess, false on failure
	*/
  function set_item($product_id, $parameters, $updater = false) {
    // default action is adding
    
    if(($parameters['quantity'] > 0) && ($this->check_remaining_quantity($product_id, $parameters['variation_values'], $parameters['quantity']) == true)) {
			$new_cart_item = new wpsc_cart_item($product_id,$parameters, $this);
			
			$add_item = true;
			$edit_item = false;
//exit('hello<pre>'.print_r($new_cart_item,true).'</pre>');
			if((count($this->cart_items) > 0) && ($new_cart_item->is_donation != 1)) {
				//loop through each cart item
				foreach($this->cart_items as $key => $cart_item) {
					// compare product ids and variations.
					if(($cart_item->product_id == $new_cart_item->product_id) &&
					  ($cart_item->product_variations == $new_cart_item->product_variations) &&
					  ($cart_item->custom_message == $new_cart_item->custom_message) &&
					  ($cart_item->custom_file == $new_cart_item->custom_file)) {
						// if they are the same, increment the count, and break out;
						if(!$updater){
							$this->cart_items[$key]->quantity  += $new_cart_item->quantity;
						} else {
							$this->cart_items[$key]->quantity  = $new_cart_item->quantity;

						}
						$this->cart_items[$key]->refresh_item();
						$add_item = false;
						$edit_item = true;
					}
				}
			
			}
			// if we are still adding the item, add it
			if($add_item === true) {
				$this->cart_items[] = $new_cart_item;
			}
		} else {
			//$errors[] = new WP_Error('no_stock_available', __(__('This product has no available stock', 'wpsc')));
		}
    
		
	  // if some action was performed, return true, otherwise, return false;
	  $status = false;
		if(($add_item == true) || ($edit_item == true)) {
			$status = true;
		}	
		$this->cart_item_count = count($this->cart_items);
		$this->clear_cache();
		do_action ("wpsc_cart_updated", &$this);
		return $status;
	}
  
	/**
	 * Edit Item method
	 * @access public
	 *
	 * @param integer a cart_items key
	 * @param array an array of parameters to change
	 * @return boolean true on sucess, false on failure
	*/
  function edit_item($key, $parameters) {
    if(isset($this->cart_items[$key])) {
			$product_id = $this->cart_items[$key]->product_id;
			$quantity = $parameters['quantity'] - $this->cart_items[$key]->quantity;
			if($this->check_remaining_quantity($product_id, $this->cart_items[$key]->variation_values, $quantity) == true) {
				foreach($parameters as $name => $value) {
					$this->cart_items[$key]->$name = $value; 
				}
				$this->cart_items[$key]->refresh_item();
				$this->clear_cache();
			} else {
				//$errors[] = new WP_Error('no_stock_available', __(__('This product has no available stock', 'wpsc')));
			}
      return true;
    } else {
     return false;
    }
  }
  
	/**
	 * check remaining quantity method
	 * currently only checks remaining stock, in future will do claimed stock and quantity limits
	 * will need to return errors, then, rather than true/false, maybe use the wp_error object?
	 * @access public
	 *
	 * @param integer a product ID key
	 * @param array  variations on the product
	 * @return boolean true on sucess, false on failure
	*/
  function check_remaining_quantity($product_id, $variations = array(), $quantity = 1) {
    global $wpdb;

    
		$quantity_data = $wpdb->get_row("SELECT `quantity_limited`, `quantity`  FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN ('$product_id') LIMIT 1", ARRAY_A);
		// check to see if the product uses stock
		if($quantity_data['quantity_limited'] == 1){
			if(count($variations) > 0) { /// if so and we have variations, select the stock for the chosen variations
				$variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variations)."')");
				asort($variation_ids);
				$all_variation_ids = implode(",", $variation_ids);
				
				$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '".(int)$product_id."' AND `value_id` IN ( '".implode("', '",$variations )."' )  AND `all_variation_ids` IN('$all_variation_ids')  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variations)."' LIMIT 1");
				
				$variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
				$stock = $variation_stock_data['stock'];
				
			} else { /// if so and we have no variations, select the stock for the product
			  $stock = $quantity_data['quantity'];
			  $priceandstock_id = 0;
			}
	    if($stock > 0) {
				$claimed_stock = $wpdb->get_var("SELECT SUM(`stock_claimed`) FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `product_id` IN('$product_id') AND `variation_stock_id` IN('$priceandstock_id')");
				if(($claimed_stock + $quantity) <= $stock) {
					$output = true;
				} else {
					$output = false;
				}
		  } else {
				$output = false;	    
	    }
	     
    } else {
      $output = true;
    }
    return $output;
  }
  
	/**
	 * get remaining quantity method
	 * currently only checks remaining stock, in future will do claimed stock and quantity limits
	 * will need to return errors, then, rather than true/false, maybe use the wp_error object?
	 * @access public
	 *
	 * @param integer a product ID key
	 * @param array  variations on the product
	 * @return boolean true on sucess, false on failure
	*/
  function get_remaining_quantity($product_id, $variations = array(), $quantity = 1) {
    global $wpdb;
		$quantity_data = $wpdb->get_row("SELECT `quantity_limited`, `quantity`  FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN ('$product_id') LIMIT 1", ARRAY_A);
		// check to see if the product uses stock
		if($quantity_data['quantity_limited'] == 1){
			if(count($variations) > 0) { /// if so and we have variations, select the stock for the chosen variations
				$variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variations)."')");
				asort($variation_ids);
				$all_variation_ids = implode(",", $variation_ids);
				
				$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '".(int)$product_id."' AND `value_id` IN ( '".implode("', '",$variations )."' )  AND `all_variation_ids` IN('$all_variation_ids')  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variations)."' LIMIT 1");
				
				$variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
				$stock = $variation_stock_data['stock'];
				
			} else { /// if so and we have no variations, select the stock for the product
			  $stock = $quantity_data['quantity'];
			  $priceandstock_id = 0;
			}
			
			
			
	    if($stock > 0) {
				$claimed_stock = $wpdb->get_var("SELECT SUM(`stock_claimed`) FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `product_id` IN('$product_id') AND `variation_stock_id` IN('$priceandstock_id')");
				$output = $stock - $claimed_stock;				
		  } else {
				$output = 0;
		  }
	     
    }
    return $output;
  }
  
  
	/**
	 * Remove Item method 
	 * @access public
	 *
	 * @param integer a cart_items key
	 * @return boolean true on sucess, false on failure
	*/
  function remove_item($key) {
    if(isset($this->cart_items[$key])) {
		$cart_item =& $this->cart_items[$key];
		$cart_item->update_item(0);
		unset($this->cart_items[$key]);
	    $this->cart_items = array_values($this->cart_items);
		$this->cart_item_count = count($this->cart_items);
	    $this->current_cart_item = -1;
		$this->clear_cache();
		return true;
	} else {
		$this->clear_cache();
		return false;
	}
	
  }
  
	/**
	 * Empty Cart method 
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function empty_cart($fromwidget = true) {
		global $wpdb;

		if(isset($_SESSION['wpsc_sessionid']) && !($fromwidget)){
		//	exit('google triggered');
			///wpsc_empty_google_logs();
		}
		$wpdb->query($wpdb->prepare("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN ('%s');", $this->unique_id));

		$this->cart_items = array();
		$this->cart_item = null;
		$this->cart_item_count = 0;
		$this->current_cart_item = -1;
		unset($this->coupons_amount);
		unset($this->coupons_name);
		$this->clear_cache();
		$this->cleanup();
		
		do_action('wpsc_empty_cart', &$this);
  }
  
  
  
	/**
	 * Clear Cache method, used to clear the cached totals 
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function clear_cache() {
		$this->total_tax = null;
		$this->base_shipping = null;
		$this->total_item_shipping = null;
		$this->total_shipping = null;
		$this->subtotal = null;
		$this->total_price = null;
		$this->uses_shipping = null;
		$this->shipping_quotes = null;
		$this->get_shipping_option();	
	}
  
 	/**
	 * submit_stock_claims method, changes the association of the stock claims from the cart unique to the purchase log ID
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function submit_stock_claims($purchase_log_id) {
    global $wpdb;
    //exit($wpdb->prepare("UPDATE `".WPSC_TABLE_CLAIMED_STOCK."` SET `cart_id` = '%d', `cart_submitted` = '1' WHERE `cart_id` IN('%s')", $purchase_log_id, $this->unique_id));
		$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_CLAIMED_STOCK."` SET `cart_id` = '%d', `cart_submitted` = '1' WHERE `cart_id` IN('%s')", $purchase_log_id, $this->unique_id));
	}
	
	 	/**
	 * cleanup method, cleans up the cart just before final destruction
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function cleanup() {
    global $wpdb;
    //echo $wpdb->prepare("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN ('%s')", $this->unique_id);
		$wpdb->query($wpdb->prepare("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN ('%s')", $this->unique_id));
	}
	
  /**
	 * calculate total price method 
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function calculate_total_price() {
    if($this->total_price == null) {
			$total = $this->calculate_subtotal();
			$total += $this->calculate_total_shipping();
			if(wpsc_tax_isincluded() == false){
				$total += $this->calculate_total_tax();
			}	
			$total -= $this->coupons_amount;
			$this->total_price = $total;
			//exit($this->coupons_amount);
		} else {
		  $total = $this->total_price;
		}
		return $total;
  }


  
  /**
	 * calculate_subtotal method 
	 * @access public
	 *
	 * @param boolean for_shipping = exclude items with no shipping,
	 * @return float returns the price as a floating point value
	*/
  function calculate_subtotal($for_shipping = false) {
    global $wpdb;
    if($for_shipping == true ) {
			$total = 0;
			foreach($this->cart_items as $key => $cart_item) {
			  if($cart_item->uses_shipping == 1) {
					$total += $cart_item->total_price;
				}
			}
    } else {
  	  $total = 0;
		if($this->subtotal == null) {
			foreach($this->cart_items as $key => $cart_item) {
				$total += $cart_item->total_price;
			}
			$this->subtotal = $total;
		} else {
			$total = $this->subtotal;
		}
	}
		return $total;
  }
  
  	/**
	 * calculate total tax method 
	 * @access public
	 * @return float returns the price as a floating point value
	 */
   function calculate_total_tax() {
    global $wpdb, $wpsc_cart;
    $total = 0;
	if(wpsc_tax_isincluded() == false){
    	if($this->total_tax == null) {
			foreach($this->cart_items as $key => $cart_item) {
				$total += $cart_item->tax;
			}
			$this->total_tax = $total;
		} else {
		  $total = $this->total_tax;
		}
		if($this->total_tax != null && $this->coupons_amount > 0){
			$total = ($this->calculate_subtotal()-$this->coupons_amount)/$this->tax_percentage;
		}
		
	}else{
		if($this->total_tax == null) {
			foreach($this->cart_items as $key => $cart_item) {
				$total += $cart_item->taxable_price/(100+$wpsc_cart->tax_percentage)*$wpsc_cart->tax_percentage;
			}
			$this->total_tax = $total;
		} else {
		  $total = $this->total_tax;
		}
	}
		$total = apply_filters('wpsc_convert_tax_prices', $total);
		//If coupon is larger or equal to the total price, then the tax should be 0 as the product is going to be free.

		if($this->coupons_amount >= $this->total_price && !empty($this->coupons_amount)){
			$total = 0;
		}
		return $total;
  }
  
  
  
  	/**
	 * calculate_total_weight method 
	 * @access public
	 *
	 * @param boolean for_shipping = exclude items with no shipping,
	 * @return float returns the price as a floating point value
	*/
  function calculate_total_weight($for_shipping = false) {
    global $wpdb;
    if($for_shipping == true ) {
			foreach($this->cart_items as $key => $cart_item) {
				if($cart_item->uses_shipping == 1) {
					$total += $cart_item->weight*$cart_item->quantity;
				}
			}
    } else {
			foreach($this->cart_items as $key => $cart_item) {
				$total += $cart_item->weight*$cart_item->quantity;
			}
		}
		return $total;
  }
  
  /**
	 * get category url name  method
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function get_item_categories() {
  	$category_list = array();
		foreach($this->cart_items as $key => $cart_item) {
			$category_list = array_merge((array)$cart_item->category_list, $category_list);
		}
		return $category_list;
  }

  /**
	 * get category IDs total price method
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function get_item_category_ids() {
  	$category_list = array();
		foreach($this->cart_items as $key => $cart_item) {
			$category_list = array_merge((array)$cart_item->category_id_list, $category_list);
		}
		return $category_list;
  }

  
   /**
	* calculate_total_shipping method, gets the shipping option from the selected method and associated quotes
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function calculate_total_shipping() {
  	if( ! ( (get_option('shipping_discount')== 1) && (get_option('shipping_discount_value') <= $this->calculate_subtotal() ) ) ){
			$total = $this->calculate_base_shipping();
		//	echo 'Base shipping'.$total;
		//	echo '<br /> Per Item:'.$this->calculate_per_item_shipping();
			$total += $this->calculate_per_item_shipping();
    }else{
			$total = 0;
    }

	 $total = apply_filters('wpsc_convert_total_shipping',$total);

    return $total;
  }
  
   /**
	* calculate_total_shipping method, gets the shipping option from the selected method and associated quotes
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function has_total_shipping_discount() {
  	if(get_option('shipping_discount')== 1) {
  		if(get_option('shipping_discount_value') <= $this->calculate_subtotal() ) {
				return true;
  		}
  	}
    return false;
  }
  
    /**
	* calculate_base_shipping method, gets the shipping option from the selected method and associated quotes
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function calculate_base_shipping() {
       global $wpdb, $wpsc_shipping_modules;
    	if($this->uses_shipping()) {
			if(!empty($this->shipping_quotes) && !is_callable(array($wpsc_shipping_modules[$this->selected_shipping_method]), "getQuote"  )) {
	
				$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
			//	exit('NOT EMPTY<pre>'.print_r($this, true).'</pre>');
			}
			if($this->selected_shipping_option == null){
				$this->get_shipping_option();
			}

			$total = (float)$this->shipping_quotes[$this->selected_shipping_option];

			$this->base_shipping = $total;
		} else {
			
		  $total = 0;
		}
		return $total;
  }
  
    /**
	* calculate_per_item_shipping method, gets the shipping option from the selected method and associated quotesing 
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function calculate_per_item_shipping($method = null) {
    global $wpdb, $wpsc_shipping_modules;
    if($method == null) {
      $method = $this->selected_shipping_method;
    }
		foreach((array)$this->cart_items as $cart_item) {
			$total += $cart_item->calculate_shipping($method);
		}
		if($method == $this->selected_shipping_method) {
			$this->total_item_shipping = $total;
		}
	//	echo("<pre>".print_r($this->selected_shipping_method." ".$method." ".$total,true)."</pre>");
		return $total;
  }
  
  
  /**
	 * uses shipping method, to determine if shipping is used.
	 * @access public
	 *  (!(get_option('shipping_discount')== 1) && (get_option('shipping_discount_value') <= $wpsc_cart->calculate_subtotal()))
	 * @return float returns the price as a floating point value
	*/
  function uses_shipping() {
    global $wpdb;
    $uses_shipping = 0;
    if(($this->uses_shipping == null)) {
			foreach($this->cart_items as $key => $cart_item) {
				$uses_shipping += (int)$cart_item->uses_shipping;
			}
		  $uses_shipping = (bool)$uses_shipping;
		} else {
		  $uses_shipping = $this->uses_shipping;
		}
		return $uses_shipping;
  }
  
	/**
	 * process_as_currency method 
	 * @access public
	 *
	 * @param float a price
	 * @return string a price with a currency sign
	*/
	function process_as_currency($price) {
		global $wpdb, $wpsc_currency_data;
		$currency_type = get_option('currency_type');
		if(count($wpsc_currency_data) < 3) {
			$wpsc_currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
		}
		$price = round($price + pow(10, -2-1), 2);
		$price =  number_format($price, 2, '.', ',');
	
		if($wpsc_currency_data['symbol'] != '') {
			if($nohtml == false) {
				$currency_sign = $wpsc_currency_data['symbol_html'];
			} else {
				$currency_sign = $wpsc_currency_data['symbol'];
			}
		} else {
			$currency_sign = $wpsc_currency_data['code'];
		}
	
		$currency_sign_location = get_option('currency_sign_location');
		switch($currency_sign_location) {
			case 1:
			$output = $price.$currency_sign;
			break;
	
			case 2:
			$output = $price.' '.$currency_sign;
			break;
	
			case 3:
			$output = $currency_sign.$price;
			break;
	
			case 4:
			$output = $currency_sign.'  '.$price;
			break;
		}
		$output = apply_filters('wpsc_price_display_changer', $output);
		return $output;  
  }
  
  	/**
	 * save_to_db method, saves the cart to the database
	 * @access public
	 *
	*/
  function save_to_db($purchase_log_id) {
    global $wpdb;
    
		foreach($this->cart_items as $key => $cart_item) {
		  $cart_item->save_to_db($purchase_log_id);
		}
  }
  
  /**
	 * cart loop methods
	*/
 
  
  function next_cart_item() {
		$this->current_cart_item++;
		$this->cart_item = $this->cart_items[$this->current_cart_item];
		return $this->cart_item;
	}

  
  function the_cart_item() {
		$this->in_the_loop = true;
		$this->cart_item = $this->next_cart_item();
		if ( $this->current_cart_item == 0 ) // loop has just started
			do_action('wpsc_cart_loop_start');
	}

	function have_cart_items() {
		if ($this->current_cart_item + 1 < $this->cart_item_count) {
			return true;
		} else if ($this->current_cart_item + 1 == $this->cart_item_count && $this->cart_item_count > 0) {
			do_action('wpsc_cart_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_cart_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_cart_items() {
		$this->current_cart_item = -1;
		if ($this->cart_item_count > 0) {
			$this->cart_item = $this->cart_items[0];
		}
	}
  
  /**
	 * shipping_methods methods
	*/
	function next_shipping_method() {
		$this->current_shipping_method++;
		$this->shipping_method = $this->shipping_methods[$this->current_shipping_method];
		return $this->shipping_method;
	}
	
	
	function the_shipping_method() {
		$this->shipping_method = $this->next_shipping_method();
	 	$this->get_shipping_quotes();
	}
	
	function have_shipping_methods() {
		if ($this->current_shipping_method + 1 < $this->shipping_method_count) {
			return true;
		} else if ($this->current_shipping_method + 1 == $this->shipping_method_count && $this->shipping_method_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_shipping_methods();
		}
		return false;
	}
	
	function rewind_shipping_methods() {
		$this->current_shipping_method = -1;
		if ($this->shipping_method_count > 0) {
			$this->shipping_method = $this->shipping_methods[0];
		}
	}
	
	/**
	 * shipping_quotes methods
	*/
  function get_shipping_quotes() {
    global $wpdb, $wpsc_shipping_modules;
    $this->shipping_quotes = array();
 //   exit('<pre>'.print_r($this, true).'</pre>');
 	if($this->shipping_method == null){
 		$this->get_shipping_method();
 	}
 		//echo "<pre>".print_r($this->shipping_method,true)."<pre>";
 		//echo "<pre>".print_r($wpsc_shipping_modules,true)."<pre>";
		if(is_callable(array($wpsc_shipping_modules[$this->shipping_method], "getQuote"  ))) {
			$unprocessed_shipping_quotes = $wpsc_shipping_modules[$this->shipping_method]->getQuote();

    }
   // exit('<pre>'.print_r($unprocessed_shipping_quotes,true).'</pre>');
    $num = 0;
    foreach((array)$unprocessed_shipping_quotes as $shipping_key => $shipping_value) {
      //$this->calculate_per_item_shipping();
			$per_item_shipping = $this->calculate_per_item_shipping($this->shipping_method);
      //echo('<pre>'.print_r($unprocessed_shipping_quotes,true).'</pre>');
      $this->shipping_quotes[$num]['name'] = $shipping_key;
      $this->shipping_quotes[$num]['value'] = (float)$shipping_value+(float)$per_item_shipping;
      $num++;
    }
    $this->shipping_quote_count = count($this->shipping_quotes);
  }
  
  function google_shipping_quotes(){
  	global $wpsc_shipping_modules;
		$custom_shipping = get_option('custom_shipping_options');
		if(array_search($this->selected_shipping_method, (array)$this->shipping_methods) === false) {
				//unset($this->selected_shipping_method);
			}
			
			$shipping_quotes = null;
			if($this->selected_shipping_method != null) {
	//	exit('here');
		$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
				// use the selected shipping module
				if ( is_callable( array( $wpsc_shipping_modules[$this->selected_shipping_method], 'getQuote' ) ) ) {
				
					$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
				}
			} else {
			
				//exit('here');
				// otherwise select the first one with any quotes
				foreach((array)$custom_shipping as $shipping_module) {
					
					// if the shipping module does not require a weight, or requires one and the weight is larger than zero
					$this->selected_shipping_method = $shipping_module;
					if ( is_callable( array( $wpsc_shipping_modules[$this->selected_shipping_method], 'getQuote' ) ) ) {
					
						$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
					}
					if(count($this->shipping_quotes) >  $shipping_quote_count) { // if we have any shipping quotes, break the loop.
					
						break;
					}
				}
				
			}

  }
  
	function next_shipping_quote() {
		$this->current_shipping_quote++;
		$this->shipping_quote = $this->shipping_quotes[$this->current_shipping_quote];
		return $this->shipping_quote;
	}
	
	
	function the_shipping_quote() {
		$this->shipping_quote = $this->next_shipping_quote();
		
	}
	
	function have_shipping_quotes() {
		if ($this->current_shipping_quote + 1 < $this->shipping_quote_count) {
			return true;
		} else if ($this->current_shipping_quote + 1 == $this->shipping_quote_count && $this->shipping_quote_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_shipping_quotes();
		}
		return false;
	}
	
	function rewind_shipping_quotes() {
		$this->current_shipping_quote = -1;
		if ($this->shipping_quote_count > 0) {
			$this->shipping_quote = $this->shipping_quotes[0];
		}
	}
	
	/**
	 * Applying Coupons
	 */
	function apply_coupons($couponAmount='', $coupons=''){
		$this->clear_cache();
		$this->coupons_name = $coupons;
		$this->coupons_amount = $couponAmount;	
		$this->calculate_total_price();
		if ( $this->total_price < 0 ) {
			$this->coupons_amount += $this->total_price;
			$this->total_price = null;
			$this->calculate_total_price();
		}
	}
	
}



/**
 * The WPSC Cart Items class
 */
class wpsc_cart_item {
  // each cart item contains a reference to the cart that it is a member of
	var $cart;
	
  // provided values
	var $product_id;
	var $variation_values;
	var $product_variations;
	var $variation_data;
	var $quantity = 1;
	var $provided_price;
	
	
	//values from the database
	var $product_name;
	var $category_list = array();
	var $category_id_list = array();
	var $unit_price;
	var $total_price;
	var $taxable_price = 0;
	var $tax = 0;
	var $weight = 0;
	var $shipping = 0;
	var $product_url;
	var $image_id;
	var $thumbnail_image;
	var $custom_tax_rate = null;
	
	var $is_donation = false;
	var $apply_tax = true;
	var $priceandstock_id;
	
	// user provided values
	var $custom_message = null;
	var $custom_file = null;
	
	
	var $meta = array();
		/**
	 * wpsc_cart_item constructor, requires a product ID and the parameters for the product
	 * @access public
	 *
	 * @param integer the product ID
	 * @param array parameters
	 * @param objcet  the cart object
	 * @return boolean true on sucess, false on failure
	*/
	function wpsc_cart_item($product_id, $parameters, &$cart) {
    global $wpdb;
    // still need to add the ability to limit the number of an item in the cart at once.
    // each cart item contains a reference to the cart that it is a member of, this makes that reference
    // The cart is in the cart item, which is in the cart, which is in the cart item, which is in the cart, which is in the cart item...
    $this->cart = &$cart;


    foreach($parameters as $name => $value) {
			$this->$name = $value;
    }
    
    
		$this->product_id = (int)$product_id;
		// to preserve backwards compatibility, make product_variations a reference to variations.
		$this->product_variations =& $this->variation_values;
		
		
		
    if(($parameters['is_customisable'] == true) && ($parameters['file_data'] != null)) {
      $this->save_provided_file($this->file_data);
    }
		//$this->meta = $meta;
		$this->refresh_item();
	}

	/**
	 * update item method, currently can only update the quantity
	 * will require the parameters to update (no, you cannot change the product ID, delete the item and make a new one)
	 * @access public
	 *
	 * @param integer quantity
	 * #@param array parameters
	 * @return boolean true on sucess, false on failure
	*/
	function update_item($quantity) {
		$this->quantity = (int)$quantity;
		$this->refresh_item();
		$this->update_claimed_stock();

	}
			/**
	 * refresh_item method, refreshes the item, calculates the prices, gets the name
	 * @access public
	 *
	 * @return array array of monetary and other values
	*/
	function refresh_item() {
    global $wpdb, $wpsc_shipping_modules, $wpsc_cart;
    $product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$this->product_id}' LIMIT 1", ARRAY_A);
    $priceandstock_id = 0;

    if(defined('WPSC_ADD_DEBUG_PAGE') && (constant('WPSC_ADD_DEBUG_PAGE') == true)) {
			$this->product_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$this->product_id}' LIMIT 1", ARRAY_A);
    }
    
    if(count($this->variation_values) > 0) {
      // if there are variations, get the price of the combination and the names of the variations.
			$variation_data = $wpdb->get_results("SELECT *FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$this->variation_values)."')", ARRAY_A);
			$this->variation_data = $variation_data;
			$variation_names = array();
			$variation_ids = array();
			foreach($variation_data as $variation_row) {
				$variation_names[] = $variation_row['name'];
				$variation_ids[] = $variation_row['variation_id'];
			}
			
			asort($variation_ids);         
			$variation_id_string = implode(",", $variation_ids);
			
			$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$this->product_id}' AND `value_id` IN ( '".implode("', '",$this->variation_values )."' ) AND `all_variation_ids` IN('$variation_id_string') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($this->variation_values)."' LIMIT 1");	
			
			$priceandstock_values = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
			
			$price = $priceandstock_values['price'];
			$weight = wpsc_convert_weights($priceandstock_values['weight'], $priceandstock_values['weight_unit']);
			$file_id = $priceandstock_values['file'];
			
		} else {
		  $priceandstock_id = 0;
			$weight = wpsc_convert_weights($product['weight'], $product['weight_unit']);
		  // otherwise, just get the price.
			if(($product['special_price'] > 0) and (($product['price'] - $product['special_price']) >= 0)) {
				$sale_discount = (float)$product['special_price'];
			} else {
				$sale_discount = 0;
			}
			$price = $product['price'] - $sale_discount;

			$file_id = $product['file'];
			
			
			// if we are using table rate price
			$levels = get_product_meta($this->product_id, 'table_rate_price');
			if ($levels != '') {
				foreach((array)$levels['quantity'] as $key => $qty) {
					if ($this->quantity >= $qty) {
						$unit_price = $levels['table_price'][$key];
						if ($unit_price != '') {
							$price = $unit_price;
						}
					}
				}
			}
		}
		$price = apply_filters('wpsc_do_convert_price', $price);
		// create the string containing the product name.
		$product_name = $product['name'];
		if(count($variation_names) > 0) {
			$product_name .= " (".implode(", ",$variation_names).")";
		}

		$this->product_name = $product_name;
		$this->priceandstock_id = $priceandstock_id;
		$this->is_donation = (bool)$product['donation'];
		// change notax to boolean and invert it
		$this->apply_tax = !(bool)$product['notax'];
		// change no_shipping to boolean and invert it
		$this->uses_shipping = !(bool)$product['no_shipping'];
		$this->has_limited_stock = (bool)(int)$product['quantity_limited'];
		if($this->is_donation == 1) {
			$this->unit_price = $this->provided_price;
		} else {
			$this->unit_price = $price;
		}
		$this->weight = $weight;
		$this->total_price = $this->unit_price * $this->quantity;

		$category_data = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id`,`".WPSC_TABLE_PRODUCT_CATEGORIES."`.`nice-name`  FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` , `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` IN ('".$product['id']."') AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` AND `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`active` IN('1')", ARRAY_A);

		$this->category_list = array();
		$this->category_id_list = array();
		
		foreach($category_data as $category_row) {
			$this->category_list[] = $category_row['nice-name'];
			$this->category_id_list[] = $category_row['id'];
		}
		
		if($this->apply_tax == true) {
		  $this->taxable_price = $this->total_price;
			$custom_tax = get_product_meta($this->product_id, 'custom_tax');
			if(is_numeric($custom_tax)) {
			  $this->custom_tax_rate = $custom_tax;
			  $this->tax = $this->taxable_price * ($this->custom_tax_rate/100);
			} else {
			  $this->tax = $this->taxable_price * ($wpsc_cart->tax_percentage/100);
			}
		}
		$this->product_url = wpsc_product_url($this->product_id);
		
		$this->image_id = $product['image'];
		if($product['thumbnail_image'] != null) {
			$this->thumbnail_image = $product['thumbnail_image'];
		} else {
			$this->thumbnail_image = $product['image'];
		}

		$product_files = (array)get_product_meta($this->product_id, 'product_files');
		
		if($file_id > 0 && ((count($product_files) <= 0) || (count($this->variation_values) > 0))) {
			$this->file_id = (int)$file_id;
			$this->is_downloadable = true;
		} else if(count($product_files) > 0) {
			$this->file_id = null;
			$this->is_downloadable = true;
		} else {
			$this->file_id = null;
			$this->is_downloadable = false;
		}
		
		if ( is_callable( array( $wpsc_shipping_modules[$this->cart->selected_shipping_method], 'get_item_shipping' ) ) ) {
			$this->shipping = $wpsc_shipping_modules[$this->cart->selected_shipping_method]->get_item_shipping($this);
	  }
	  // update the claimed stock here
	  $this->update_claimed_stock();
	}
		
	/**
	 * Calculate shipping method
	 * if no parameter passed, takes the currently selected method
	 * @access public
	 *
	 * @param string shipping method
	 * @return boolean true on sucess, false on failure
	*/		
		
	function calculate_shipping($method = null) {
    global $wpdb, $wpsc_shipping_modules;
    if($method === null) {
      $method = $this->cart->selected_shipping_method;
    }
		if(method_exists( $wpsc_shipping_modules[$method], "get_item_shipping"  )) {
			$shipping = $wpsc_shipping_modules[$method]->get_item_shipping( $this );
			//echo("<pre>".print_r($shipping,true)."</pre>");
    }
    if($method == $this->cart->selected_shipping_method) {
			$this->shipping = $shipping;
    }
	  return $shipping;
	}
		
	/**
	 * user provided file method
	 * @access public
	 * @param string shipping method
	 * @return boolean true on sucess, false on failure
	*/		
		
	function save_provided_file($file_data) {
    global $wpdb;
		$accepted_file_types['mime'][] = 'image/jpeg';
		$accepted_file_types['mime'][] = 'image/gif';
		$accepted_file_types['mime'][] = 'image/png';
		
		$accepted_file_types['mime'][] = 'image/pjpeg';  // Added for IE compatibility
		$accepted_file_types['mime'][] = 'image/x-png';  // Added for IE compatibility
		//$accepted_file_types['mime'][] = 'image/svg+xml';
		
		
		$accepted_file_types['ext'][] = 'jpeg';
		$accepted_file_types['ext'][] = 'jpg';
		$accepted_file_types['ext'][] = 'gif';
		$accepted_file_types['ext'][] = 'png';
		//$accepted_file_types['ext'][] = 'svg';
		
		
		$can_have_uploaded_image = get_product_meta($this->product_id,'can_have_uploaded_image');
		if ($can_have_uploaded_image=='on') {
		  $mime_type_data = wpsc_get_mimetype($file_data['tmp_name'], true);			
			$name_parts = explode('.',basename($file_data['name']));
			$extension = array_pop($name_parts);
		  if($mime_type_data['is_reliable'] == true) {
		    $mime_type = $mime_type_data['mime_type'];
		  } else {
		    // if we can't use what PHP provides us with, we have to trust the user as there aren't really any other choices.
		    $mime_type = $file_data['type'];
		  }
		  //echo( "<pre>".print_r($mime_type_data,true)."</pre>" );
		  //exit( "<pre>".print_r($file_data,true)."</pre>" );
			if((   (array_search($mime_type, $accepted_file_types['mime']) !== false) || (get_option('wpsc_check_mime_types') == 1) ) && (array_search($extension, $accepted_file_types['ext']) !== false) ) {
			  if(is_file(WPSC_USER_UPLOADS_DIR.$file_data['name'])) {
					$name_parts = explode('.',basename($file_data['name']));
					$extension = array_pop($name_parts);
					$name_base = implode('.',$name_parts);
					$file_data['name'] = null;
					$num = 2;
					//  loop till we find a free file name, first time I get to do a do loop in yonks
					do {
						$test_name = "{$name_base}-{$num}.{$extension}";
						if(!file_exists(WPSC_USER_UPLOADS_DIR.$test_name)) {
							$file_data['name'] = $test_name;
						}
						$num++;
					} while ($file_data['name'] == null);
			  }
			  //exit($file_data['name']);
			  $unique_id =  sha1(uniqid(rand(), true));
				if(move_uploaded_file($file_data['tmp_name'], WPSC_USER_UPLOADS_DIR.$file_data['name']) ) {
					$this->custom_file = array('file_name' => $file_data['name'], 'mime_type' => $mime_type, "unique_id" => $unique_id );			
				}
			}
		}
	}

		
	/**
	 * update_claimed_stock method
	 * Updates the claimed stock table, to prevent people from having more than the existing stock in their carts
	 * @access public
	 *
	 * no parameters, nothing returned
	*/
	function update_claimed_stock() {
		global $wpdb;
		if($this->has_limited_stock == true) {
			$current_datetime = date("Y-m-d H:i:s");
			$wpdb->query($wpdb->prepare("REPLACE INTO`".WPSC_TABLE_CLAIMED_STOCK."` ( `product_id` , `variation_stock_id` , `stock_claimed` , `last_activity` , `cart_id` )VALUES ('%d', '%d', '%s', '%s', '%s');",$this->product_id, $this->priceandstock_id, $this->quantity, $current_datetime, $this->cart->unique_id));
 		}
	}
		
		
	/**
	 * save to database method
	 * @access public
	 *
	 * @param integer purchase log id
	*/
	function save_to_db($purchase_log_id) {
		global $wpdb, $wpsc_shipping_modules;
		
	    if($method === null) {
	      $method = $this->cart->selected_shipping_method;
	    }
		if(method_exists( $wpsc_shipping_modules[$method], "get_item_shipping"  )) {
			$shipping = $wpsc_shipping_modules[$this->cart->selected_shipping_method]->get_item_shipping( $this );
		}
	    if($this->cart->has_total_shipping_discount()) {
				$shipping = 0;
	    }
		if($this->apply_tax == true && wpsc_tax_isincluded() == false) {
			if(is_numeric($this->custom_tax_rate)) {
				$tax_rate = $this->custom_tax_rate;
			} else {
				$tax_rate = $this->cart->tax_percentage;
			}
			$tax = $this->unit_price * ($tax_rate/100);
		} else {
			$tax = 0;
			$tax_rate = 0;
		}		
		
		$wpdb->query($wpdb->prepare("INSERT INTO `".WPSC_TABLE_CART_CONTENTS."` (`prodid`, `name`, `purchaseid`, `price`, `pnp`,`tax_charged`, `gst`, `quantity`, `donation`, `no_shipping`, `custom_message`, `files`, `meta`) VALUES ('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '0', '%s', '%s', NULL)", $this->product_id, $this->product_name, $purchase_log_id, $this->unit_price, (float)$shipping, (float)$tax, (float)$tax_rate, $this->quantity, $this->is_donation, $this->custom_message, serialize($this->custom_file)));
		$cart_id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_CART_CONTENTS."` LIMIT 1");
		
		foreach((array)$this->variation_data as $variation_row) {
			$wpdb->query("INSERT INTO `".WPSC_TABLE_CART_ITEM_VARIATIONS."` ( `cart_id` , `variation_id` , `value_id` ) VALUES ( '".$cart_id."', '".$variation_row['variation_id']."', '".$variation_row['id']."' );");
		}
		
    $downloads = get_option('max_downloads');
		if($this->is_downloadable == true) {
			//$product_files = $wpdb->get_row("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` = '".$this->product_id."' AND `meta_key` = 'product_files'", ARRAY_A);
			//$product_files = unserialize($product_files["meta_value"]);
			$product_files = get_product_meta($this->product_id, 'product_files');
			
			if($this->file_id != null){
				// if the file is downloadable, check that the file is real
				if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id` IN ('{$this->file_id}')")) {
					$unique_id = sha1(uniqid(mt_rand(), true));
					$wpdb->query("INSERT INTO `".WPSC_TABLE_DOWNLOAD_STATUS."` (`product_id` , `fileid` , `purchid` , `cartid`, `uniqueid`, `downloads` , `active` , `datetime` ) VALUES ( '{$this->product_id}', '{$this->file_id}', '{$purchase_log_id}', '{$cart_id}', '{$unique_id}', '$downloads', '0', NOW( ));");
				}
			}	else {
				foreach($product_files as $file){
					// if the file is downloadable, check that the file is real
					if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id` IN ('{$file}')")) {
						$unique_id = sha1(uniqid(mt_rand(), true));
						$wpdb->query("INSERT INTO `".WPSC_TABLE_DOWNLOAD_STATUS."` (`product_id` , `fileid` , `purchid` , `cartid`, `uniqueid`, `downloads` , `active` , `datetime` ) VALUES ( '{$this->product_id}', '{$file}', '{$purchase_log_id}', '{$cart_id}', '{$unique_id}', '$downloads', '0', NOW( ));");
					}
				}
			}
		}

		
		do_action('wpsc_save_cart_item', $cart_id, $this->product_id);
	}
	
}
?>
