<?php
/**
 * WPSC Product modifying functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

/**
 * Check the memory_limit and calculate a recommended memory size
 * inspired by nextGenGallery Code
 * 
 * @return string message about recommended image size
 */
function wpsc_check_memory_limit() {

	if ( (function_exists('memory_get_usage')) && (ini_get('memory_limit')) ) {
		
		// get memory limit
		$memory_limit = ini_get('memory_limit');
		if ($memory_limit != '')
			$memory_limit = substr($memory_limit, 0, -1) * 1024 * 1024;
		
		// calculate the free memory 	
		$freeMemory = $memory_limit - memory_get_usage();
		
		// build the test sizes
		$sizes = array();
		$sizes[] = array ( 'width' => 800, 'height' => 600 );
		$sizes[] = array ( 'width' => 1024, 'height' => 768 );
		$sizes[] = array ( 'width' => 1280, 'height' => 960 );  // 1MP	
		$sizes[] = array ( 'width' => 1600, 'height' => 1200 ); // 2MP
		$sizes[] = array ( 'width' => 2016, 'height' => 1512 ); // 3MP
		$sizes[] = array ( 'width' => 2272, 'height' => 1704 ); // 4MP
		$sizes[] = array ( 'width' => 2560, 'height' => 1920 ); // 5MP
		
		// test the classic sizes
		foreach ($sizes as $size){
			// very, very rough estimation
			if ($freeMemory < round( $size['width'] * $size['height'] * 5.09 )) {
            	$result = sprintf(  __( 'Please refrain from uploading images larger than <strong>%d x %d</strong> pixels' ), $size['width'], $size['height']); 
				return $result;
			}
		}
	}
	return;
} 

function wpsc_get_max_upload_size(){
// Get PHP Max Upload Size
	if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');	
	else $upload_max = __('N/A', 'nggallery');
	
	return $upload_max;

}
 /**
	* wpsc_admin_submit_product function 
	*
	* @return nothing
*/
function wpsc_admin_submit_product() {
  check_admin_referer('edit-product', 'wpsc-edit-product');
  $post_data = wpsc_sanitise_product_forms();
  if(isset($post_data['title']) && $post_data['title'] != '' && isset($post_data['category'])){
		$product_id = wpsc_insert_product($post_data, true);
		if($product_id > 0) {
			$sendback = add_query_arg('product_id', $product_id);
		}
		
		$sendback = add_query_arg('message', 1, $sendback);
  //exit('<pre>'.print_r($sendback,true).'</pre>');
		wp_redirect($sendback);
  } else {
  	$_SESSION['product_error_messages'] = array();	
  	if($post_data['title'] == ''){
  		$_SESSION['product_error_messages'][] = __('<strong>ERROR</strong>: Please enter a Product name.<br />');
  	}
  	if(!isset($post_data['category'])){
  		$_SESSION['product_error_messages'][] = __('<strong>ERROR</strong>: Please enter a Product Category.<br />');
   	}
   	
   	$_SESSION['wpsc_failed_product_post_data'] = $post_data;
   //	exit('<pre>'.print_r($_SESSION['product_error_messages'], true).'</pre>');
  	$sendback = add_query_arg('ErrMessage', 1);
		wp_redirect($sendback);
  }
	exit();
}
 
 
  /**
	* wpsc_sanitise_product_forms function 
	* 
	* @return array - Sanitised product details
*/
function wpsc_sanitise_product_forms($post_data = null) {
	if ( empty($post_data) ) {
		$post_data = &$_POST;
	}
// 	$post_data['product_id'] = isset($post_data['product_id']) ? $post_data['product_id'] : '';
	$post_data['name'] = isset($post_data['title']) ? $post_data['title'] : '';
	$post_data['description'] = isset($post_data['content']) ? $post_data['content'] : '';
	$post_data['meta'] = isset($post_data['productmeta_values']) ? $post_data['productmeta_values'] : '';
	$post_data['edit_variation_values'] = $post_data['edit_var_val'];

  // cast to boolean to convert to true or false, then cast to integer to convert to 1 or 0
	$post_data['quantity_limited'] = (int)(bool)$post_data['quantity_limited'];
	$post_data['special'] = (int)(bool)$post_data['special'];
	$post_data['notax'] = (int)(bool)$post_data['notax'];
	$post_data['donation'] = (int)(bool)$post_data['donation'];
	$post_data['no_shipping'] = (int)(bool)$post_data['no_shipping'];
	$post_data['publish'] = (int)(bool)$post_data['publish']; 
	$post_data['meta']['unpublish_oos'] = (int)(bool)$post_data['inform_when_oos'];

	$post_data['price'] = (float)$post_data['price'];
	if(is_numeric($post_data['special_price'])) {
		$post_data['special_price'] = (float)($post_data['price'] - $post_data['special_price']);
	} else {
		$post_data['special_price'] = 0;
	}
	
	// if special is unticked, wipe the special_price value
// 	if($post_data['special'] !== 1) {
// 	  $post_data['special_price'] = 0;
// 	}
	
	// if table_rate_price is unticked, wipe the table rate prices
	if($post_data['table_rate_price'] != 1) {
		$post_data['meta']['table_rate_price'] = null;
	}

	$post_data['files'] = $_FILES;
//exit('<pre>'.print_r($post_data, true).'</pre><pre>'.print_r($_POST, true).'</pre>');
  //exit('<pre>'.print_r($post_data, true).'</pre>');
  return $post_data;
}
  
 /**
	* wpsc_insert_product function 
	*
	* @param unknown 
	* @return unknown
*/
 // exit('Image height'.get_option('product_image_height'));	
