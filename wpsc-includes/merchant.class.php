<?php
/**
	* WP eCommerce Base Merchant Class
	*
	* This is the base merchant class, all merchant files that use the new API extend this class.
	* 
	*
	* @package wp-e-commerce
	* @since 3.7.6
	* @abstract
	* @subpackage wpsc-merchants
*/

class wpsc_merchant {
  var $name = 'Base Merchant';
  
  var $is_receiving = false;
  var $purchase_id = null;
  var $session_id = null;

  var $received_data = array();
  
  /**
  * This is where the cart data, like the address, country and email address is held
  * @var array
  */
  var $cart_data = array();
  
  /**
  * This is where the cart items are stored
  @var array
  */
  var $cart_items = array();

  /**
  * This is where the data to be sent is gathered before being converted to the necessary format and sent. 
  * @var array
  */
  var $collected_gateway_data = array();

  
	/**
	* collate_data method, collate purchase data, like addresses, like country
	* @access public
	*/
	function __construct($purchase_id = null, $is_receiving = false ) {
		global $wpdb;
		//$purchase_logs = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = {$purchase_id} LIMIT 1") ;
		if(($purchase_id == null) && ($is_receiving == true)) {
			$this->is_receiving = true;
			$this->parse_gateway_notification();
		}
		if($purchase_id > 0) {
			$this->purchase_id = $purchase_id;
		}
		$this->collate_data();
		$this->collate_cart();

	}
	
	function wpsc_merchant($purchase_id = null, $is_receiving = false){
		if(version_compare(PHP_VERSION,"5.0.0","<")){
			$this->__construct($purchase_id, $is_receiving);
		}
	}

