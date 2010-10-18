<?php
/**

*/
$nzshpcrt_gateways[$num] = array(
	'name' => 'Paypal Payments Standard 2.0',
	'api_version' => 2.0,
	'class_name' => 'wpsc_merchant_paypal_standard',
	'has_recurring_billing' => true,
	'wp_admin_cannot_cancel' => true,
	'requirements' => array(
		/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'php_version' => 4.3,
		 /// for modules that may not be present, like curl
		'extra_modules' => array()
	),
	
	// this may be legacy, not yet decided
	'internalname' => 'wpsc_merchant_paypal_standard',

	// All array members below here are legacy, and use the code in paypal_multiple.php
	'form' => 'form_paypal_multiple',
	'submit_function' => 'submit_paypal_multiple',
	'payment_type' => 'paypal',
	'supported_currencies' => array(
		'currency_list' =>  array('AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'),
		'option_name' => 'paypal_curcode'
	)
);



/**
	* WP eCommerce Paypal Standard Merchant Class
	*
	* This is the paypal standard merchant class, it extends the base merchant class
	*
	* @package wp-e-commerce
	* @since 3.7.6
	* @subpackage wpsc-merchants
*/
class wpsc_merchant_paypal_standard extends wpsc_merchant {
  var $name = 'Paypal Payments Standard';
  var $paypal_ipn_values = array();

	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access public
	*/
	function construct_value_array() {
		//$collected_gateway_data
		$paypal_vars = array();
		// Store settings to be sent to paypal
		$paypal_vars += array(
			'business' => get_option('paypal_multiple_business'),
			'return' => add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url']),
			'cancel_return' => $this->cart_data['transaction_results_url'],
			'notify_url' => add_query_arg('gateway', 'wpsc_merchant_paypal_standard', $this->cart_data['notification_url']), 
			'rm' => '2',
			'currency_code' => $this->cart_data['store_currency'],
			'lc' => $this->cart_data['store_currency'],
			'bn' => $this->cart_data['software_name'],
			
			'no_note' => '1',
			'charset' => 'utf-8'
			);
			
		//used to send shipping
		if((int)(bool)get_option('paypal_ship') == 1) {
			$paypal_vars += array(
				'address_override' => '1',
				'no_shipping' => '0'
			);
		}


		// User settings to be sent to paypal
		$paypal_vars += array(
			'email' => $this->cart_data['email_address'],
			'first_name' => $this->cart_data['shipping_address']['first_name'],
			'last_name' => $this->cart_data['shipping_address']['last_name'],
			'address1' => $this->cart_data['shipping_address']['address'],
			'city' => $this->cart_data['shipping_address']['city'],
			'country' => $this->cart_data['shipping_address']['country'],
			'zip' => $this->cart_data['shipping_address']['post_code']
			);
		if($this->cart_data['shipping_address']['state'] != '') {
			$paypal_vars += array(
				'state' => $this->cart_data['shipping_address']['state']
			);
		}


		// Order settings to be sent to paypal
		$paypal_vars += array(
			//'tax' => '',
			//'custom' => '',
			'invoice' => $this->cart_data['session_id']
			);

			if($this->cart_data['is_subscription'] == true) {


				$reprocessed_cart_data['shopping_cart'] = array(
					'is_used' => false,
					'price' => 0,
					'length' => 1,
					'unit' => 'd',
					'times_to_rebill' => 1
				);
				
				$reprocessed_cart_data['subscription'] = array(
					'is_used' => false,
					'price' => 0,
					'length' => 1,
					'unit' => 'D',
					'times_to_rebill' => 1
				);
				
				foreach($this->cart_items as $cart_row) {
					if($cart_row['is_recurring'] == true) {
						$reprocessed_cart_data['subscription']['is_used'] = true;
						$reprocessed_cart_data['subscription']['price'] = $cart_row['price'];
						$reprocessed_cart_data['subscription']['length'] = $cart_row['recurring_data']['rebill_interval']['length'];
						$reprocessed_cart_data['subscription']['unit'] = strtoupper($cart_row['recurring_data']['rebill_interval']['unit']);
						$reprocessed_cart_data['subscription']['times_to_rebill'] =$cart_row['recurring_data']['times_to_rebill'];

					} else {
						$item_cost = ($cart_row['price'] + $cart_row['shipping'] + $cart_row['tax']) + $cart_row['quantity'];
						if($item_cost > 0) {
							$reprocessed_cart_data['shopping_cart']['price'] += $item_cost;
							$reprocessed_cart_data['shopping_cart']['is_used'] = true;
						}
					}
					
					$paypal_vars += array(
						"item_name" => __('Your Subscription', 'wpsc'),
						"src" => "1" // I fail to see the point of sending a subscription to paypal as a subscription if it does not recur, if (src == 0) then (this == underfeatured waste of time)
					);


					// this can be false, we don't need to have additional items in the cart
					if($reprocessed_cart_data['shopping_cart']['is_used'] == true) {
						$paypal_vars += array(
							"a1" => $this->format_price($reprocessed_cart_data['shopping_cart']['price']),
							"p1" => $reprocessed_cart_data['shopping_cart']['length'],
							"t1" => $reprocessed_cart_data['shopping_cart']['unit'],
						);
					}
					
					//we need at least one subscription product,  if we are in thise piece of code and this is not true, something is rather wrong
					if($reprocessed_cart_data['subscription']['is_used'] == true) {
						$paypal_vars += array(
							"a3" => $this->format_price($reprocessed_cart_data['subscription']['price']),
							"p3" => $reprocessed_cart_data['subscription']['length'],
							"t3" => $reprocessed_cart_data['subscription']['unit'],
						);
						// If the srt value for the number of times to rebill is not greater than 1, paypal won't accept the transaction.
						if($reprocessed_cart_data['subscription']['times_to_rebill'] > 1) {
							$paypal_vars += array("srt" => $reprocessed_cart_data['subscription']['times_to_rebill']);
						}
					}


				
				}

				
			} else {
				// Stick the cart item values together here
				$i = 1;
				foreach($this->cart_items as $cart_row) {
					$paypal_vars += array(
						"item_name_$i" => $cart_row['name'],
						"amount_$i" => $this->format_price($cart_row['price']),
						"tax_$i" => $this->format_price($cart_row['tax']),
						"quantity_$i" => $cart_row['quantity'],
						"item_number_$i" => $cart_row['product_id'],
						"shipping_$i" => $this->format_price($cart_row['shipping']/$cart_row['quantity']), // additional shipping for the the (first item / total of the items)
						"shipping2_$i" => $this->format_price($cart_row['shipping']/$cart_row['quantity']), // additional shipping beyond the first item
						"handling_$i" => '',
					);
					++$i;
				}
			}
		
		//set base shipping
		$paypal_vars += array(
			"handling_cart" => $this->cart_data['base_shipping']
		);		
		
		// Payment Type settings to be sent to paypal
		if($this->cart_data['is_subscription'] == true) {
			$paypal_vars += array(
				'cmd'=> '_xclick-subscriptions'
			);
		} else {
			$paypal_vars += array(
				'upload' => '1',
				'cmd' => '_ext-enter',
				'redirect_cmd' => '_cart'
			);
		}


		$this->collected_gateway_data = $paypal_vars;
	}
	