function wpsc_insert_product($post_data, $wpsc_error = false) {
  global $wpdb;
  $adding = false;
  $update = false;
  if((int)$post_data['product_id'] > 0) {
	  $product_id	= absint($post_data['product_id']);
    $update = true;
  }
  
  $product_columns = array(
		'name' => '',
		'description' => '',
		'additional_description' => '',
		'price' => null,
		'weight' => null,
		'weight_unit' => '',
		'pnp' => null,
		'international_pnp' => null,
		'file' => null,
		'image' => '0',
		'quantity_limited' => '',
		'quantity' => null,
		'special' => null,
		'special_price' => null,
		'display_frontpage' => null,
		'notax' => null,
		'publish' => null,
		'active' => null,
		'donation' => null,
		'no_shipping' => null,
		'thumbnail_image' => null,
		'thumbnail_state' => null
  );
  

  foreach($product_columns as $column => $default) {
    if(isset($post_data[$column]) || ($post_data[$column] !== null) ) {
			$update_values[$column] = stripslashes($post_data[$column]);
    } else if(($update != true) && ($default !== null)) {
			$update_values[$column] = stripslashes($default);
    }
  }
   if($update === true) {
		$where = array( 'id' => $product_id );
		if ( false === $wpdb->update( WPSC_TABLE_PRODUCT_LIST, $update_values, $where ) ) {
			if ( $wpsc_error ) {
				return new WP_Error('db_update_error', __('Could not update product in the database'), $wpdb->last_error);
			} else {
				return false;
			}
		}			
  } else {
		if ( false === $wpdb->insert( WPSC_TABLE_PRODUCT_LIST, $update_values ) ) {
			if ( $wp_error ) {
				return new WP_Error('db_insert_error', __('Could not insert product into the database'), $wpdb->last_error);
			} else {
				return 0;
			}
		}
		$adding = true;
		$product_id = (int) $wpdb->insert_id;
  }
  
  
	/* Add tidy url name */
	if($post_data['name'] != '') {
		$existing_name = get_product_meta($product_id, 'url_name');
		// strip slashes, trim whitespace, convert to lowercase
		$tidied_name = strtolower(trim(stripslashes($post_data['name'])));
		// convert " - " to "-", all other spaces to dashes, and remove all foward slashes.
		//$url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/", "/(\/)+/"), array("-","-", ""), $tidied_name);
		$url_name =  sanitize_title($tidied_name);
		
		// Select all similar names, using an escaped version of the URL name 
		$similar_names = (array)$wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` NOT IN('{$product_id}}') AND `meta_key` IN ('url_name') AND `meta_value` REGEXP '^(".$wpdb->escape(preg_quote($url_name))."){1}[[:digit:]]*$' ");

		// Check desired name is not taken
		if(array_search($url_name, $similar_names) !== false) {
		  // If it is, try to add a number to the end, if that is taken, try the next highest number...
			$i = 0;
			do {
				$i++;
			} while(array_search(($url_name.$i), $similar_names) !== false);
			// Concatenate the first number found that wasn't taken
			$url_name .= $i;
		}
	  // If our URL name is the same as the existing name, do othing more.
		if($existing_name != $url_name) {
			update_product_meta($product_id, 'url_name', $url_name);
		}
	}
  
	// if we succeed, we can do further editing
	
	// update the categories
	wpsc_update_category_associations($product_id, $post_data['category']);
	
	// and the tags
	wpsc_update_product_tags($product_id, $post_data['product_tags'], $post_data['wpsc_existing_tags']);
	
	// and the meta
	wpsc_update_product_meta($product_id, $post_data['meta']);
	
	// and the custom meta
	wpsc_update_custom_meta($product_id, $post_data);

	// and the images
	wpsc_update_product_images($product_id, $post_data);
	
	//and the alt currency
	foreach((array)$post_data['newCurrency'] as $key =>$value){
		wpsc_update_alt_product_currency($product_id, $value, $post_data['newCurrPrice'][$key]);
	}
	
	if($post_data['files']['file']['tmp_name'] != '') {
		wpsc_item_process_file($product_id, $post_data['files']['file']);
	} else {
	  wpsc_item_reassign_file($product_id, $post_data['select_product_file']);
	}
	
	//exit('<pre>'.print_r($post_data, true).'</pre>');
	if($post_data['files']['preview_file']['tmp_name'] != '') {
 		wpsc_item_add_preview_file($product_id, $post_data['files']['preview_file']);
	}
     
	$variations_processor = new nzshpcrt_variations;
	
	if(($adding === true) && ($_POST['variations'] != null)) {
		foreach((array)$_POST['variations'] as $variation_id => $state) {
			$variation_id = (int)$variation_id;
			if($state == 1) {
				$variation_values = $variations_processor->falsepost_variation_values($variation_id);
				$variations_processor->add_to_existing_product($product_id,$variation_values);
			}
		}
	}
	
	
	if($post_data['edit_variation_values'] != null) {
		$variations_processor->edit_product_values($product_id,$post_data['edit_variation_values']);
	}
	
	if($post_data['edit_add_variation_values'] != null) {
		$variations_processor->edit_add_product_values($product_id,$post_data['edit_add_variation_values']);
	}
		
	if($post_data['variation_priceandstock'] != null) {
		$variations_processor->update_variation_values($product_id, $post_data['variation_priceandstock']);
	}

	
	do_action('wpsc_edit_product', $product_id);
	wpsc_ping();
	return $product_id;
}

function wpsc_update_alt_product_currency($product_id, $newCurrency, $newPrice){
	global $wpdb;
	$sql = "SELECT `isocode` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`=".$newCurrency;
	$isocode = $wpdb->get_var($sql);
	//exit($sql);
	$newCurrency = 'currency['.$isocode.']';
	
	if(($newPrice != '') &&  ($newPrice > 0)){
		update_product_meta($product_id, $newCurrency, $newPrice, $prev_value = '');
	} else {
		delete_product_meta($product_id, $newCurrency);
	}
	
	//exit('<pre>'.print_r($newCurrency, true).'</pre>'.$newPrice);
}
/**
 * wpsc_update_categories function 
 *
 * @param integer product ID
 * @param array submitted categories
 */
function wpsc_update_category_associations($product_id, $categories = array()) {
  global $wpdb;
  
  $associated_categories = $wpdb->get_col($wpdb->prepare("SELECT `category_id` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` IN('%s')", $product_id));
  
  $categories_to_add = array_diff((array)$categories, (array)$associated_categories);
  $categories_to_delete = array_diff((array)$associated_categories, (array)$categories);
  $insert_sections = array();
  foreach($categories_to_delete as $key => $category_to_delete) {
		$categories_to_delete[$key] = absint($category_to_delete);
  }

	//exit('<pre>'.print_r($categories_to_delete, true).'</pre>');

  foreach($categories_to_add as $category_id) {
    $insert_sections[] = $wpdb->prepare("( %d, %d)", $product_id, $category_id);
  }
  if(count($insert_sections)) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` (`product_id`, `category_id`) VALUES ".implode(", ",$insert_sections)."");
  }
  
  foreach($categories_to_add as $category_id) {
		$check_existing = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category_id') AND `order` IN('0') LIMIT 1;",ARRAY_A);
		if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category_id') AND `product_id` IN('$product_id') LIMIT 1")) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = '0' WHERE `category_id` IN('$category_id') AND `product_id` IN('$product_id') LIMIT 1;");
		} else {				  
			$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_ORDER."` (`category_id`, `product_id`, `order`) VALUES ('$category_id', '$product_id', 0)");
		}
		if($check_existing != null) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = (`order` + 1) WHERE `category_id` IN('$category_id') AND `product_id` NOT IN('$product_id') AND `order` < '0'");
		}
  }
  if(count($categories_to_delete) > 0) {
    $wpdb->query("DELETE FROM`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` = {$product_id} AND `category_id` IN(".implode(",",$categories_to_delete).") LIMIT ".count($categories_to_delete)."");
  }
}
  
  /**
 * wpsc_update_product_tags function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_tags($product_id, $product_tags, $existing_tags) {
	if(isset($existing_tags)){
		$tags = explode(',',$existing_tags);
		if(is_array($tags)){
			foreach((array)$tags as $tag){
				$tt = wp_insert_term((string)$tag, 'product_tag');
			}
		}
	}
	wp_set_object_terms($product_id, $tags, 'product_tag');
	if(isset($product_tags) && $product_tags != 'Add new tag') {
		
		$tags = explode(',',$product_tags);
		product_tag_init();
		if(is_array($tags)) {
			foreach((array)$tags as $tag){
				$tt = wp_insert_term((string)$tag, 'product_tag');
			}
		}
		wp_set_object_terms($product_id, $tags, 'product_tag');
	}
}

 /**
 * wpsc_update_product_meta function
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_meta($product_id, $product_meta) {
    if($product_meta != null) {
      foreach((array)$product_meta as $key => $value) {
        if(get_product_meta($product_id, $key) != false) {
          update_product_meta($product_id, $key, $value);
				} else {
          add_product_meta($product_id, $key, $value);
				}
			}
		}
}

/*
/* Code to support Publish/No Publish (1bigidea)
*/
/**
 * set status of publish conditions
 * @return 
 * @param string 	$product_id
 * @param bool			$status		Publish State 
 */