	/**
	* collate_data method, collate purchase data, like addresses, like country
	* @access public
	*/
  function collate_data() {
		global $wpdb;

		// get purchase data, regardless of being fed the ID or the sessionid
		if($this->purchase_id > 0) {
			$purchase_id = & $this->purchase_id;
			$purchase_logs = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = {$purchase_id} LIMIT 1", ARRAY_A) ;
		} else if($this->session_id != null) {
			$purchase_logs = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` = {$this->session_id} LIMIT 1", ARRAY_A) ;
			$this->purchase_id = $purchase_logs['id'];
			$purchase_id = & $this->purchase_id;
		}
		
		$email_address = $wpdb->get_var("SELECT `value` FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` AS `form_field` INNER JOIN `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` AS `collected_data` ON `form_field`.`id` = `collected_data`.`form_id` WHERE `form_field`.`type` IN ( 'email' ) AND `collected_data`.`log_id` IN ( '{$purchase_id}' )");

		$currency_code = $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1");

		$collected_form_data = $wpdb->get_results("SELECT `data_names`.`id`, `data_names`.`unique_name`, `collected_data`.`value` FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` AS `collected_data` JOIN `".WPSC_TABLE_CHECKOUT_FORMS."` AS `data_names` ON `collected_data`.`form_id` = `data_names`.`id` WHERE `log_id` = '".$purchase_id."'", ARRAY_A);



		$address_keys = array(
			'billing' => array(
				'first_name'	=>	'billingfirstname',
				'last_name'	=>	'billinglastname',
				'address'	=>	'billingaddress',
				'city'	=>	'billingcity',
				'state'	=>	'billingstate',
				'country'	=>	'billingcountry',
				'post_code'	=>	'billingpostcode',
			),
			'shipping' => array(
				'first_name'	=>	'shippingfirstname',
				'last_name'	=>	'shippinglastname',
				'address'	=>	'shippingaddress',
				'city'	=>	'shippingcity',
				'state'	=>	'shippingstate',
				'country'	=>	'shippingcountry',
				'post_code'	=>	'shippingpostcode',
			)
		);
		
		$address_data = array(
			'billing' => array(),
			'shipping' => array()
		);
		
		foreach((array)$collected_form_data as $collected_form_row) {
			$address_data_set = 'billing';
			$address_key =  array_search($collected_form_row['unique_name'], $address_keys['billing']);
			if($address_key == null) {
				$address_data_set = 'shipping';
//					exit('<pre>'.print_r($collected_form_row,true).'</pre>');
				$address_key =  array_search($collected_form_row['unique_name'], $address_keys['shipping']);
			}
			if($address_key == null) {
				continue;
			}
			if($collected_form_row['unique_name'] == 'billingcountry' || $collected_form_row['unique_name'] == 'shippingcountry'){
				$country = maybe_unserialize($collected_form_row['value']);
				$address_data[$address_data_set][$address_key] =$country[0];
			}elseif($collected_form_row['unique_name'] == 'shippingstate'){
				$address_data[$address_data_set][$address_key] = wpsc_get_state_by_id($collected_form_row['value'], 'code');			
			}else{
				$address_data[$address_data_set][$address_key] = $collected_form_row['value'];
			}
		}
//		exit('<pre>'.print_r($address_data,true).'</pre>');
		if(count($address_data['shipping']) < 1) {
			$address_data['shipping'] = $address_data['billing'];
		}
		
		$this->cart_data = array(
			'software_name' => 'WP e-Commerce/'.WPSC_PRESENTABLE_VERSION.'',
			// 'store_name' => '',  /// is this useful or needed?
			'store_location' => get_option('base_country'),
			'store_currency' => $currency_code,
			'is_subscription' => false,
			'has_discounts' => false,
			'notification_url' => add_query_arg('wpsc_action', 'gateway_notification', (get_option('siteurl')."/index.php")),
			'transaction_results_url' => get_option('transact_url'),
			'shopping_cart_url' => get_option('shopping_cart_url'),
			'products_page_url' => get_option('product_list_url'),
			'base_shipping' => $purchase_logs['base_shipping'],
			'total_price' => $purchase_logs['totalprice'],
			'session_id' => $purchase_logs['sessionid'],
 			'transaction_id' => $purchase_logs['transaction_id'], // Transaction ID might not  be set yet
			'email_address' => $email_address,
			'billing_address' => $address_data['billing'],
			'shipping_address' => $address_data['shipping']
		);
  }
	
	/**
	* collate_cart method, collate cart data
	* @access public
	*
	*/
  function collate_cart() {
		global $wpdb;
		$purchase_id = & $this->purchase_id;
		$original_cart_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid` = {$purchase_id}", ARRAY_A);
		//return;
		foreach((array)$original_cart_data as $cart_row) {
		  $is_downloadable = false;
			if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `cartid` = {$cart_row['id']}")) {
				$is_downloadable = true;
			}
			
			$is_recurring= (bool) wpsc_get_cartmeta($cart_row['id'],'is_recurring',true);

			if($is_recurring == true) {
				$this->cart_data['is_subscription'] = true;
			}
			$rebill_interval = wpsc_get_cartmeta($cart_row['id'],'rebill_interval',true);
			$new_cart_item = array(
				"cart_item_id" => $cart_row['id'],
				"product_id" => $cart_row['prodid'],
				"name" => $cart_row['name'],
				"price" => $cart_row['price'],
				"shipping" => $cart_row['pnp'],
				"tax" => $cart_row['tax_charged'],
				"quantity" => $cart_row['quantity'],
				"is_downloadable" => $is_downloadable,
				"is_capability" => (bool) wpsc_get_cartmeta($cart_row['id'],'provided_capabilities',true),
				"is_recurring" => $is_recurring,
				"is_subscription" => $is_recurring,
				"recurring_data" => array(
					"rebill_interval" => array(
						'unit' => $rebill_interval['unit'],
						'length' => $rebill_interval['interval']
					),
					"charge_to_expiry" => (bool) wpsc_get_cartmeta($cart_row['id'],'charge_to_expiry',true),
					"times_to_rebill" => wpsc_get_cartmeta($cart_row['id'],'times_to_rebill',true),
				)
			);
			$this->cart_items[] = $new_cart_item;
			
		}
  }

  
    /**
	* set_error_message, please don't extend this without very good reason
	* saves error message, data it is stored in may need to change, hence the need to not extend this.
	*/
  function set_error_message($error_message) {
		global $wpdb;
		$_SESSION['wpsc_checkout_misc_error_messages'][] = $error_message;
  }
  
    /**
	* return_to_checkout, please don't extend this without very good reason
	* returns to checkout, if this changes and you extend this, your merchant module may go to the wrong place
	*/
  function return_to_checkout() {
		global $wpdb;
		wp_redirect(get_option('shopping_cart_url'));
		exit(); // follow the redirect with an exit, just to be sure.
  }

   /**
	* go_to_transaction_results, please don't extend this without very good reason
	* go to transaction results, if this changes and you extend this, your merchant module may go to the wrong place
	*/
  function go_to_transaction_results($session_id) {
		global $wpdb;
		$transaction_url_with_sessionid = add_query_arg('sessionid', $session_id, get_option('transact_url'));
		wp_redirect($transaction_url_with_sessionid);
    exit(); // follow the redirect with an exit, just to be sure.
  }


  
  /**
	* set_transaction_details, maybe extended in merchant files
	*/
  function set_transaction_details($transaction_id, $status = 1) {
		global $wpdb;
		$transaction_id = $wpdb->escape($transaction_id);
		$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '".absint($status)."', `transactid` ='{$transaction_id}'  WHERE `id` = ".absint($this->purchase_id)." LIMIT 1");
		//echo("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '".absint($status)."', `transactid` ='{$transaction_id}'  WHERE `id` IN ('".absint($this->purchase_id)."') LIMIT 1");
  }
	/**
	* construct_value_array gateway specific data array, extended in merchant files
	* @abstract
	* @todo When we drop support for PHP 4, make this a proper abstract method
	*/
  function construct_value_array() {
		return false;
  }
	
	/**
	* submit to gateway, extended in merchant files
	* @abstract
	* @todo When we drop support for PHP 4, make this a proper abstract method
	*/
  function submit() {
		return false;
  }
  
	/**
	* parse gateway notification, recieves and converts the notification to an array, if possible, extended in merchant files
	* @abstract
	* @todo When we drop support for PHP 4, make this a proper abstract method
	*/
	function parse_gateway_notification() {
		return false;
	}
  
	/**
	* process gateway notification, checks and decides what to do with the data from the gateway, extended in merchant files
	* @abstract
	* @todo When we drop support for PHP 4, make this a proper abstract method
	*/
	function process_gateway_notification() {
		return false;
	}
}
?>