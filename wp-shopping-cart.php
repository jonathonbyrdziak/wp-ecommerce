<?php
/*
Plugin Name: WP e-Commerce Plugin
Plugin URI: http://getshopped.org
Description: A plugin that provides a WordPress Shopping Cart. See also: <a href="http://getshopped.org" target="_blank">GetShopped.org</a> | <a href="http://getshopped.org/forums/" target="_blank">Support Forum</a> | <a href="http://getshopped.org/resources/docs/" target="_blank">Documentation</a>
Version: 3.7.6.9
Author: Instinct Entertainment
Author URI: http://getshopped.org
*/
/**
 * WP e-Commerce Main Plugin File
 * @package wp-e-commerce
*/
// this is to make sure it sets up the table name constants correctly on activation
global $wpdb;
if ( function_exists('memory_get_usage') && ( (int) @ini_get('memory_limit') < 64 ) ) @ini_set('memory_limit', '64M');
define('WPSC_VERSION', '3.7');
define('WPSC_MINOR_VERSION', '57');

define('WPSC_PRESENTABLE_VERSION', '3.7.6.9');

define('WPSC_DEBUG', false);
define('WPSC_GATEWAY_DEBUG', false);

$v1 = str_replace(array('_','-','+'), '.', strtolower($wp_version));
$v1 = str_replace(array('alpha','beta','gamma'), array('a','b','g'), $v1);
$v1 = preg_split("/([a-z]+)/i",$v1,-1, PREG_SPLIT_DELIM_CAPTURE);
array_walk($v1, create_function('&$v', '$v = trim($v,". ");'));

define('IS_WP25', version_compare($v1[0], '2.5', '>='));
define('IS_WP27', version_compare($v1[0], '2.7', '>='));

// // we need to know where we are, rather than assuming where we are

//Define the path to the plugin folder
define('WPSC_FILE_PATH', dirname(__FILE__));
define('WPSC_DIR_NAME', basename(WPSC_FILE_PATH));

$wpsc_siteurl = get_option('siteurl');
if(is_ssl()) {
	$wpsc_siteurl = str_replace("http://", "https://", $wpsc_siteurl);
}

$wpsc_plugin_url = WP_CONTENT_URL;
if(is_ssl()) {
  $plugin_url_parts = parse_url($wpsc_plugin_url);
  $site_url_parts = parse_url($wpsc_siteurl);
  if(stristr($plugin_url_parts['host'], $site_url_parts['host']) && stristr($site_url_parts['host'], $plugin_url_parts['host'])) {
		$wpsc_plugin_url = str_replace("http://", "https://", $wpsc_plugin_url);
	}
}


//Define the URL to the plugin folder
define('WPSC_FOLDER', dirname(plugin_basename(__FILE__)));
define('WPSC_URL', $wpsc_plugin_url.'/plugins/'.WPSC_FOLDER);

if(isset($wpdb->blogid)) {
   define('IS_WPMU', 1);
} else {
	define('IS_WPMU', 0);
}

/* 
  load plugin text domain for get_text files. Plugin language will be the same 
  as wordpress language defined in wp-config.php line 67
*/
load_plugin_textdomain('wpsc', false, dirname( plugin_basename(__FILE__) ) . '/languages');



if(!empty($wpdb->prefix)) {
  $wp_table_prefix = $wpdb->prefix;
} else if(!empty($table_prefix)) {
  $wp_table_prefix = $table_prefix;
}