function wpsc_set_publish_status($product_id, $state) {
	global $wpdb;
	$status = (int) ( $state ) ? 1 : 0; // Cast the Publish flag
	$result = $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `publish` = '{$status}' WHERE `id` = '{$product_id}'");
}
/**
 * Toggle publish status and update product record
 * @return bool		Publish status
 * @param string	$product_id
 */
function wpsc_toggle_publish_status($product_id) {
	global $wpdb;
	$status = (int) ( wpsc_publish_status($product_id) ) ? 0 : 1; // Flip the Publish flag True <=> False
	$sql = "UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `publish` = '{$status}' WHERE `id` = '{$product_id}'";
	$result = $wpdb->query($sql);
	return $status;
}
/**
 * Returns publish status from product database
 * @return bool		publish status
 * @param string	$product_id
 */
function wpsc_publish_status($product_id) {
	global $wpdb;
	$status = (bool)$wpdb->get_var("SELECT `publish` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$product_id}'");
	return $status;
}
/**
 * Called from javascript within product page to toggle publish status - AJAX
 * @return bool	publish status
 */
function wpsc_ajax_toggle_publish() {
/**
 * @todo - Check Admin Referer
 * @todo - Check Permissions
 */
	$status = (wpsc_toggle_publish_status($_REQUEST['productid'])) ? ('true') : ('false');
	exit( $status );
}
//add_action('wp_ajax_wpsc_toggle_publish','wpsc_ajax_toggle_publish');
/*
/*  END - Publish /No Publish functions
*/

