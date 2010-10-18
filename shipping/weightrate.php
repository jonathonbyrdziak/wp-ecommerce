<?php
class weightrate {
	var $internal_name, $name;
	function weightrate () {
		$this->internal_name = "weightrate";
		$this->name="Weight Rate";
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
	//	$output ="<table>";
		$output.="<tr><th>".__('Total weight <br />(<abbr alt="You must enter the weight here in pounds, regardless of what you used on your products" title="You must enter the weight here in pounds, regardless of what you used on your products">in Pounds</abbr>)', 'wpsc')."</th><th>".__('Shipping Price', 'wpsc')."</th></tr>";
		$layers = get_option("weight_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr class='rate_row'><td >";
				$output .="<i style='color: grey;'>".__('If weight is ', 'wpsc')."</i><input type='text' value='$key' name='weight_layer[]'size='4'><i style='color: grey;'>".__(' and above', 'wpsc')."</i></td><td>".wpsc_get_currency_symbol()."<input type='text' value='{$shipping}' name='weight_shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button' >".__('Delete', 'wpsc')."</a></td></tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='weight'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' id='addweightlayer' >Add Layer</a></td></tr>";
	//	$output .="</table>";
		return $output;
	}
	
	function submit_form() {
		$layers = (array)$_POST['weight_layer'];
		$shippings = (array)$_POST['weight_shipping'];
		if ($shippings != ''){
			foreach($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}
		if ($_POST['checkpage'] == 'weight') {
			update_option('weight_rate_layers',$new_layer);
		}
		return true;
	}
	
	function getQuote() {
		global $wpdb;
		$weight = wpsc_cart_weight_total();
		$layers = get_option('weight_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					return array("Weight Rate"=>$shipping);
				}
			}
		
			return array("Weight Rate"=>array_shift($layers));
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
		$layers = get_option('weight_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					$output = $shipping;
				}
			}
		}
	  return $output;
	}
}
$weightrate = new weightrate();
$wpsc_shipping_modules[$weightrate->getInternalName()] = $weightrate;