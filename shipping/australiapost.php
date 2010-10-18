<?php
/*  Copyright 2009 OM4 (email: info@om4.com.au    web: http://om4.com.au/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/**
 * WP e-Commerce Australia Post shipping module - http://auspost.com.au
 *
 */
class australiapost {
	var $internal_name, $name;
	
	/**
	 * List of Valid Australia Post services
	 *
	 * @var Array
	 */
	var $services = array();
	
	/**
	 * Shipping module settings
	 *
	 * @var Array
	 */
	var $settings;
	
	var $base_country;
	var $base_zipcode;
	
	/**
	 * Constructor
	 */
	function australiapost () {
		$this->internal_name = "australiapost";
		$this->name = __('Australia Post', 'wpsc');
		$this->is_external = true;
		$this->requires_weight = true;
		$this->needs_zipcode = true;
		$this->debug = false; // change to true to log (to the PHP error log) the API URLs and responses for each active service
		
		// Initialise the list of available postage services
		$this->services['STANDARD'] = __('Standard Parcel Post', 'wpsc');
		$this->services['EXPRESS'] = __('Express Post', 'wpsc');
		$this->services['AIR'] = __('Air Mail', 'wpsc');
		$this->services['SEA'] = __('Sea Mail', 'wpsc');
		$this->services['ECONOMY'] = __('Economy Air Parcel Post', 'wpsc');
		$this->services['EPI'] = __('Express Post International', 'wpsc');
		
		// Attempt to load the existing settings
		$this->settings = get_option("wpsc_australiapost_settings");
		
		$this->base_country = get_option('base_country');
		$this->base_zipcode = get_option('base_zipcode');
		
		if (!$this->settings) {
			// Initialise the settings
			$this->settings = array();
			foreach ($this->services as $code => $value) {
				$this->settings['services'][$code] = true;
			}
		}
		
		return true;
	}
	
	function getId() {
	}
	
	function setId($id) {
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
		// Only for Australian merchants
		if ($this->base_country != 'AU') {
			return __('This shipping module only works if the base country in settings, region is set to Australia.', 'wpsc');
		}
		
		// Base postcode must be set
		if (strlen($this->base_zipcode) != 4) {
			return __('You must set your base postcode above before this shipping module will work.', 'wpsc');
		}
		
		$output .= "<tr><td>" . __('Select the Australia Post services that you want to offer during checkout:', 'wpsc') . "</td></tr>\n\r";
		$output .= "<tr><td>\n\r";
		foreach ($this->services as $code => $value) {
			$checked = $this->settings['services'][$code] ? "checked='checked'" : '';
			$output .= "		<label style=\"margin-left: 50px;\"><input type='checkbox' {$checked} name='wpsc_australiapost_settings[services][{$code}]'/>{$this->services[$code]}</label><br />\n\r";
		}
		$output .= "</td></tr>";
		$output .= "<tr><td><h4>" . __('Notes:', 'wpsc') . "</h4>";
		$output .= __('1. The actual services quoted to the customer during checkout will depend on the destination country. Not all methods are available to all destinations.', 'wpsc') . "<br />";
		$output .= __('2. Each product must have a valid weight configured. When editing a product, use the weight field.).', 'wpsc') . "<br />";
		$output .= __('3. To ensure accurate quotes, each product must valid dimensions configured. When editing a product, use the height, width and length fields.', 'wpsc') . "<br />";
		$output .= __('4. The combined dimensions are estimated by calculating the volume of each item, and then calculating the cubed root of the overall order volume which becomes width, length and height.', 'wpsc') . "<br />";
		$output .= __('5. If no product volumes are defined, then default package dimensions of 100mm x 100mm x 100mm will be used.', 'wpsc') . "<br />";
		$output .= "</tr></td>";
		return $output;
	}
	
	function submit_form() {
		$this->settings['services'] = array();
		
		if (isset($_POST['wpsc_australiapost_settings'])) {
			if (isset($_POST['wpsc_australiapost_settings']['services'])) {
				foreach ($this->services as $code => $name) {
					$this->settings['services'][$code] = isset($_POST['wpsc_australiapost_settings']['services'][$code]) ? true : false;
				}
			}
		}
		// Only save if this module's options were updated
		if (isset($_POST['shippingname']) && $_POST['shippingname'] == $this->internal_name) {
			update_option('wpsc_australiapost_settings', $this->settings);
		}
		return true;
	}
	