function wpsc_update_custom_meta($product_id, $post_data) {
  global $wpdb;
    if($post_data['new_custom_meta'] != null) {
      foreach((array)$post_data['new_custom_meta']['name'] as $key => $name) {
				$value = $post_data['new_custom_meta']['value'][(int)$key];
        if(($name != '') && ($value != '')) {
					add_product_meta($product_id, $name, $value, false, true);
        }
			}
		}
		
		
    if($post_data['custom_meta'] != null) {
      foreach((array)$post_data['custom_meta'] as $key => $values) {
        if(($values['name'] != '') && ($values['value'] != '')) {
          $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_key` = '".$wpdb->escape($values['name'])."', `meta_value` = '".$wpdb->escape($values['value'])."' WHERE `id` IN ('".(int)$key."')LIMIT 1 ;");
         // echo "UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_key` = '".$wpdb->escape($values['name'])."', `meta_value` = '".$wpdb->escape($values['value'])."' WHERE `id` IN ('".(int)$key."') LIMIT 1 ;";
					//add_product_meta($_POST['prodid'], $values['name'], $values['value'], false, true);
        }
			}
		}
}

/**
* wpsc_update_product_tags function 
*
* @param integer product ID
* @param array the post data
*/
function wpsc_update_product_images($product_id, $post_data) {
  global $wpdb;
  $uploaded_images = array();

  // This segment is for associating the images uploaded using swfuploader when adding a product
  foreach((array)$post_data['gallery_image_id'] as $added_image) {
		if($added_image > 0) {
			$uploaded_images[] = absint($added_image);
    }
  }
  if(count($uploaded_images) > 0) {
		$uploaded_image_data = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` IN (".implode(', ', $uploaded_images).") AND `product_id` = '0'");
		if(count($uploaded_image_data) > 0) {
			$first_image = null;
			foreach($uploaded_image_data as $uploaded_image_id) {
				if($first_image === null) {
					$first_image = absint($uploaded_image_id);
				}
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `product_id` = '$product_id' WHERE `id` = '{$uploaded_image_id}' LIMIT 1;");
			}
			
			$previous_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1");
			if($previous_image == 0) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$first_image}' WHERE `id`='{$product_id}' LIMIT 1");
			}
			wpsc_resize_image_thumbnail($product_id, 1);
		}
	}

  

	/* Handle new image uploads here */
  if($post_data['files']['image']['tmp_name'] != '') {
		$image = wpsc_item_process_image($product_id, $post_data['files']['image']['tmp_name'], str_replace(" ", "_", $post_data['files']['image']['name']), $post_data['width'], $post_data['height'], $post_data['image_resize']);
		
		$image_action = absint($post_data['image_resize']);
		$image_width = $post_data['width'];
		$image_height = $post_data['height'];
	
	} else {
		$image_action = absint($post_data['gallery_resize']);
		$image_width = $post_data['gallery_width'];
		$image_height = $post_data['gallery_height'];
		
	}
	
//    exit( "<pre>".print_r($image_action, true)."</pre>");
	wpsc_resize_image_thumbnail($product_id, $image_action, $image_width, $image_height);
 	//exit( " <pre>".print_r($post_data, true)."</pre>");
	
	


}

 /**
 * wpsc_resize_image_thumbnail function 
 *
 * @param integer product ID
 * @param integer the action to perform on the image
 * @param integer the width of the thumbnail image
 * @param integer the height of the thumbnail image
 * @param array the custom image array from $_FILES
 */
function wpsc_resize_image_thumbnail($product_id, $image_action= 0, $width = 0, $height = 0, $custom_image = null) {
  global $wpdb;
	$image_id = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$product_id}' LIMIT 1");
	$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_id}' LIMIT 1");
	
	// check if there is an image that is supposed to be there.
	if($image != '') {
		if(is_numeric($image)){			
		}
	  // check that is really there
	  if(file_exists(WPSC_IMAGE_DIR.$image)) {
			// if the width or height is less than 1, set the size to the default

			if((($width  < 1) || ($height < 1)) && ($image_action == 2)) {
				$image_action = 1;
			}
			switch($image_action) {
				case 0:
					if(!file_exists(WPSC_THUMBNAIL_DIR.$image)) {
						copy(WPSC_IMAGE_DIR.$image, WPSC_THUMBNAIL_DIR.$image);
					}
				break;
					
				
				case 1:
				  // if case 1, replace the provided size with the default size
					$height = get_option('product_image_height');
					$width  = get_option('product_image_width');				
				case 2:
				  // if case 2, use the provided size
					$image_input = WPSC_IMAGE_DIR . $image;
					$image_output = WPSC_THUMBNAIL_DIR . $image;
					
					if($width < 1) {
						$width = 96;
					}
					if($height < 1) {
						$height = 96;
					}
					
					image_processing($image_input, $image_output, $width, $height);
					update_product_meta($product_id, 'thumbnail_width', $width);
					update_product_meta($product_id, 'thumbnail_height', $height);
				break;
				
				case 3:
				  // replacing the thumbnail with a custom image is done here
				  $uploaded_image = null;
				    //exit($uploaded_image);
				   if(file_exists($_FILES['gallery_thumbnailImage']['tmp_name'])) {
						$uploaded_image =  $_FILES['gallery_thumbnailImage']['tmp_name'];
				   } else if(file_exists($_FILES['thumbnailImage']['tmp_name'])) {
						$uploaded_image =  $_FILES['thumbnailImage']['tmp_name'];
				   }
				  if($uploaded_image !== null) {
				  		$image = uniqid().$image;
						move_uploaded_file($uploaded_image, WPSC_THUMBNAIL_DIR.$image);
				    //exit($uploaded_image);
				  
				  }
				break;
			}
			
			if(!file_exists(WPSC_IMAGE_DIR.$image)) {
				//$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` SET `thumbnail_state` = '$image_action' WHERE `id`='{$product_id}' LIMIT 1");
				if($image_action != 3){
				$sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$product_id}', '{$image}', '{$width}', '{$height}' )";
				$wpdb->query($sql);	
				
					$image_id = (int) $wpdb->insert_id;
				}
			}
			if($image_action != 3){
				$sql="UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_state` = '$image_action', `image` ='{$image_id}' WHERE `id`='{$product_id}' LIMIT 1";
			}else{
	$sql="UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_state` = '$image_action', `image` ='{$image_id}',`thumbnail_image`='{$image}' WHERE `id`='{$product_id}' LIMIT 1";
			}
			
			$wpdb->query($sql);
		} else {
			//if it is not, we need to unset the associated image
			//$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '' WHERE `id`='{$product_id}' LIMIT 1");
			//$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$product_id}', '{$image}', '{$width}', '{$height}' )");	
		}
	}

}




 /**
 * wpsc_upload_image_thumbnail function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_upload_image_thumbnail($product_id, $product_meta) {
		if(($_POST['image_resize'] == 3) && ($_FILES['thumbnailImage'] != null) && file_exists($_FILES['thumbnailImage']['tmp_name'])) {
			$imagefield='thumbnailImage';
	
			$image=image_processing($_FILES['thumbnailImage']['tmp_name'], WPSC_THUMBNAIL_DIR.$_FILES['thumbnailImage']['name'],null,null,$imagefield);
			$thumbnail_image = $image;
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_image` = '".$thumbnail_image."' WHERE `id` = '".$image_data['id']."'");
			$stat = stat( dirname( (WPSC_THUMBNAIL_DIR.$image_data['image']) ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( (WPSC_THUMBNAIL_DIR.$image_data['image']), $perms );	
		}
}


 /**
 * wpsc_item_process_file function 
 *
 * @param integer product ID
 * @param array the file array from $_FILES 
 * @param array the preview file array from $_FILES
 */
