<?php
/**
 * Some parts of this code were copied from functions.bb-meta.php in bbpress 
 */
 
function wpsc_sanitize_meta_key( $key )
{
	return preg_replace( '|[^a-z0-9_]|i', '', $key );
}


/**
 * Gets meta data from the database
 * This needs caching implemented for it, but I have not yet figured out how to make this work for it
 * @internal
 */
function wpsc_get_meta( $object_id = 0, $meta_key, $type) {
	global $wpdb;
	$cache_object_id = $object_id = (int) $object_id;
	$object_type = $type;
	$value = wp_cache_get( $cache_object_id, $object_type );

	$meta_key = wpsc_sanitize_meta_key( $meta_key );

	$meta_tuple = compact( 'object_type', 'object_id', 'meta_key', 'meta_value', 'type' );
	$meta_tuple = apply_filters( 'wpsc_update_meta', $meta_tuple );
	extract( $meta_tuple, EXTR_OVERWRITE );

	$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT `meta_value` FROM `".WPSC_TABLE_META."` WHERE `object_type` = %s AND `object_id` = %d AND `meta_key` = %s", $object_type, $object_id, $meta_key ) );

	$meta_value = maybe_unserialize( $meta_value );
	return $meta_value;
}





/**
 * Adds and updates meta data in the database
 *
 * @internal
 */
function wpsc_update_meta( $object_id = 0, $meta_key, $meta_value, $type, $global = false ) {
	global $wpdb;
	if ( !is_numeric( $object_id ) || empty( $object_id ) && !$global ) {
		return false;
	}
	$cache_object_id = $object_id = (int) $object_id;
	
	$object_type = $type;
	
	$meta_key = wpsc_sanitize_meta_key( $meta_key );

	$meta_tuple = compact( 'object_type', 'object_id', 'meta_key', 'meta_value', 'type' );
	$meta_tuple = apply_filters( 'wpsc_update_meta', $meta_tuple );
	extract( $meta_tuple, EXTR_OVERWRITE );

	$meta_value = $_meta_value = maybe_serialize( $meta_value );
	$meta_value = maybe_unserialize( $meta_value );

	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPSC_TABLE_META."` WHERE `object_type` = %s AND `object_id` = %d AND `meta_key` = %s", $object_type, $object_id, $meta_key ) );
	if ( !$cur ) {
		$wpdb->insert( WPSC_TABLE_META, array( 'object_type' => $object_type, 'object_id' => $object_id, 'meta_key' => $meta_key, 'meta_value' => $_meta_value ) );
	} elseif ( $cur->meta_value != $meta_value ) {
		$wpdb->update( WPSC_TABLE_META, array( 'meta_value' => $_meta_value), array( 'object_type' => $object_type, 'object_id' => $object_id, 'meta_key' => $meta_key ) );
	}
	wp_cache_delete( $cache_object_id, $object_type );

	if ( !$cur ) {
		return true;
	}
}




/**
 * Deletes meta data from the database
 *
 * @internal
 */
function wpsc_delete_meta( $object_id = 0, $meta_key, $meta_value, $type, $global = false ) {
	global $wpdb;
	if ( !is_numeric( $object_id ) || empty( $object_id ) && !$global ) {
		return false;
	}
	$cache_object_id = $object_id = (int) $object_id;
	
	$object_type = $type;

	$meta_key = wpsc_sanitize_meta_key( $meta_key );

	$meta_tuple = compact( 'object_type', 'object_id', 'meta_key', 'meta_value', 'type' );
	$meta_tuple = apply_filters( 'wpsc_delete_meta', $meta_tuple );
	extract( $meta_tuple, EXTR_OVERWRITE );

	$meta_value = maybe_serialize( $meta_value );

	if ( empty( $meta_value ) ) {
		$meta_sql = $wpdb->prepare( "SELECT `meta_id` FROM `".WPSC_TABLE_META."` WHERE `object_type` = %s AND `object_id` = %d AND `meta_key` = %s", $object_type, $object_id, $meta_key );
	} else {
		$meta_sql = $wpdb->prepare( "SELECT `meta_id` FROM `".WPSC_TABLE_META."` WHERE `object_type` = %s AND `object_id` = %d AND `meta_key` = %s AND `meta_value` = %s", $object_type, $object_id, $meta_key, $meta_value );
	}

	if ( !$meta_id = $wpdb->get_var( $meta_sql ) ) {
		return false;
	}

	$wpdb->query( $wpdb->prepare( "DELETE FROM `".WPSC_TABLE_META."` WHERE `meta_id` = %d", $meta_id ) );
	wp_cache_delete( $cache_object_id, $object_type );
	return true;
}



/**
 * Cart meta functions are as follows:
*/


function wpsc_get_cartmeta( $cart_id, $meta_key ) {
	return wpsc_get_meta( $cart_id, $meta_key, 'wpsc_cart_item' );
}

function wpsc_update_cartmeta( $cart_id, $meta_key, $meta_value ) {
	return wpsc_update_meta( $cart_id, $meta_key, $meta_value, 'wpsc_cart_item' );
}

function wpsc_delete_cartmeta( $cart_id, $meta_key, $meta_value = '' ) {
	return wpsc_delete_meta( $cart_id, $meta_key, $meta_value, 'wpsc_cart_item' );
}
/**
 * Cart meta functions end here.
*/

/**
 * category meta functions are as follows:
*/


function wpsc_get_categorymeta( $cart_id, $meta_key ) {
	return wpsc_get_meta( $cart_id, $meta_key, 'wpsc_category' );
}

function wpsc_update_categorymeta( $cart_id, $meta_key, $meta_value ) {
	return wpsc_update_meta( $cart_id, $meta_key, $meta_value, 'wpsc_category' );
}

function wpsc_delete_categorymeta( $cart_id, $meta_key, $meta_value = '' ) {
	return wpsc_delete_meta( $cart_id, $meta_key, $meta_value, 'wpsc_category' );
}
/**
 * category meta functions end here.
*/


?>