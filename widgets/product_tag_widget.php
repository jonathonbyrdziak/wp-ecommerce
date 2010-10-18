<?php
include_once(WPSC_FILE_PATH.'/tagging_functions.php');

function widget_product_tag($args)
  {
  global $wpdb, $table_prefix;
  extract($args);
  $options = get_option('wpsc-widget_product_tag');  
  $title = empty($options['title']) ? __(__('Product Tags', 'wpsc')) : $options['title'];
  echo $before_widget; 
  $full_title = $before_title . $title . $after_title;
  echo $full_title;
  product_tag_cloud();
  echo $after_widget;
  }

function widget_product_tag_control() {
  $option_name = 'wpsc-widget_product_tag';  // because I want to only change this to reuse the code.
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

 function widget_product_tag_init() {
   if(function_exists('register_sidebar_widget')) {
		//$widget_ops = array('classname' => 'widget_pages', 'description' => __( "Your blog's WordPress Pages") );
		//wp_register_sidebar_widget('pages', __('Pages'), 'wp_widget_pages', $widget_ops);
    register_sidebar_widget(__('Product Tags', 'wpsc'), 'widget_product_tag');
    register_widget_control(__('Product Tags', 'wpsc'), 'widget_product_tag_control');
	}
	return;
}
add_action('plugins_loaded', 'widget_product_tag_init');
?>