function wpsc_item_process_file($product_id, $submitted_file, $preview_file = null) {
  global $wpdb;
  $preview_file = null; //break this, is done in a different function, now
	$files = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_PRODUCT_FILES." ORDER BY id ASC", ARRAY_A);
	
	if (is_array($files)){
		foreach($files as $file){
			$file_names[] = $file['filename'];
			$file_hashes[] = $file['idhash'];
		}
	}
		
	if(apply_filters( 'wpsc_filter_file', $submitted_file['tmp_name'] )) {
	  // initialise $idhash to null to prevent issues with undefined variables and error logs
	  $idhash = null;
// 		$fileid_data = $wpdb->get_results("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
		/* if we are adding, make a new file row and get the ID of it */
		$timestamp = time();
		$query_results = $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_FILES."` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '', '', '', '$timestamp');");
		$fileid = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `".WPSC_TABLE_PRODUCT_FILES."`");
			
			
		/* if there is no idhash, generate it */
		if($idhash == null) {
			$idhash = sha1($fileid);
			if($idhash == '') {
			  // if sha1 doesnt spit an error, but doesnt return anything either (it has done so on some servers)
				$idhash = md5($fileid);
			}
		}
		// if needed, we can add code here to stop hash doubleups in the unlikely event that they shoud occur
	
		$mimetype = wpsc_get_mimetype($submitted_file['tmp_name']);
		
		$filename = basename($submitted_file['name']);
		
		
		if (in_array($submitted_file['name'],(array)$file_names)){
			$i=0;
			$new_name = $submitted_file['name'].".old";
			while(file_exists(WPSC_FILE_DIR.$new_name)){
				$new_name = $submitted_file['name'].".old_".$i;
				$i++;
			}
			$old_idhash_id = array_search($submitted_file['name'],(array)$file_names);
			$old_idhash = $file_hashes[$old_idhash_id];
			while(!file_exists(WPSC_FILE_DIR.$old_idhash)){
				unset($file_hashes[$old_idhash_id]);
				unset($file_names[$old_idhash_id]);
				
				$old_idhash_id = array_search($submitted_file['name'],(array)$file_names);
				$old_idhash = $file_hashes[$old_idhash_id];
			}
			if(is_file(WPSC_FILE_DIR.$old_idhash)) {
				copy(WPSC_FILE_DIR.$old_idhash, WPSC_FILE_DIR.$new_name);
				unlink(WPSC_FILE_DIR.$old_idhash);
			}
		}
		if(move_uploaded_file($submitted_file['tmp_name'],(WPSC_FILE_DIR.$idhash)))	{
			$stat = stat( dirname( (WPSC_FILE_DIR.$idhash) ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( (WPSC_FILE_DIR.$idhash), $perms );	
			if(function_exists("make_mp3_preview"))	{
				if($mimetype == "audio/mpeg" && (!isset($preview_file['tmp_name']))) {
				  // if we can generate a preview file, generate it (most can't due to sox being rare on servers and sox with MP3 support being even rarer), thus this needs to be enabled by editing code
					make_mp3_preview((WPSC_FILE_DIR.$idhash), (WPSC_PREVIEW_DIR.$idhash.".mp3"));
					$preview_filepath = (WPSC_PREVIEW_DIR.$idhash.".mp3");
				} else if(file_exists($preview_file['tmp_name'])) {    
					$preview_filename = basename($preview_file['name']);
					$preview_mimetype = wpsc_get_mimetype($preview_file['tmp_name']);
					copy($preview_file['tmp_name'], (WPSC_PREVIEW_DIR.$preview_filename));
					$preview_filepath = (WPSC_PREVIEW_DIR.$preview_filename);
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '$fileid' LIMIT 1");
				}
				$stat = stat( dirname($preview_filepath));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $preview_filepath, $perms );	
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `product_id` = '{$product_id}', `filename` = '".$wpdb->escape($filename)."', `mimetype` = '$mimetype', `idhash` = '$idhash' WHERE `id` = '$fileid' LIMIT 1");
		}
		$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		return $fileid;
  } else {
		return false;
  }
}
 /**
 * wpsc_item_reassign_file function 
 *
 * @param integer product ID
 * @param string the selected file name;
 */
