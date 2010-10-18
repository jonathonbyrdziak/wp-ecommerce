<?php
function widget_admin_menu($args){
  global $wpdb, $table_prefix, $current_user;
	get_currentuserinfo();
  if($current_user->wp_capabilities['administrator'] == 1) {
		extract($args);
		//$options = get_option('widget_wp_shopping_cart');
		$title = empty($options['title']) ? __(__('Admin Menu', 'wpsc')) : $options['title'];
		echo $before_widget;
		$full_title = $before_title . $title . $after_title;
		echo $full_title;
		admin_menu();
		echo $after_widget;
  
  }
}

function widget_admin_menu_control() { return null; }

function widget_admin_menu_init() {
	if(function_exists('register_sidebar_widget')) {
		register_sidebar_widget(__('Admin Menu', 'wpsc'), 'widget_admin_menu');
		#register_widget_control('Admin Menu', 'widget_admin_menu', 300, 90);
	}
	return;
 }
add_action('plugins_loaded', 'widget_admin_menu_init');

function admin_menu() {
	$siteurl = get_option('siteurl');
	echo "<ul id='set1'>";
	echo "<li><a title='People come here to write new pages' href='".$siteurl."/wp-admin/page-new.php'>Add Pages</a></li>";
	echo "<li><a title='People come here to add products' href='".$siteurl."/wp-admin/admin.php?page=wpsc-edit-products'>Add Products</a></li>";
	echo "<li><a title='People come here to change themes and widgets settings' href='".$siteurl."/wp-admin/themes.php'>Presentation</a></li>";
	echo "</ul>";
}
?>