// Define the database table names
define('WPSC_TABLE_CATEGORY_TM', "{$wp_table_prefix}wpsc_category_tm");
define('WPSC_TABLE_ALSO_BOUGHT', "{$wp_table_prefix}wpsc_also_bought");
define('WPSC_TABLE_CART_CONTENTS', "{$wp_table_prefix}wpsc_cart_contents");
define('WPSC_TABLE_META', "{$wp_table_prefix}wpsc_meta");
define('WPSC_TABLE_CART_ITEM_VARIATIONS', "{$wp_table_prefix}wpsc_cart_item_variations");
define('WPSC_TABLE_CHECKOUT_FORMS', "{$wp_table_prefix}wpsc_checkout_forms");
define('WPSC_TABLE_CURRENCY_LIST', "{$wp_table_prefix}wpsc_currency_list");
define('WPSC_TABLE_DOWNLOAD_STATUS', "{$wp_table_prefix}wpsc_download_status");
define('WPSC_TABLE_ITEM_CATEGORY_ASSOC', "{$wp_table_prefix}wpsc_item_category_assoc");
define('WPSC_TABLE_PRODUCT_CATEGORIES', "{$wp_table_prefix}wpsc_product_categories");
define('WPSC_TABLE_PRODUCT_FILES', "{$wp_table_prefix}wpsc_product_files");
define('WPSC_TABLE_PRODUCT_IMAGES', "{$wp_table_prefix}wpsc_product_images");
define('WPSC_TABLE_PRODUCT_LIST', "{$wp_table_prefix}wpsc_product_list");
define('WPSC_TABLE_PRODUCT_ORDER', "{$wp_table_prefix}wpsc_product_order");
define('WPSC_TABLE_PRODUCT_RATING', "{$wp_table_prefix}wpsc_product_rating");
define('WPSC_TABLE_PRODUCT_VARIATIONS', "{$wp_table_prefix}wpsc_product_variations");
define('WPSC_TABLE_PURCHASE_LOGS', "{$wp_table_prefix}wpsc_purchase_logs");
define('WPSC_TABLE_PURCHASE_STATUSES', "{$wp_table_prefix}wpsc_purchase_statuses");
define('WPSC_TABLE_REGION_TAX', "{$wp_table_prefix}wpsc_region_tax");
define('WPSC_TABLE_SUBMITED_FORM_DATA', "{$wp_table_prefix}wpsc_submited_form_data");
define('WPSC_TABLE_VARIATION_ASSOC', "{$wp_table_prefix}wpsc_variation_assoc");
define('WPSC_TABLE_VARIATION_PROPERTIES', "{$wp_table_prefix}wpsc_variation_properties");
define('WPSC_TABLE_VARIATION_VALUES', "{$wp_table_prefix}wpsc_variation_values");
define('WPSC_TABLE_VARIATION_VALUES_ASSOC', "{$wp_table_prefix}wpsc_variation_values_assoc");
define('WPSC_TABLE_COUPON_CODES', "{$wp_table_prefix}wpsc_coupon_codes");
define('WPSC_TABLE_LOGGED_SUBSCRIPTIONS', "{$wp_table_prefix}wpsc_logged_subscriptions");
define('WPSC_TABLE_PRODUCTMETA', "{$wp_table_prefix}wpsc_productmeta");
define('WPSC_TABLE_CATEGORISATION_GROUPS', "{$wp_table_prefix}wpsc_categorisation_groups");
define('WPSC_TABLE_VARIATION_COMBINATIONS', "{$wp_table_prefix}wpsc_variation_combinations");
define('WPSC_TABLE_CLAIMED_STOCK', "{$wp_table_prefix}wpsc_claimed_stock");


// start including the rest of the plugin here
require_once(WPSC_FILE_PATH.'/wpsc-includes/wpsc_query.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/variations.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/ajax.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/misc.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/mimetype.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/cart.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/checkout.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/display.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/pagination.class.php');

require_once(WPSC_FILE_PATH.'/wpsc-includes/shortcode.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/coupons.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/purchaselogs.class.php');
include_once(WPSC_FILE_PATH."/wpsc-includes/category.functions.php");
include_once(WPSC_FILE_PATH."/wpsc-includes/processing.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/form-display.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/merchant.class.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/meta.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/productfeed.php");

require_once(WPSC_FILE_PATH.'/wpsc-includes/theme.functions.php');

//Add deprecated functions in this file please
require_once(WPSC_FILE_PATH."/wpsc-includes/deprecated.functions.php");


//exit(print_r($v1,true));
if($v1[0] >= 2.8){
	require_once(WPSC_FILE_PATH."/wpsc-includes/upgrades.php");
}

if (!IS_WP25) {
	require_once(WPSC_FILE_PATH.'/editor.php');
} else { 
	require_once(WPSC_FILE_PATH.'/js/tinymce3/tinymce.php');
}