function wpsc_item_reassign_file($product_id, $selected_files) {
  global $wpdb;
  $product_file_list=array();
	// initialise $idhash to null to prevent issues with undefined variables and error logs
	$idhash = null;
	/* if we are editing, grab the current file and ID hash */ 
	if(!$selected_files) {
		// unlikely that anyone will ever upload a file called .none., so its the value used to signify clearing the product association
		$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '0' WHERE `id` = '$product_id' LIMIT 1");
		return null;
	}
	foreach($selected_files as $selected_file) {
		// if we already use this file, there is no point doing anything more.
		$current_fileid = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1");
		if($current_fileid > 0) {
			$current_file_data = $wpdb->get_row("SELECT `id`,`idhash` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id` = '$current_fileid' LIMIT 1",ARRAY_A);
			if(basename($selected_file) == $file_data['idhash']) {
				//$product_file_list[] = $current_fileid;
				//return $current_fileid;
			}
		}
		
		$selected_file = basename($selected_file);
		if(file_exists(WPSC_FILE_DIR.$selected_file)) {
			$timestamp = time();
			$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `idhash` IN('".$wpdb->escape($selected_file)."') LIMIT 1", ARRAY_A);
			$fileid = (int)$file_data['id'];
			// if the file does not have a database row, add one.
			if($fileid < 1) {
				$mimetype = wpsc_get_mimetype(WPSC_FILE_DIR.$selected_file);
				$filename = $idhash = $selected_file;
				$timestamp = time();
				$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_FILES."` (`product_id`, `filename`  , `mimetype` , `idhash` , `date` ) VALUES ('{$product_id}', '{$filename}', '{$mimetype}', '{$idhash}', '{$timestamp}');");
				$fileid = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `date` = '{$timestamp}' AND `filename` IN ('{$filename}')");
			}
			// update the entry in the product table
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
			$product_file_list[] = $fileid;
		}	
  }

  
	//exit('<pre>'.print_r($product_file_list, true).'</pre>');
  update_product_meta($product_id, 'product_files', $product_file_list);
	return $fileid;
}



 /**
 * wpsc_item_add_preview_file function 
 *
 * @param integer product ID
 * @param array the preview file array from $_FILES
 */