	function getQuote() {
		global $wpdb, $wpsc_cart;
		
		if ($this->base_country != 'AU' || strlen($this->base_zipcode) != 4 || !count($wpsc_cart->cart_items)) return;
		
		$dest = $_SESSION['wpsc_delivery_country'];
		
		// Weight is in pounds but needs to be in grams
		$weight = floatval(wpsc_cart_weight_total() * 453.59237);

		$destzipcode = '';
		if(isset($_POST['zipcode'])) {
			$destzipcode = $_POST['zipcode'];      
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
		} else if(isset($_SESSION['wpsc_zipcode'])) {
			$destzipcode = $_SESSION['wpsc_zipcode'];
		}

		if ($dest == 'AU' && strlen($destzipcode) != 4) {
		    // Invalid Australian Post Code entered, so just return an empty set of quotes instead of wasting time contactin the Aus Post API
		    return array();
		}
 
		//Calculate the total cart dimensions by adding the volume of each product then calculating the cubed root
		$volume = 0;
		foreach((array)$wpsc_cart->cart_items as $cart_item) {
			$meta = get_product_meta($cart_item->product_id,'dimensions');
			if ($meta && is_array($meta)) {
				$productVolume = 1;
				foreach (array('width','height','length') as $dimension) {
					
					
					switch ($meta["{$dimension}_unit"]) {
						// we need the units in mm
						case 'cm':
							// convert from cm to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 10);
							break;
						case 'meter':
							// convert from m to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 1000);
							break;
						case 'in':
							// convert from in to mm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 25.4);
							break;
					}
				}
				$volume += floatval($productVolume);
			}
		}
		// Calculate the cubic root of the total volume, rounding up
		$cuberoot = ceil(pow($volume, 1 / 3));
		
		// Use default dimensions of 100mm if the volume is zero
		$height=100;
		$width=100;
		$length=100;
		
		if ($cuberoot > 0) {
			$height = $width = $length = $cuberoot;
		}

		// As per http://auspost.com.au/personal/parcel-dimensions.html: if the parcel is box-shaped, both its length and width must be at least 15cm.
		if ($length < 150) $length = 150;
		if ($width < 150) $width = 150;
		
		// API Documentation: http://drc.edeliver.com.au/
		$url = "http://drc.edeliver.com.au/ratecalc.asp?Pickup_Postcode={$this->base_zipcode}&Destination_Postcode={$destzipcode}&Quantity=1&Weight={$weight}&Height={$height}&Width={$width}&Length={$length}&Country={$dest}";

		$log = '';
		$methods = array();
		foreach ($this->services as $code => $service) {
			if (!$this->settings['services'][$code]) continue;
			
			$fullURL = "$url&Service_Type=$code";
			
			$response = wp_remote_get($fullURL);
			
			// Silently ignore any API server errors
			if ( is_wp_error($response) || $response['response']['code'] != '200' || empty($response['body']) ) continue;

			if ($this->debug) {
			    $log .="  {$fullURL}\n    " . $response['body'] . "\n";
			}
			
			$lines = explode("\n", $response['body']);
			
			foreach($lines as $line) {
				list($key, $value) = explode('=', $line);
				$key = trim($key);
				$value = trim($value);
				switch ($key) {
					case 'charge':
						$methods[$code]['charge'] = floatval($value);
						break;
					case 'days':
						$methods[$code]['days'] = floatval($value);
						break;
					case 'err_msg':
						$methods[$code]['err_msg'] = trim($value);
						break;
				}
			}
			$methods[$code]['name'] = $this->services[$code];
		}
		if ($this->debug)
		    error_log( 'WP e-Commerce Australia Post shipping quotes for ' . site_url() . ":\n----------\n$log----------" );
		
		// Allow another WordPress plugin to override the quoted method(s)/amount(s)
		
		$methods = apply_filters('wpsc_australia_post_methods', $methods, $this->base_zipcode, $destzipcode, $dest, $weight);
		
		$quotedMethods = array();
		
		foreach ($methods as $code => $data) {
			// Only include methods with an OK response
			if ($data['err_msg'] != 'OK') continue;
			
			if ($data['days']) {
				// If the estimated number of days is specified, so include it in the quote
				$text = sprintf(_n('%1$s (estimated delivery time: %2$d business day)', '%1$s (estimated delivery time: %2$d business days)', $data['days'], 'wpsc'), $data['name'], $data['days']);
			} else {
				// No time estimate
				$text = $data['name'];
			}
			$quotedMethods[$text] = $data['charge'];
		}
		return $quotedMethods;
	}
	
	function get_item_shipping() {
	}
}
$australiapost = new australiapost();
$wpsc_shipping_modules[$australiapost->getInternalName()] = $australiapost;
?>