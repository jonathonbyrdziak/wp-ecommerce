<?php

function widget_latest_products($args) {
	global $wpdb, $table_prefix;
	extract($args);
  $options = get_option('wpsc-widget_latest_products');   
	$title = empty($options['title']) ? __(__('Latest Products', 'wpsc')) : $options['title'];
	echo $before_widget."<br />";
	$full_title = $before_title . $title . $after_title;
	echo $full_title."<br />";
	
	nzshpcrt_latest_product();
	echo $after_widget;
}
 
function nzshpcrt_latest_product($input = null) {
	global $wpdb;
	$image_width = get_option('product_image_width');
	$image_height = get_option('product_image_height');
	$siteurl = get_option('siteurl');
	$options = get_option("wpsc-widget_latest_products");
	$number = ($options["number"]==0)?5:$options["number"];
	$latest_product = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1') AND `publish` IN ('1') ORDER BY `id` DESC LIMIT ".$number, ARRAY_A);
	if($latest_product != null) {
		$output = "<div>";
		foreach($latest_product as $special) {			
			$output.="<div>";
			$output .= "	<div class='item_image'>";
 			$output.="			<a href='".wpsc_product_url($special['id'],$special['category'])."'>";
			if(($special['image'] > 0)) {
				if(get_option('wpsc_selected_theme') == 'marketplace') {
					$src = WPSC_IMAGE_URL.$special['image'];
							
					$output .= "				<img src='". "index.php?image_id={$special['image']}&amp;width=".$image_width."&amp;height=".$image_height."' title='".$special['name']."' alt='".$special['name']."' />";
					
				} else {
					$output .= "				<img src='". "index.php?image_id={$special['image']}&amp;width=".$image_width."&amp;height=".$image_height."' title='".$special['name']."' alt='".$special['name']."' /><br />";
				}
			} else {
				//$output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$special['name']."' alt='".$special['name']."' /><br />";
			}
			
 			$output.="		</a>";
			$output .= "	</div>";
			
 			$output.="	<a href='".wpsc_product_url($special['id'],$special['category'])."'>";
			$output .= "		<strong>".stripslashes($special['name'])."</strong><br />";
			
			$output .= "	</a>";
			$output .= "</div>";
		}
		$output .= "</div>";
	} else {
		$output = '';
	}
	echo $input.$output;
}

function widget_latest_products_control() {
  $option_name = 'wpsc-widget_latest_products';  // because I want to only change this to reuse the code.
	$options = $newoptions = get_option($option_name);
	if ( isset($_POST[$option_name]) ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST[$option_name]));
		$newoptions['number'] = (absint($_POST['wpsc_lpwn']) == 0)? 5:absint($_POST['wpsc_lpwn']);

	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option($option_name, $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Title:')."<input class='widefat' id='{$option_name}' name='{$option_name}' type='text' value='{$title}' /></label>\n\r";
	echo "  <label for='wpsc_lpwn'>"._e('Number of products to show:')."
			<select id='wpsc_lpwn' name='wpsc_lpwn'>";
	for($i = 1; $i <= 30; $i++){
		$selected=''; if ($i==$options["number"]) $selected=" SELECTED ";  echo "<option".$selected." value='".$i."'>".$i."</option>";
		}
	echo "	</select>
			</label>\n\r";

	echo "</p>\n\r";
}

function widget_latest_products_init() {
	if(function_exists('register_sidebar_widget')) {
		register_sidebar_widget(__('Latest Products', 'wpsc'), 'widget_latest_products');
		register_widget_control(__('Latest Products', 'wpsc'), 'widget_latest_products_control');
	}
	return;
}
 add_action('plugins_loaded', 'widget_latest_products_init');
 ?>