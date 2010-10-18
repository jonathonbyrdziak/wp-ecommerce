<?php
/**
 * WP eCommerce shortcode definitions
 *
 * These are the shortcode definitions for the wp-eCommerce plugin
 *
 * @package wp-e-commerce
 * @since 3.7
*/
/**
 * The WPSC shortcodes
 */

/**
* wpsc products shorttag function
* @return string - html displaying one or more products, derived from wpsc_display_products
*/
function wpsc_products_shorttag($atts) {

	$number_per_page = get_option('use_pagination') ? get_option('wpsc_products_per_page') : 0;
	$query = shortcode_atts(array(
		'product_id' => 0,
		'product_url_name' => null,
		'product_name' => null,
		'category_id' => 0,
		'category_url_name' => null,
		'tag' => null,
		'price' => 0,
		'limit_of_items' => 0,
		'sort_order' => null,
		'number_per_page' => $number_per_page,
		'page' => 0,
	), $atts);

	return wpsc_display_products_page($query);
}
add_shortcode('wpsc_products', 'wpsc_products_shorttag');






?>