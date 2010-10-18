<?php
/**
 * Deprecated functions that will be removed at a later date.
 * @package Wp-e-commerce
 * Since 3.7.6rc2
 *
 */

//// This language file is no longer used, but it is still included for
//// users that have old (non gettext) WPEC themes
 if(!wpsc_check_theme_versions()){

	include_once(WPSC_FILE_PATH.'/languages/EN_en.php');
 }



/**
 * Filter: wpsc-purchlogitem-links-start
 *
 * This filter has been deprecated and replaced with one that follows the
 * correct naming conventions with underscores.
 *
 * Since 3.7.6rc2
 */
function wpsc_purchlogitem_links_start_deprecated() {
	
	do_action( 'wpsc-purchlogitem-links-start' );
	
}
add_action( 'wpsc_purchlogitem_links_start', 'wpsc_purchlogitem_links_start_deprecated' );


/**
 * Filter: end-wpec-settings-shipping-general
 *
 * This filter has been deprecated and replaced with wpsc_settings_shipping_general_end that follows the
 * correct naming conventions with underscores.
 *
 * Since 3.7.6.4
 */
function wpsc_end_wpec_settings_shipping_general() {
	
	do_action( 'end-wpec-settings-shipping-general' );
	
}
add_action( 'wpsc_settings_shipping_general_end', 'wpsc_end_wpec_settings_shipping_general' );

/**
 * Filter: wpsc-empty-cart
 *
 * This filter has been deprecated and replaced with wpsc_empty_cart that follows the
 * correct naming conventions with underscores.
 *
 * Since 3.7.6.4
 */
function wpsc_empty_cart_filter($cart) {
	
	do_action( 'wpsc-empty-cart', $cart );
	
}
add_action( 'wpsc_empty_cart', 'wpsc_empty_cart_filter' );

?>