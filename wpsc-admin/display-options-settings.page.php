<?php
/*
 * Display Settings page
 */
 
 // clear the previously selected shipping form session variable if you are not on the shipping page
 if($_GET['tab'] != 'shipping') {
   $_SESSION['previous_shipping_name'] = '';
 }

 
 
function wpsc_display_settings_page(){
?>
 <div id="wpsc_options" class="wrap">
	<?php if(wpsc_check_theme_versions()){ ?>
		<div class="updated fade below-h2" id="message" style="background-color: rgb(255, 251, 204);">
			<p><?php _e("It looks like your wp-e-commerce theme files are out of date, this can cause potential problems with some gateways and the appearances of the front end of your shop. <br /><strong>We recommend you download the latest version of wp-e-commerce unzip it, and copy the new themes folder into your 'uploads/wpsc/themes' folder on your site. Or port your changes across.</strong>", "wpsc"); ?></p>
		</div>
	<?php
}
 wpsc_the_settings_tabs(); 
if(isset($_GET['tab'])){
	$page = $_GET['tab'];
}else{
	$page = 'general';
}
if(preg_match("/[a-zA-Z]{2,4}/",$_GET['isocode'])) {
		include(WPSC_FILE_PATH.'/tax_and_shipping.php');
		return;
}
if (isset($_GET['googlecheckoutshipping'])) {
	include(WPSC_FILE_PATH.'/google_shipping_country.php');
	return;
	exit();
}

if(isset($_GET['selected_all'])){
	wpsc_submit_options($_GET['selected_all']);
}
if($_SESSION['wpsc_thumbnails_resized'] == true) {
	?>
	<div class="updated fade below-h2" id="message" style="background-color: rgb(255, 251, 204);">
		<p><?php _e("Thanks, your thumbnail images have been resized."); ?></p>
	</div>
	<?php
	$_SESSION['wpsc_thumbnails_resized'] = false;
}
?> <div id='wpsc_options_page'> <?php
switch($page) {
	case "checkout";
	require_once('includes/settings-pages/checkout.php');
	wpsc_options_checkout();
	break;
	case "gateway";
	require_once('includes/settings-pages/gateway.php');
	wpsc_options_gateway();
	break;
	case "shipping";
	require_once('includes/settings-pages/shipping.php');
	wpsc_options_shipping();
	break;
	case "admin";
	require_once('includes/settings-pages/admin.php');
	wpsc_options_admin();
	break;
	
	case "presentation";
	require_once('includes/settings-pages/presentation.php');
	wpsc_options_presentation();
	break;
	
	case "import";
	require_once('includes/settings-pages/import.php');
	wpsc_options_import();
	break;
	
	default;
	case "general";
	require_once('includes/settings-pages/general.php');
	wpsc_options_general();
	break;
}
 $_SESSION['wpsc_settings_curr_page'] = $page;
 ?>
</div>
</div>
<?php
}




/*
 * Create settings page tabs 
 */
function wpsc_settings_tabs() {
	$_default_tabs = array(
		'general' => __('General', 'wpsc'), // handler action suffix => tab text
		'presentation' => __('Presentation', 'wpsc'),
		'admin' => __('Admin', 'wpsc'),
		'shipping' => __('Shipping', 'wpsc'),
		'gateway' => __('Payment Options', 'wpsc'),
		'import' => __('Import', 'wpsc'),
		'checkout' => __('Checkout', 'wpsc')
	);

	return apply_filters('wpsc_settings_tabs', $_default_tabs);
}
/*
 * Display settings tabs
 */
function wpsc_the_settings_tabs(){
global $redir_tab;
	$tabs = wpsc_settings_tabs();

	if ( !empty($tabs) ) {
		echo '<div id="wpsc_settings_nav_bar" style="width:100%;">';
		echo "<ul id='sidemenu' >\n";
		if ( isset($redir_tab) && array_key_exists($redir_tab, $tabs) )
			$current = $redir_tab;
		elseif ( isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) )
			$current = $_GET['tab'];
		else {
			$keys = array_keys($tabs);
			$current = array_shift($keys);
		}
		foreach ( $tabs as $callback => $text ) {
			$class = '';
			if ( $current == $callback ) {
				$class = " class='current'";
				}
			$href = add_query_arg(array('tab'=>$callback, 's'=>false, 'paged'=>false, 'post_mime_type'=>false, 'm'=>false));
			$href = remove_query_arg('isocode', $href);
			$href = wp_nonce_url($href, "tab-$callback");
			$link = "<a href='" . clean_url($href) . "'$class>$text</a>";
			echo "\t<li id='" . attribute_escape("tab-$callback") . "'>$link</li>\n";
		}
		//echo "<li id='tab-spacer' ><a href='' alt='' style='width:33.4%;float:right;'>&nbsp;</a></li>";
		echo "</ul>\n";
		//echo "<div style='float:left;border:1px solid #DFDFDF;'></div>";
		
		echo '</div>';
		echo "<div style='clear:both;'></div>";
	}

}
function country_list($selected_country = null) {
      global $wpdb;
      $output = "";
      $output .= "<option value=''></option>";
      $country_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
      foreach ((array)$country_data as $country) {
        $selected ='';
        if($selected_country == $country['isocode']) {
          $selected = "selected='selected'";
				}
        $output .= "<option value='".$country['isocode']."' $selected>".htmlspecialchars($country['country'])."</option>";
			}
      return $output;
}



/*
 * Get Shipping Form for wp-admin 
 */
function wpsc_get_shipping_form($shippingname) {
  global $wpdb, $wpsc_shipping_modules;
  if(array_key_exists($shippingname, $wpsc_shipping_modules)){
		$shipping_forms = $wpsc_shipping_modules[$shippingname]->getForm();
		$shipping_module_name = $wpsc_shipping_modules[$shippingname]->getName();
		
		$output = array('name' => $shipping_module_name, 'form_fields' => $shipping_forms, 'has_submit_button' => 1);
  } else {
		$output = array('name' => '&nbsp;', 'form_fields' => __('To configure a shipping module select one on the left.', 'wpsc'), 'has_submit_button' => 0);
  }
  return $output;
}


function wpsc_settings_page_update_notification(){
if (isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) ||  isset($_GET['shipadd']) ) { ?>
			<div id="message" class="updated fade"><p>
			<?php if ( isset($_GET['updated']) && (int) $_GET['updated'] ) {
				printf( __ngettext( ' Setting Options Updated. ' , ' %s Settings Options Updated. ', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
				unset($_GET['updated']);
			}
						
			if ( isset($_GET['deleted']) && (int) $_GET['deleted'] ) {
				printf( __ngettext( '%s Setting Option deleted. ', '%s Setting Option deleted. ', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
				unset($_GET['deleted']);
			}
			if ( isset($_GET['shipadd']) && (int) $_GET['shipadd'] ) {
				printf( __ngettext( ' Shipping Option Updated.', ' Shipping Option Updated.', $_GET['shipadd'] ), number_format_i18n( $_GET['shipadd'] ) );
				unset($_GET['shipadd']);
			}
			if ( isset($_GET['added']) && (int) $_GET['added'] ) {
				printf( __ngettext( '%s Checkout Field Added.', '%s Checkout Fields Added.', $_GET['added'] ), number_format_i18n( $_GET['added'] ) );
				unset($_GET['added']);
			}

		
			$_SERVER['REQUEST_URI'] = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted','wpsc_downloadcsv','rss_key','start_timestamp','end_timestamp','email_buyer_id'), $_SERVER['REQUEST_URI'] );
			?>
			</p></div>
<?php

}
}

?>