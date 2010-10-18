<?php
function widget_donations($args) {
  global $wpdb, $table_prefix;
  extract($args);
  $options = get_option('wpsc-nzshpcrt_donations');    
	$title = empty($options['title']) ? __(__('Product Donations', 'wpsc')) : $options['title'];
	$donation_count = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `donation` IN ('1') AND `active` IN ('1')");   
	if($donation_count > 0) {
    echo $before_widget; 
    $full_title = $before_title . $title . $after_title;
    echo $full_title;
			nzshpcrt_donations();
    echo $after_widget;
    }
	}

function nzshpcrt_donations($input = null) {
	global $wpdb;
	$siteurl = get_option('siteurl');
	$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `donation` IN ('1') AND `active` IN ('1')";
	$products = $wpdb->get_results($sql,ARRAY_A);
//	exit('<pre>'.print_r($products,true).'</pre>');
	

	if($products != null) {
		$output = "<div><div>";
		foreach($products as $product) {
			$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`=".$product['image'];

			$image = $wpdb->get_row($sql, ARRAY_A);
			//exit('<pre>'.print_r($image,true).'</pre>');
			$output .= "<strong>".$product['name']."</strong><br />";
			if($product['image'] != null) {
				$output .= "<img src='".WPSC_THUMBNAIL_URL.$image['image']."' width='".get_option('product_image_width')."' height='".get_option('product_image_height')."' title='".$product['name']."' alt='".$product['name']."' /><br />";
			}
			$output .= $product['description']."<br />";
		
			$output .= "<form name='".$product['id']."' method='post' action='' >";
			$variations_processor = new nzshpcrt_variations;
			$output .= $variations_processor->display_product_variations($product['id']);
			$output .= "<input type='hidden' name='product_id' value='".$product['id']."'/>";
			$output .= "<input type='hidden' name='item' value='".$product['id']."' />";
			$output .= "<input type='hidden' name='wpsc_ajax_action' value='donations_widget' />";		
			$currency_sign_location = get_option('currency_sign_location');
			$currency_type = get_option('currency_type');
			$currency_symbol = $wpdb->get_var("SELECT `symbol_html` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1") ;
			$output .= "<label for='donation_widget_price_".$product['id']."'>".__('Donation', 'wpsc').":</label> $currency_symbol<input type='text' id='donation_widget_price_".$product['id']."' name='donation_price' value='".number_format($product['price'],2)."' size='6' /><br />"; 
			$output .= "<input type='submit' name='Buy' value='".__('Add To Cart', 'wpsc')."'  />";
			$output .= "</form>";
		}
		$output .= "</div></div>";
	} else {
		$output = '';
	}
	echo $input.$output;
}

function widget_donations_control() { 
  $option_name = 'wpsc-nzshpcrt_donations';  // because I want to only change this to reuse the code.
	$options = $newoptions = get_option($option_name);
	if ( isset($_POST[$option_name]) ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST[$option_name]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option($option_name, $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Title:')."<input class='widefat' id='{$option_name}' name='{$option_name}' type='text' value='{$title}' /></label>\n\r";
	echo "</p>\n\r";
}

function widget_donations_init() {
  if(function_exists('register_sidebar_widget')) {
    register_sidebar_widget(__('Product Donations', 'wpsc'), 'widget_donations');
    register_widget_control(__('Product Donations', 'wpsc'), 'widget_donations_control');
	}
  return;
}

add_action('plugins_loaded', 'widget_donations_init');
?>