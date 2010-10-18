<?php
class flatrate {
	var $internal_name, $name;
	function flatrate () {
		$this->internal_name = "flatrate";
		$this->name="Flat Rate";
		$this->is_external=false;
		return true;
	}
	
	function getId() {
// 		return $this->usps_id;
	}
	
	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	
	function getForm() {
		$shipping = get_option('flat_rates');
		$output = "<tr><td colspan='2'>" . __('If you do not wish to ship to a particular region, leave the field blank. To offer free shipping to a region, enter 0.', 'wpsc') . "</td>";
		$output .= "<tr><td colspan='2'>" . __('Note: If you apply $0 to any region the postage and packaging applied to products will still be added on at checkout.', 'wpsc') . "</td>";
		$output .= "<tr><td colspan='1'><strong>Base Local</strong></td>";
		
		switch(get_option('base_country')) {
		  case 'NZ':
			$output .= "<tr class='rate_row'><td>South Island</td><td>$<input type='text' size='4' name='shipping[southisland]' value='{$shipping['southisland']}'></td></tr>";
			$output .= "<tr class='rate_row'><td>North Island</td><td>$<input type='text' size='4' name='shipping[northisland]'  value='{$shipping['northisland']}'></td></tr>";
		  break;
		  
		  case 'US':
			$output .= "<tr class='rate_row'><td>Continental 48 States</td><td>$<input type='text' size='4' name='shipping[continental]' value='{$shipping['continental']}'></td></tr>";
			$output .= "<tr class='rate_row'><td>All 50 States</td><td>$<input type='text' size='4' name='shipping[all]'  value='{$shipping['all']}'></td></tr>";
		  break;
		  
		  default:
			$output .= "<td>$<input type='text' name='shipping[local]' size='4' value='{$shipping['local']}'></td></tr>";		  
		  break;	
		}

		$output.= "<tr ><td colspan='2'><strong>Base International</strong></td></tr>";
		$output .= "<tr class='rate_row'><td>North America</td><td>$<input size='4' type='text' name='shipping[northamerica]'  value='{$shipping['northamerica']}'></td></tr>";
		$output .= "<tr class='rate_row'><td>South America</td><td>$<input size='4' type='text' name='shipping[southamerica]'  value='{$shipping['southamerica']}'></td></tr>";
		$output .= "<tr class='rate_row'><td>Asia and Pacific</td><td>$<input size='4' type='text' name='shipping[asiapacific]'  value='{$shipping['asiapacific']}'></td></tr>";
		$output .= "<tr class='rate_row'><td>Europe</td><td>$<input type='text' size='4' name='shipping[europe]'  value='{$shipping['europe']}'></td></tr>";
		$output .= "<tr class='rate_row'><td>Africa</td><td>$<input type='text' size='4' name='shipping[africa]'  value='{$shipping['africa']}'></td></tr>";
		return $output;
	}
	
	function submit_form() {
	  if($_POST['shipping'] != null) {
			$shipping = (array)get_option('flat_rates');
			$submitted_shipping = (array)$_POST['shipping'];
			update_option('flat_rates',array_merge($shipping, $submitted_shipping));
		}
		return true;
	}
	
	function getQuote($for_display = false) {
		global $wpdb, $wpsc_cart;
		if (isset($_POST['country'])) {
			$country = $_POST['country'];
			$_SESSION['wpsc_delivery_country'] = $country;
		} else {
			$country = $_SESSION['wpsc_delivery_country'];
		}
		$_SESSION['quote_shipping_option'] = null;

		if (get_option('base_country') != $country) {
			$results = $wpdb->get_var("SELECT `continent` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN('{$country}') LIMIT 1");
			$flatrates = get_option('flat_rates');
				$_SESSION['quote_shipping_option'] = null;

			if ($flatrates != '') {
					
				if($_SESSION['quote_shipping_method'] == $this->internal_name) {
					if($_SESSION['quote_shipping_option'] != "Flat Rate") {
						$_SESSION['quote_shipping_option'] = null;
					}
				}
			  
				if (strlen($flatrates[$results]) > 0) return array("Flat Rate"=>(float)$flatrates[$results]);
			}
		} else {
			$flatrates = get_option('flat_rates');
			$shipping_quotes = array();
			switch($country) {
			  case 'NZ':
				if (strlen($flatrates['northisland']) > 0) $shipping_quotes["North Island"] = (float)$flatrates['northisland'];
				if (strlen($flatrates['southisland']) > 0) $shipping_quotes["South Island"] = (float)$flatrates['southisland'];
			  break;
			  
			  case 'US':
				if (strlen($flatrates['continental']) > 0) $shipping_quotes["Continental 48 States"] = (float)$flatrates['continental'];
				if (strlen($flatrates['all']) > 0) $shipping_quotes["All 50 States"] = (float)$flatrates['all'];
			  break;
			  
			  default:
				if (strlen($flatrates['local']) > 0) $shipping_quotes["Local Shipping"] = (float)$flatrates['local'];
				break;
			}
			if($_SESSION['quote_shipping_method'] == $this->internal_name) {
			  $shipping_options = array_keys($shipping_quotes);
			  if(array_search($_SESSION['quote_shipping_option'], $shipping_options) === false) {
					$_SESSION['quote_shipping_option'] = null;
			  }
			}

			return $shipping_quotes;
		}
	}
	
	
	function get_item_shipping(&$cart_item) {
		global $wpdb, $wpsc_cart;
		$unit_price = $cart_item->unit_price;
		$quantity = $cart_item->quantity;
		$weight = $cart_item->weight;
		$product_id = $cart_item->product_id;

		
		$uses_billing_address = false;
		foreach((array)$cart_item->category_id_list as $category_id) {
			$uses_billing_address = (bool)wpsc_get_categorymeta($category_id, 'uses_billing_address');
			if($uses_billing_address === true) {
			  break; /// just one true value is sufficient
			}
		}

    if(is_numeric($product_id) && (get_option('do_not_use_shipping') != 1)) {
			if($uses_billing_address == true) {
				$country_code = $wpsc_cart->selected_country;
			} else {
				$country_code = $wpsc_cart->delivery_country;
			}
			
      $product_list = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
      if($product_list['no_shipping'] == 0) {
        //if the item has shipping
        if($country_code == get_option('base_country')) {
          $additional_shipping = $product_list['pnp'];
				} else {
          $additional_shipping = $product_list['international_pnp'];
				}          
        $shipping = $quantity * $additional_shipping;
			} else {
        //if the item does not have shipping
        $shipping = 0;
			}
		} else {
      //if the item is invalid or all items do not have shipping
			$shipping = 0;
		}
    return $shipping;	
	}
	
	function get_cart_shipping($total_price, $weight) {
	  return $output;
	}
}
$flatrate = new flatrate();
$wpsc_shipping_modules[$flatrate->getInternalName()] = $flatrate;