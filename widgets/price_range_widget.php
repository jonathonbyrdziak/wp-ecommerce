<?php
function widget_price_range($args) {
	global $wpdb, $table_prefix;
	extract($args);
  $options = get_option('wpsc-widget_price_range');    
	$title = empty($options['title']) ? __(__('Price Range', 'wpsc')) : $options['title'];
	echo $before_widget."";
	$full_title = $before_title . $title . $after_title;
	echo $full_title."";
	nzshpcrt_price_range();
	echo $after_widget;
}
 
function nzshpcrt_price_range($input = null) {
	global $wpdb;
	$siteurl = get_option('siteurl');
	$product_page=get_option("product_list_url");
	if (stristr($product_page,"?")) {
		$seperater='&';
	} else {
		$seperater='?';
	}
	$result = $wpdb->get_results("SELECT DISTINCT `price` FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE `active` IN ('1') ORDER BY price ASC",ARRAY_A);
	if($result != null) {
		sort($result);
		$count = count($result);
		$price_seperater = ceil($count/6);
		for($i=0;$i<$count;$i+=$price_seperater) {
			$ranges[]=round($result[$i]['price'],-1);
		}
		$ranges = array_unique($ranges);
		
		$final_count = count($ranges);
		$ranges = array_merge(array(), $ranges);
		$_SESSION['price_range']=$ranges;
		
	//	echo('<pre>'.print_r($ranges, true).'</pre>');
		for($i=0;$i<$final_count;$i++) {
			$j=$i;
			
			if ($i==$final_count-1) {
				echo "<a href='".htmlentities($product_page.$seperater."range=".$j)."'>Over ".nzshpcrt_currency_display($ranges[$i],1,true)."</a><br/>";
			} else if($ranges[$i]==0){ 
				echo "<a href='".htmlentities($product_page.$seperater."range=".$j)."'>Under ".nzshpcrt_currency_display($ranges[$i+1],1,true)."</a><br/>";
			}else {
				echo "<a href='".htmlentities($product_page.$seperater."range=".$j)."'>".nzshpcrt_currency_display($ranges[$i],1,true)." - ".nzshpcrt_currency_display($ranges[$i+1],1,true)."</a><br/>";
			}
		}
    	if(get_option('permalink_structure') != '') {
	    	$seperator ="?";
		} else {
			$seperator ="&amp;";
		}
		echo "<a href='".get_option("product_list_url").$seperator."range=all'>".__('Show All', 'wpsc')."</a><br/>";
	}
}

function widget_price_range_control() { 
  $option_name = 'wpsc-widget_price_range';  // because I want to only change this to reuse the code.
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

function widget_price_range_init() {
	if(function_exists('register_sidebar_widget')) {
		register_sidebar_widget(__('Price Range', 'wpsc'), 'widget_price_range');
		register_widget_control(__('Price Range', 'wpsc'), 'widget_price_range_control');
	}
	return;
}
 add_action('plugins_loaded', 'widget_price_range_init');
 ?>