function wpsc_item_add_preview_file($product_id, $preview_file) {
  global $wpdb;
  
	$current_file_id = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1");
	$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='{$current_file_id}' LIMIT 1",ARRAY_A);
	
	if(apply_filters( 'wpsc_filter_file', $preview_file['tmp_name'] )) {
	  //echo "test?";
		if(function_exists("make_mp3_preview"))	{
			if($mimetype == "audio/mpeg" && (!isset($preview_file['tmp_name']))) {
				// if we can generate a preview file, generate it (most can't due to sox being rare on servers and sox with MP3 support being even rarer), thus this needs to be enabled by editing code
				make_mp3_preview((WPSC_FILE_DIR.$idhash), (WPSC_PREVIEW_DIR.$idhash.".mp3"));
				$preview_filepath = (WPSC_PREVIEW_DIR.$idhash.".mp3");
			} else if(file_exists($preview_file['tmp_name'])) {    
				$preview_filename = basename($preview_file['name']);
				$preview_mimetype = wpsc_get_mimetype($preview_file['tmp_name']);
				copy($preview_file['tmp_name'], (WPSC_PREVIEW_DIR.$preview_filename));
				$preview_filepath = (WPSC_PREVIEW_DIR.$preview_filename);
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '{$file_data['id']}' LIMIT 1");
				//exit("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '{$file_data['id']}' LIMIT 1");
			}
			$stat = stat( dirname($preview_filepath));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $preview_filepath, $perms );	
		}
		//exit("<pre>".print_r($preview_file,true)."</pre>");
		return $fileid;
   } else {
 		return $selected_files;
   }  
}

?>