	/**
	* submit method, sends the received data to the payment gateway
	* @access public
	*/
	function submit() {
		$name_value_pairs = array();
		foreach($this->collected_gateway_data as $key=>$value) {
			//$output .= $key.'='.urlencode($value).$amp;
			$name_value_pairs[]= $key.'='.urlencode($value);
		}
		$gateway_values =  implode('&', $name_value_pairs);

		
		if(defined('WPSC_ADD_DEBUG_PAGE') and (WPSC_ADD_DEBUG_PAGE == true) ) {
			echo "<a href='".get_option('paypal_multiple_url')."?".$gateway_values."'>Test the URL here</a>";
			//echo "<pre>".print_r($this->cart_items,true)."</pre>";
			echo "<pre>".print_r($this->collected_gateway_data,true)."</pre>";
			exit();
		}
		header("Location: ".get_option('paypal_multiple_url')."?".$gateway_values);
		exit();
	}


	/**
	* parse_gateway_notification method, receives data from the payment gateway
	* @access private
	*/
	function parse_gateway_notification() {
		/// PayPal first expects the IPN variables to be returned to it within 30 seconds, so we do this first.
		$paypal_url = get_option('paypal_multiple_url');
		$received_values = array();
		$received_values['cmd'] = '_notify-validate';
    $received_values += $_POST;
		$options = array(
			'timeout' => 5,
			'body' => $received_values,
			'user-agent' => ('WP e-Commerce/'.WPSC_PRESENTABLE_VERSION)
		);
		
		$response = wp_remote_post($paypal_url, $options);
		if(strpos($response['body'], 'VERIFIED') !== false) {
			$this->paypal_ipn_values = $received_values;
			$this->session_id = $received_values['invoice'];
		} else {
			exit("IPN Request Failure");
		}
	}

	/**
	* process_gateway_notification method, receives data from the payment gateway
	* @access public
	*/
	function process_gateway_notification() {
	  // Compare the received store owner email address to the set one
		if(strtolower($this->paypal_ipn_values['receiver_email']) == strtolower(get_option('paypal_multiple_business'))) {
			switch($this->paypal_ipn_values['txn_type']) {
				case 'cart':
				case 'express_checkout':
					if((float)$this->paypal_ipn_values['mc_gross'] == (float)$this->cart_data['total_price']) {
						$this->set_transaction_details($this->paypal_ipn_values['txn_id'], 2);
						transaction_results($this->cart_data['session_id'],false);
					}
				break;

				case 'subscr_signup':
				case 'subscr_payment':
					$this->set_transaction_details($this->paypal_ipn_values['subscr_id'], 2);
					foreach($this->cart_items as $cart_row) {
						if($cart_row['is_recurring'] == true) {
							do_action('wpsc_activate_subscription', $cart_row['cart_item_id'], $this->paypal_ipn_values['subscr_id']);
						}
					}
					transaction_results($this->cart_data['session_id'],false);
				break;
				
				case 'subscr_cancel':
				case 'subscr_eot':
				case 'subscr_failed':
					foreach($this->cart_items as $cart_row) {
						$altered_count = 0;
						if((bool)$cart_row['is_recurring'] == true) {
							$altered_count++;
							wpsc_update_cartmeta($cart_row['cart_item_id'], 'is_subscribed', 0);
						}
					}
				break;
				
				default:
				break;
			}
		}

		$message = "
		{$this->paypal_ipn_values['receiver_email']} => ".get_option('paypal_multiple_business')."
		{$this->paypal_ipn_values['txn_type']}
		{$this->paypal_ipn_values['mc_gross']} => {$this->cart_data['total_price']}
		{$this->paypal_ipn_values['txn_id']}

		".print_r($this->cart_items, true)."
		{$altered_count}
		";
	}



	function format_price($price) {
		$paypal_currency_code = get_option('paypal_curcode');
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
		$price = number_format(sprintf("%01.2f",$price),$decimal_places,'.','');
		return $price;
	}


	
}
?>