if((get_option('wpsc_share_this') == 1) && (get_option('product_list_url') != '')) {
  if(stristr(("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), get_option('product_list_url'))){
    include_once(WPSC_FILE_PATH."/share-this.php");
  }
}

$wpsc_currency_data = array();
$wpsc_title_data = array();
$GLOBALS['nzshpcrt_imagesize_info'] = __('Note: if this is blank, the image will not be resized', 'wpsc');
$nzshpcrt_log_states[0]['name'] = __('Order Received', 'wpsc');
$nzshpcrt_log_states[1]['name'] = TXT_WPSC_PROCESSING;
$nzshpcrt_log_states[2]['name'] = __('Closed Order', 'wpsc');


require_once(WPSC_FILE_PATH."/currency_converter.inc.php"); 
require_once(WPSC_FILE_PATH."/shopping_cart_functions.php"); 
require_once(WPSC_FILE_PATH."/homepage_products_functions.php"); 
require_once(WPSC_FILE_PATH."/transaction_result_functions.php"); 
// include_once(WPSC_FILE_PATH.'/submit_checkout_function.php');
require_once(WPSC_FILE_PATH."/admin-form-functions.php");
require_once(WPSC_FILE_PATH."/shipwire_functions.php"); 

/* widget_section */
include_once(WPSC_FILE_PATH.'/widgets/product_tag_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/shopping_cart_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/donations_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/specials_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/latest_product_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/price_range_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/admin_menu_widget.php');
//include_once(WPSC_FILE_PATH.'/widgets/api_key_widget.php');
 if (class_exists('WP_Widget')) {
	include_once(WPSC_FILE_PATH.'/widgets/category_widget.28.php');
} else {
	include_once(WPSC_FILE_PATH.'/widgets/category_widget.27.php');
}


include_once(WPSC_FILE_PATH.'/image_processing.php');


// if we are in the admin section, include the admin code
if(WP_ADMIN == true) {
	require_once(WPSC_FILE_PATH."/wpsc-admin/admin.php");
}


/**
* Code to define where the uploaded files are stored starts here
*/
/*
if(IS_WPMU == 1) {
		$upload_url = get_option('siteurl').'/files';
		$upload_path = ABSPATH.get_option('upload_path');
} else {
	if ( !defined('WP_CONTENT_URL') ) {
		define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	}
	if ( !defined('WP_CONTENT_DIR') ) {
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content');
	}
	
	$upload_path = WP_CONTENT_DIR."/uploads";
	$upload_url = WP_CONTENT_URL."/uploads";
}*/



$wp_upload_dir_data = wp_upload_dir();
//echo "<pre>".print_r($wp_upload_dir_data, true)."</pre>";
$upload_path = $wp_upload_dir_data['basedir'];
$upload_url = $wp_upload_dir_data['baseurl'];

if(is_ssl()) {
	 $upload_url = str_replace("http://", "https://", $upload_url);
}
	
$wpsc_upload_dir = "{$upload_path}/wpsc/";
$wpsc_file_dir = "{$wpsc_upload_dir}downloadables/";
$wpsc_preview_dir = "{$wpsc_upload_dir}previews/";
$wpsc_image_dir = "{$wpsc_upload_dir}product_images/";
$wpsc_thumbnail_dir = "{$wpsc_upload_dir}product_images/thumbnails/";
$wpsc_category_dir = "{$wpsc_upload_dir}category_images/";
$wpsc_user_uploads_dir = "{$wpsc_upload_dir}user_uploads/";
$wpsc_cache_dir = "{$wpsc_upload_dir}cache/";
$wpsc_upgrades_dir = "{$wpsc_upload_dir}upgrades/";
$wpsc_themes_dir = "{$wpsc_upload_dir}themes/";

define('WPSC_UPLOAD_DIR', $wpsc_upload_dir);
define('WPSC_FILE_DIR', $wpsc_file_dir);
define('WPSC_PREVIEW_DIR', $wpsc_preview_dir);
define('WPSC_IMAGE_DIR', $wpsc_image_dir);
define('WPSC_THUMBNAIL_DIR', $wpsc_thumbnail_dir);
define('WPSC_CATEGORY_DIR', $wpsc_category_dir);
define('WPSC_USER_UPLOADS_DIR', $wpsc_user_uploads_dir);
define('WPSC_CACHE_DIR', $wpsc_cache_dir);
define('WPSC_UPGRADES_DIR', $wpsc_upgrades_dir);
define('WPSC_THEMES_PATH', $wpsc_themes_dir);


/**
* files that are uploaded as part of digital products are not directly downloaded, therefore there is no need for a URL constant for them
*/
$wpsc_upload_url = "{$upload_url}/wpsc/";
$wpsc_preview_url = "{$wpsc_upload_url}previews/";
$wpsc_image_url = "{$wpsc_upload_url}product_images/";
$wpsc_thumbnail_url = "{$wpsc_upload_url}product_images/thumbnails/";
$wpsc_category_url = "{$wpsc_upload_url}category_images/";
$wpsc_user_uploads_url = "{$wpsc_upload_url}user_uploads/";
$wpsc_cache_url = "{$wpsc_upload_url}cache/";
$wpsc_upgrades_url = "{$wpsc_upload_url}upgrades/";
$wpsc_themes_url = "{$wpsc_upload_url}themes/";

define('WPSC_UPLOAD_URL', $wpsc_upload_url);
define('WPSC_PREVIEW_URL', $wpsc_preview_url);
define('WPSC_IMAGE_URL', $wpsc_image_url);
define('WPSC_THUMBNAIL_URL', $wpsc_thumbnail_url);
define('WPSC_CATEGORY_URL', $wpsc_category_url);
define('WPSC_USER_UPLOADS_URL', $wpsc_user_uploads_url);
define('WPSC_CACHE_URL', $wpsc_cache_url);
define('WPSC_UPGRADES_URL', $wpsc_upgrades_url);

define('WPSC_THEMES_URL', $wpsc_themes_url);

/* 
 * This plugin gets the merchants from the merchants directory and
 * needs to search the merchants directory for merchants, the code to do this starts here
 */
$gateway_directory = WPSC_FILE_PATH.'/merchants';
$nzshpcrt_merchant_list = wpsc_list_dir($gateway_directory);

$num=0;
foreach((array)$nzshpcrt_merchant_list as $nzshpcrt_merchant) {
  if(stristr( $nzshpcrt_merchant , '.php' )) {
    //echo $nzshpcrt_merchant;
    require(WPSC_FILE_PATH."/merchants/".$nzshpcrt_merchant);
	}
  $num++;
}

$nzshpcrt_gateways = apply_filters('wpsc_gateway_modules',$nzshpcrt_gateways);
/* 
 * and ends here
 */
 
// include shipping modules here.
$shipping_directory = WPSC_FILE_PATH.'/shipping';
$nzshpcrt_shipping_list = wpsc_list_dir($shipping_directory);
foreach((array)$nzshpcrt_shipping_list as $nzshpcrt_shipping) {
	if(stristr( $nzshpcrt_shipping , '.php' )) {
		if($nzshpcrt_shipping == 'ups.php'){
			if (phpMinV('5')){
			require(WPSC_FILE_PATH."/shipping/".$nzshpcrt_shipping);
			}
		}else{
			require(WPSC_FILE_PATH."/shipping/".$nzshpcrt_shipping);
		}
	}
}
$wpsc_shipping_modules = apply_filters('wpsc_shipping_modules',$wpsc_shipping_modules);
// if the gold cart file is present, include it, this must be done before the admin file is included
if(is_file(WPSC_UPGRADES_DIR . "gold_cart_files/gold_shopping_cart.php")) {
  require_once(WPSC_UPGRADES_DIR . "gold_cart_files/gold_shopping_cart.php");
}

// need to sort the merchants here, after the gold ones are included. 
if(!function_exists('wpsc_merchant_sort')) {
	function wpsc_merchant_sort($a, $b) { 
		return strnatcmp(strtolower($a['name']), strtolower($b['name']));
	}
}
uasort($nzshpcrt_gateways, 'wpsc_merchant_sort');

// make an associative array of references to gateway data.
$wpsc_gateways = array(); 
foreach((array)$nzshpcrt_gateways as $key => $gateway) {
	$wpsc_gateways[$gateway['internalname']] = &$nzshpcrt_gateways[$key];
}

$theme_path = WPSC_FILE_PATH . '/themes/';
if((get_option('wpsc_selected_theme') != '') && (file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".php") )) {    
  include_once(WPSC_FILE_PATH.'/themes/'.get_option('wpsc_selected_theme').'/'.get_option('wpsc_selected_theme').'.php');
}
$current_version_number = get_option('wpsc_version');
if(count(explode(".",$current_version_number)) > 2) {
	// in a previous version, I accidentally had the major version number have two dots, and three numbers
	// this code rectifies that mistake
	$current_version_number_array = explode(".",$current_version_number);
	array_pop($current_version_number_array);
	$current_version_number = (float)implode(".", $current_version_number_array );
} else if(!is_numeric(get_option('wpsc_version'))) {
  $current_version_number = 0;
}


//if there are any upgrades present, include them., thanks to nielo.info and lsdev.biz
if($v1[0] >= 2.8){
	$upgrades = get_upgrades();
	foreach ($upgrades as $path=>$upgrade) {
		$upgrade_file = WPSC_UPGRADES_DIR . '/' . $path;
		require_once($upgrade_file);
	}
}


include_once(WPSC_FILE_PATH."/wpsc-includes/install_and_update.functions.php");
register_activation_hook(__FILE__, 'wpsc_install');


/**
* Code to define where the uploaded files are stored ends here
*/

if(!function_exists('wpsc_start_the_query')) {
	function wpsc_start_the_query() {
	  global $wp_query, $wpsc_query;
	  $wpsc_query = new WPSC_query();
	
		$post_id = $wp_query->post->ID;
		$page_url = get_permalink($post_id);
		if(get_option('shopping_cart_url') == $page_url) {
			$_SESSION['wpsc_has_been_to_checkout'] = true;
			//echo $_SESSION['wpsc_has_been_to_checkout'];
		}

	}
}

// after init and after when the wp query string is parsed but before anything is displayed
add_action('template_redirect', 'wpsc_start_the_query', 0);


/**
 * Check to see if the session exists, if not, start it
 */
if((!is_array($_SESSION)) xor (!isset($_SESSION['nzshpcrt_cart'])) xor (!$_SESSION)) {
  session_start();
}
if(!function_exists('wpsc_initialisation')){
	function wpsc_initialisation() {
	  global $wpsc_cart,  $wpsc_theme_path, $wpsc_theme_url, $wpsc_category_url_cache;
	  // set the theme directory constant
	  
		$file_names = array();
		
		// Check that theme folder exists to prevent fatal timeout error
		// which crashes WordPress.
		if ( is_dir( WPSC_THEMES_PATH ) ) {
			$uploads_dir = @opendir( WPSC_THEMES_PATH );
			while ( ( $file = @readdir( $uploads_dir ) ) !== false ) {
				//echo "<br />test" . WPSC_THEMES_PATH . $file;
				if ( is_dir( WPSC_THEMES_PATH . $file ) && ( $file != "..") && ( $file != "." ) && ( $file != ".svn" ) ) {
					$file_names[] = $file;
				}
			}
		}
		
	  if(count($file_names) > 0) {
			$wpsc_theme_path = WPSC_THEMES_PATH;
			$wpsc_theme_url = WPSC_THEMES_URL;
	  } else {
			$wpsc_theme_path = WPSC_FILE_PATH . "/themes/";
			$wpsc_theme_url = WPSC_URL. '/themes/';
	  }
	  //$theme_path = WPSC_FILE_PATH . "/themes/";
	  //exit(print_r($file_names,true));
		if((get_option('wpsc_selected_theme') == null) || (!file_exists($wpsc_theme_path.get_option('wpsc_selected_theme')))) {
			$theme_dir = 'default';
		} else {
			$theme_dir = get_option('wpsc_selected_theme');
		}
		define('WPSC_THEME_DIR', $theme_dir);
	  
	  // initialise the cart session, if it exist, unserialize it, otherwise make it
		if(isset($_SESSION['wpsc_cart'])) {
			if(is_object($_SESSION['wpsc_cart'])) {
				$GLOBALS['wpsc_cart'] = $_SESSION['wpsc_cart'];
			} else {
				$GLOBALS['wpsc_cart'] = unserialize($_SESSION['wpsc_cart']);
			}
			if(!is_object($GLOBALS['wpsc_cart']) || (get_class($GLOBALS['wpsc_cart']) != "wpsc_cart")) {
				$GLOBALS['wpsc_cart'] = new wpsc_cart;
			}
		} else {
			$GLOBALS['wpsc_cart'] = new wpsc_cart;
		}
	}
	$GLOBALS['wpsc_category_url_cache'] = get_option('wpsc_category_url_cache');
	//register_taxonomy('product_tag', 'product');
}
// first plugin hook in wordpress
add_action('plugins_loaded','wpsc_initialisation', 0);



function wpsc_register_post_types() {
	register_taxonomy('product_tag', 'wpsc-product');
}
add_action( 'init', 'wpsc_register_post_types', 8 ); // highest priority


switch(get_option('cart_location')) {
  case 1:
  add_action('wp_list_pages','nzshpcrt_shopping_basket');
  break;
  
  case 2:
  add_action('the_content', 'nzshpcrt_shopping_basket' , 14);
  break;
  
  default:
  break;
}
add_action('plugins_loaded', 'widget_wp_shopping_cart_init', 10);


// refresh page urls when permalinks are turned on or altered
add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');




if(is_ssl()) {
	function wpsc_add_https_to_page_url_options($url) {
		return str_replace("http://", "https://", $url);
	}
	add_filter('option_product_list_url', 'wpsc_add_https_to_page_url_options');
	add_filter('option_shopping_cart_url', 'wpsc_add_https_to_page_url_options');
	add_filter('option_transact_url', 'wpsc_add_https_to_page_url_options');
	add_filter('option_user_account_url', 'wpsc_add_https_to_page_url_options');
}



/**
 * This serializes the shopping cart variable as a backup in case the unserialized one gets butchered by various things
 */  
if(!function_exists('wpsc_serialize_shopping_cart')){
	function wpsc_serialize_shopping_cart() {
	  global $wpdb, $wpsc_start_time, $wpsc_cart, $wpsc_category_url_cache;
	  if(is_object($wpsc_cart)) {
			$wpsc_cart->errors = array();
	  }
	  $_SESSION['wpsc_cart'] = serialize($wpsc_cart);

		$previous_category_url_cache = get_option('wpsc_category_url_cache');
		if($wpsc_category_url_cache != $previous_category_url_cache) {
			update_option('wpsc_category_url_cache', $wpsc_category_url_cache);
		}
	  
	  return true;
	} 
} 
add_action('shutdown','wpsc_serialize_shopping_cart');


function wpsc_break_canonical_redirects($redirect_url, $requested_url) {
	global $wp_query;
	
	if(is_numeric($wp_query->query_vars['category_id'] )) {
		return false;
	}
	return $redirect_url;

}

add_filter('redirect_canonical', 'wpsc_break_canonical_redirects', 10, 2);



/**
 * Update Notice
 *
 * Displays an update message below the auto-upgrade link in the WordPress admin
 * to notify users that they should check the upgrade information and changelog
 * before upgrading in case they need to may updates to their theme files.
 *
 * @package wp-e-commerce
 * @since 3.7.6.1
 */
function wpsc_update_notice() {
	$info_title = __( 'Please Note', 'wpsc' );
	$info_text = sprintf( __( 'Before upgrading you should check the <a %s>upgrade information</a> and changelog as you may need to make updates to your template files.', 'wpsc' ), 'href="http://getshopped.org/resources/docs/upgrades/staying-current/" target="_blank"' );
	echo '<div style="border-top:1px solid #CCC; margin-top:3px; padding-top:3px; font-weight:normal;"><strong style="color:#CC0000">' . strip_tags( $info_title ) . '</strong>: ' . strip_tags( $info_text, '<br><a><strong><em><span>' ) . '</div>';
}

if ( is_admin() ) {
	add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), 'wpsc_update_notice' );
}


?>