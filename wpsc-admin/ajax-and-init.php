<?php
/**
 * WP eCommerce Admin AJAX functions
 *
 * These are the WPSC Admin AJAX functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
//exit('<pre>'.print_r($_POST, true).'</pre>');
 function wpsc_ajax_add_tracking() {
  	global $wpdb;
  	foreach($_POST as $key=>$value){
	  	if($value != ''){
  		 	$parts = preg_split('/^wpsc_trackingid/', $key);
	  		if(count($parts) > '1'){
	  			$id = $parts[1];
	  			$trackingid = $value;
	  			$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `track_id`='".$trackingid."' WHERE `id`=".$id;
	  			$wpdb->query($sql);
	  		}
	  	}
  	}
	
}
 
 
if($_REQUEST['submit'] == 'Add Tracking ID') {
	add_action('admin_init', 'wpsc_ajax_add_tracking');
}
 function wpsc_delete_currency_layer() {
  	global $wpdb;
  	$meta_key = 'currency['.$_POST['currSymbol'].']';
  	$sql= "DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key`='".$meta_key."' LIMIT 1";
  	$wpdb->query($sql);
//	exit('<pre>'.print_r($_POST, true).'</pre>');

}
 
 
if($_REQUEST['wpsc_admin_action'] == 'delete_currency_layer') {
	add_action('admin_init', 'wpsc_delete_currency_layer');
}


 function wpsc_purchlog_email_trackid() {
  	global $wpdb;
  	$id = absint($_POST['purchlog_id']);
  	$trackingid = $wpdb->get_var("SELECT `track_id` FROM ".WPSC_TABLE_PURCHASE_LOGS." WHERE `id`={$id} LIMIT 1");

		$message = get_option('wpsc_trackingid_message');
		$message = str_replace('%trackid%',$trackingid,$message);
    $message = str_replace('%shop_name%',get_option('blogname'),$message);

		$email_form_field = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1");
		$email = $wpdb->get_var("SELECT `value` FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$id." AND `form_id` = '$email_form_field' LIMIT 1");

		
    $subject = get_option('wpsc_trackingid_subject');
    $subject = str_replace('%shop_name%',get_option('blogname'),$subject);
    wp_mail($email, $subject, $message,"From: ".get_option('return_email')." <".get_option('return_email').">");
    //exit($email.'<br /> '.$subject.'<br /> '. $message.'<br /> '."From: ".get_option('return_email')." <".get_option('return_email').">");
    exit(true);
}

 
if($_REQUEST['wpsc_admin_action'] == 'purchlog_email_trackid') {
	add_action('admin_init', 'wpsc_purchlog_email_trackid');
}

function wpsc_ajax_sales_quarterly() {
  	global $wpdb;
  	$lastdate = $_POST['add_start'];
  	$date = preg_split('/-/', $lastdate);
  	$lastquart = mktime(0,0,0,$date[1], $date[2], $date[0]);
  	//$lastdate = date('M d y', $lastquart);
  	if($lastquart != get_option('wpsc_last_quarter')){  		
  		update_option('wpsc_last_date', $lastdate);
  		update_option('wpsc_fourth_quart', $lastquart);
  	 	$thirdquart =   mktime(0,0,0,$date[1]-3, $date[2], $date[0]); 		
 		update_option('wpsc_third_quart', $thirdquart);
	  	$secondquart =  mktime(0,0,0,$date[1]-6, $date[2], $date[0]);
 		update_option('wpsc_second_quart', $secondquart);
	  	$firstquart =   mktime(0,0,0,$date[1]-9, $date[2], $date[0]); 		
  		update_option('wpsc_first_quart', $firstquart);
  		$finalquart =  mktime(0,0,0,$date[1], $date[2], $date[0]-1); 		
  		update_option('wpsc_final_quart', $finalquart);
  		
  	}
	
  	
//	exit($lastquart.' '.$firstquart.' '.$secondquart.' '.$thirdquart);
}
 
 
if($_REQUEST['wpsc_admin_action'] == 'wpsc_quarterly') {
	add_action('admin_init', 'wpsc_ajax_sales_quarterly');
}


function wpsc_ajax_load_product() {
  global $wpdb;
  $product_id = absint($_REQUEST['product_id']);
  check_admin_referer('edit_product_' .  $product_id);
	wpsc_display_product_form($product_id);
	exit();
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'load_product') {
	add_action('admin_init', 'wpsc_ajax_load_product');
}

function wpsc_crop_thumb() {
	global $wpdb;
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$targ_w = $targ_h = $_POST['thumbsize'];
		$jpeg_quality = $_POST['jpegquality'];
		$product_id = $_POST['product_id'];
		
		$image['x'] = absint($_POST['x']);
		$image['y'] = absint($_POST['y']);
		$image['w'] = absint($_POST['w']);
		$image['h'] = absint($_POST['h']);
		
		
		$imagename = basename($_POST['imagename']);
		$source = WPSC_IMAGE_DIR.$imagename;
		$destination =  WPSC_THUMBNAIL_DIR.$imagename;
		
		if(is_file($source)) {
			$imagetype = getimagesize($source);
			
			switch($imagetype[2]) {
				case IMAGETYPE_JPEG:
				$img_r = imagecreatefromjpeg($source);
				break;
		
				case IMAGETYPE_GIF:
				$img_r = imagecreatefromgif($source);
				break;
		
				case IMAGETYPE_PNG:
				$img_r = imagecreatefrompng($source);
				break;
			}
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
			imagecopyresampled($dst_r,$img_r,0,0,$image['x'],$image['y'],$targ_w,$targ_h,$image['w'],$image['h']);
			imagejpeg($dst_r,$destination,$jpeg_quality);
			$cropped = true;
		}
		$sendback = wp_get_referer();
		if($cropped){
			$sendback = add_query_arg('product_id', $product_id, $sendback);
		}
		wp_redirect($sendback);
		//exit();
	}
}
 
  
 if($_REQUEST['wpsc_admin_action'] == 'crop_thumb') {
	add_action('admin_init', 'wpsc_crop_thumb');
} 

function wpsc_delete_file() {
	global $wpdb;
	$file_id = $_GET['file_id'];
	$file_hash = $wpdb->get_var("SELECT `idhash` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id` LIKE '".$file_id."' LIMIT 1");
	
  check_admin_referer('delete_file_'.$file_id);
	if(file_exists(WPSC_FILE_DIR.basename($file_hash)) && is_file(WPSC_FILE_DIR.basename($file_hash))) {
		if($wpdb->query($wpdb->prepare("DELETE FROM ".WPSC_TABLE_PRODUCT_FILES." WHERE idhash=%s", $file_hash)) == 1) {
			// Only delete the file if the delete query above affected a single row. Prevents deletion of an arbitrary file
			unlink(WPSC_FILE_DIR.basename($file_hash));
		}
	}
	if($_POST['ajax'] !== 'true') {
		$sendback = wp_get_referer();
		wp_redirect($sendback);
	}
	exit();
}

 
 if($_REQUEST['wpsc_admin_action'] == 'delete_file') {
	add_action('admin_init', 'wpsc_delete_file');
} 
 

function wpsc_bulk_modify_products() {
  global $wpdb;
  $doaction = $_GET['bulkAction'];
	$sendback = wp_get_referer();
  switch ( $doaction ) {
		case 'delete':
			if ( isset($_GET['product']) && ! isset($_GET['bulk_edit']) && (isset($doaction) || isset($_GET['doaction2'])) ) {
				check_admin_referer('bulk-products', 'wpsc-bulk-products');
				$deleted = 0;
				foreach( (array) $_GET['product'] as $product_id ) {
				  $product_id = absint($product_id);
					if($wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET  `active` = '0' WHERE `id`='{$product_id}' LIMIT 1")) {
						$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` = '{$product_id}' AND `meta_key` IN ('url_name')");  
						product_tag_init();
						$term = wp_get_object_terms($product_id, 'product_tag');
						if ($term->errors == '') {
							wp_delete_object_term_relationships($product_id, 'product_tag');
						}
	
						$deleted++;
					}
				}
			}
			if ( isset($deleted) ) {
				$sendback = add_query_arg('deleted', $deleted, $sendback);
			}
		break;
		
		case 'show':
		case 'hide':
			if ( isset($_GET['product']) && ! isset($_GET['bulk_edit']) && (isset($doaction) || isset($_GET['doaction2'])) ) {
				check_admin_referer('bulk-products', 'wpsc-bulk-products');
				$flipped = 0;
				$status = array('show' => 1, 'hide' => 0);
				if( !key_exists($_REQUEST['bulkAction'], $status) ) break; // Action not valid
				$status_key = $_REQUEST['bulkAction'];
				$status_value = $status[$status_key];
				foreach( (array) $_GET['product'] as $product_id ) {
				  $product_id = absint($product_id);
				  $new_status = wpsc_set_publish_status($product_id, $status_value);
				  $flipped++;
				}
			}
			$sendback = add_query_arg('flipped', $flipped, $sendback);
		break;

		
		default:
			if(isset($_GET['search']) && !empty($_GET['search'])) {
				// urlencode the search query to allow for spaces, etc
				$sendback = add_query_arg('search',urlencode(stripslashes($_GET['search'])), $sendback);
			}
		break;
	}
	
	wp_redirect($sendback);
	exit();
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'bulk_modify') {
	add_action('admin_init', 'wpsc_bulk_modify_products');
}

function wpsc_modify_product_price() {
	global $wpdb;
	$product_data = array_pop($_POST['product_price']);

	$product_id = absint($product_data['id']);
	$product_price = (float)$product_data['price'];
	$product_nonce = $product_data['nonce'];

	if(wp_verify_nonce($product_nonce, 'edit-product_price-'.$product_id) ) {
		if($wpdb->query("UPDATE ".WPSC_TABLE_PRODUCT_LIST." SET price='{$product_price}' WHERE id='{$product_id}'")) {
			echo "success = 1;\n\r";
			echo "new_price = '".nzshpcrt_currency_display($product_price, 1, true)."';\n\r";
		} else {
			echo "success = 0;\n\r";
		}
	} else {
		echo "success = -1;\n\r";
	}
	exit();
}

 
 if($_REQUEST['wpsc_admin_action'] == 'modify_price') {
	add_action('admin_init', 'wpsc_modify_product_price');
}
 
 
/**
  Function and action for deleting single products 
 */
function wpsc_delete_product() {
  global $wpdb;
  
	$deleted = 0;
	$product_id = absint($_GET['product']);
  check_admin_referer('delete_product_' .  $product_id);

	if($wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET  `active` = '0' WHERE `id`='{$product_id}' LIMIT 1")) {
		$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` = '{$product_id}' AND `meta_key` IN ('url_name')");  
		product_tag_init();
		$term = wp_get_object_terms($product_id, 'product_tag');
		if ($term->errors == '') {
			wp_delete_object_term_relationships($product_id, 'product_tag');
		}
		$deleted = 1;
		do_action('wpsc_delete_product', $product_id);
	}
	
	$sendback = wp_get_referer();
	if ( isset($deleted) ) {
		$sendback = add_query_arg('deleted', $deleted, $sendback);
	}
	wp_redirect($sendback);
	exit();
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'delete_product') {
	add_action('admin_init', 'wpsc_delete_product');
}
 
/**
  Function and action for publishing or unpublishing single products
 */
function wpsc_ajax_toggle_published() {
  global $wpdb;
  
	$product_id = absint($_GET['product']);
  check_admin_referer('toggle_publish_' . $product_id);
  
	$status = (wpsc_toggle_publish_status($product_id)) ? ('true') : ('false');
	$sendback = add_query_arg('flipped', "1", wp_get_referer());
	wp_redirect($sendback);
	exit();
}
 
 
 if($_REQUEST['wpsc_admin_action'] == 'toggle_publish') {
	add_action('admin_init', 'wpsc_ajax_toggle_published');
}

  
/**
  Function and action for duplicating products,
 */
function wpsc_duplicate_product() {
	global $wpdb;
	$product_id = absint($_GET['product']);
  check_admin_referer('duplicate_product_' .  $product_id);
	if ($product_id > 0) {
		$sql = " INSERT INTO ".WPSC_TABLE_PRODUCT_LIST."( `name` , `description` , `additional_description` , `price` , `weight` , `weight_unit` , `pnp` , `international_pnp` , `file` , `image`  , `quantity_limited` , `quantity` , `special` , `special_price` , `display_frontpage` , `notax` , `active` , `publish`, `donation` , `no_shipping` , `thumbnail_image` , `thumbnail_state` ) SELECT `name` , `description` , `additional_description` , `price` , `weight` , `weight_unit` , `pnp` , `international_pnp` , `file` , `image`  , `quantity_limited` , `quantity` , `special` , `special_price` , `display_frontpage` , `notax` , `active`  , `publish`, `donation` , `no_shipping` , `thumbnail_image` , `thumbnail_state` FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = '".$product_id."' ";
	//	exit($sql);
		$wpdb->query($sql);
		$new_id= $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_LIST."` LIMIT 1");
		
		//Inserting duplicated variations record.
		$nzshpcrt_variations = new nzshpcrt_variations();
		$nzshpcrt_variations->duplicate_variation_values($new_id, $product_id);
/*
		$variation_assocs = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_ASSOC." WHERE associated_id = ".$product_id, ARRAY_A);
		if(count($variation_assocs))foreach($variation_assocs as $variation_assoc){
			$wpdb->query("INSERT INTO ".WPSC_TABLE_VARIATION_ASSOC." VALUES ('', '".$variation_assoc['type']."', '".$variation_assoc['name']."', '".$new_id."', '".$variation_assoc['variation_id']."');");
		}
		
		$variation_combinations = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_COMBINATIONS." WHERE product_id = ".$product_id, ARRAY_A);
		if(count($variation_combinations))foreach($variation_combinations as $variation_combination){
			$variation_properties = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_PROPERTIES." WHERE id = ".$variation_combination['priceandstock_id'], ARRAY_A);
			exit('<pre>'.print_r($variation_properties, true).'</pre>');
			$wpdb->query("INSERT INTO ".WPSC_TABLE_VARIATION_PROPERTIES." VALUES ('', '".$new_id."', '".$variation_properties[0]['stock']."', '".$variation_properties[0]['price']."', '".$variation_properties[0]['weight']."', '".$variation_properties[0]['weight_unit']."', '".$variation_properties[0]['visibility']."', '".$variation_properties[0]['file']."');");
			$new_prop_id= $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` LIMIT 1");
			$wpdb->query("INSERT INTO ".WPSC_TABLE_VARIATION_COMBINATIONS." VALUES ('".$new_id."', '".$new_prop_id."', '".$variation_combination['value_id']."', '".$variation_combination['variation_id']."', '".$variation_combination['all_variation_ids']."');");			
		}
		
		$variation_values_assocs = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_VALUES_ASSOC." WHERE product_id = ".$product_id, ARRAY_A);
		if(count($variation_values_assocs))foreach($variation_values_assocs as $variation_values_assoc){
			$wpdb->query("INSERT INTO ".WPSC_TABLE_VARIATION_VALUES_ASSOC." VALUES ('', '".$new_id."', '".$variation_values_assoc['value_id']."', '".$variation_values_assoc['visible']."', '".$variation_values_assoc['variation_id']."');");
		}
		//end of variations
*/
		//Inserting duplicated category record.
		$category_assoc = $wpdb->get_col("SELECT `category_id` FROM ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." WHERE product_id = '".$product_id."'");
		$new_product_category = array();
		if (count($category_assoc) > 0) {
			foreach($category_assoc as $key => $category) {
				$new_product_category[] = "('".$new_id."','".$category."')";
				
				$check_existing = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category') AND `order` IN('0') LIMIT 1;",ARRAY_A);
				if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN ('$category') AND `product_id` IN ('$product_id') LIMIT 1")) {
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = '0' WHERE `category_id` IN ('$category') AND `product_id` IN ('$product_id') LIMIT 1;");
				} else {
					$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_ORDER."` (`category_id`, `product_id`, `order`) VALUES ('$category', '$product_id', 0)");
				}
				if($check_existing != null) {
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = (`order` + 1) WHERE `category_id` IN('$category') AND `product_id` NOT IN('$product_id') AND `order` < '0'");
				}
			}
			$wpdb->query("INSERT INTO ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." (product_id, category_id) VALUES ".implode(",",$new_product_category));
		}

	
		
	
	
		//Inserting duplicated meta info
		$meta_values = $wpdb->get_results("SELECT `meta_key`, `meta_value`, `custom` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id='".$product_id."' AND  `meta_key` NOT IN ('url_name') ", ARRAY_A);
		$new_meta_value = array();
		if (count($meta_values)>0) {
			foreach($meta_values as $key => $meta) {
				$new_meta_value[] = "('".$new_id."','".$meta['meta_key']."','".$meta['meta_value']."','".$meta['custom']."')";
			}
			$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (`product_id`, `meta_key`, `meta_value`, `custom`) VALUES ".implode(",",$new_meta_value));
		}
		
		
	
		$product_name = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$new_id' LIMIT 1");
		if($product_name != '') {
			$tidied_name = strtolower(trim($product_name));
			$url_name = sanitize_title($tidied_name);
			$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ('url_name') AND `meta_value` REGEXP '^($url_name){1}[[:digit:]]*$' ",ARRAY_A);
			$extension_number = '';
			if($similar_names['count'] > 0) {
				$extension_number = (int)$similar_names['max_number']+1;
			}
			$url_name .= $extension_number;
			add_product_meta($new_id, 'url_name', $url_name,true);
		}
		
		
		$tags =  wp_get_object_terms($product_id, 'product_tag', array('fields' => 'names'));
		wp_set_object_terms($new_id, $tags, 'product_tag');
		            
		//Inserting duplicated image info
		$image_values = $wpdb->get_results("SELECT `image`, `width`, `height`, `image_order`, `meta` FROM ".WPSC_TABLE_PRODUCT_IMAGES." WHERE product_id='".$product_id."'", ARRAY_A);
		$new_image_value = array();
		if (count($image_values)>0){
			foreach($image_values as $key => $image) {
			  if($image['image'] != '') {
			    if(is_numeric($image['width']) && is_numeric($image['height'])) {
			      $image['width'] = absint($image['width']);
			      $image['height'] = absint($image['height']);
			    } else {
			      $image['width'] = 'null';
			      $image['height'] = 'null';
			    }
					$new_image_value[] = "('".$new_id."','".$image['image']."',".$image['width'].",".$image['height'].",'".$image['image_order']."','".$image['meta']."')";
				}
			}
			if(count($new_image_value) > 0) {
				$new_image_value = implode(",", $new_image_value);
				$sql = "INSERT INTO ".WPSC_TABLE_PRODUCT_IMAGES." (`product_id`, `image`, `width`, `height`, `image_order`, `meta`) VALUES ".$new_image_value;
				$wpdb->query($sql);
			}
		}
		
	  $duplicated = true;
	}	
	
	$sendback = wp_get_referer();
	if ( isset($duplicated) ) {
		$sendback = add_query_arg('duplicated', (int)$duplicated, $sendback);
	}
	wp_redirect($sendback);
	exit();
}

if ($_GET['wpsc_admin_action'] == 'duplicate_product') {
	add_action('admin_init', 'wpsc_duplicate_product');
}
 
 
 
function wpsc_purchase_log_csv() {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();
  if(($_GET['rss_key'] == 'key') && is_numeric($_GET['start_timestamp']) && is_numeric($_GET['end_timestamp']) && ($user_level >= 7)) {
  //exit('in use');
    $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
    $form_data = $wpdb->get_results($form_sql,ARRAY_A);
    
    $start_timestamp = $_GET['start_timestamp'];
    $end_timestamp = $_GET['end_timestamp'];
    $data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC",ARRAY_A);
         // exit('<pre>'.print_r($data, true).'</pre>');

         
    header('Content-Type: text/csv');
    header('Content-Disposition: inline; filename="Purchase Log '.date("M-d-Y", $start_timestamp).' to '.date("M-d-Y", $end_timestamp).'.csv"');
    
    foreach((array)$data as $purchase) {
      $country_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
   
      $output .= "\"".$purchase['totalprice'] ."\",";
                
      foreach((array)$form_data as $form_field) {
        $collected_data_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".$form_field['id']."' LIMIT 1";
        $collected_data = $wpdb->get_results($collected_data_sql,ARRAY_A);
        $collected_data = $collected_data[0];
        $output .= "\"".$collected_data['value']."\",";
			}
        
      if(get_option('payment_method') == 2) {
        $gateway_name = '';
        foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
          if($purchase['gateway'] != 'testmode') {
            if($gateway['internalname'] == $purchase['gateway'] ) {
              $gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
					}
				}
        $output .= "\"". $gateway_name ."\",";
			}
              
      if($purchase['processed'] < 1) {
        $purchase['processed'] = 1;
			}
      $stage_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_STATUSES."` WHERE `id`='".$purchase['processed']."' AND `active`='1' LIMIT 1";
      $stage_data = $wpdb->get_results($stage_sql,ARRAY_A);
              
      $output .= "\"". $stage_data[0]['name'] ."\",";
      
      $output .= "\"". date("jS M Y",$purchase['date']) ."\"";
      
      $cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase['id']."";
      $cart = $wpdb->get_results($cartsql,ARRAY_A) ; 
      //exit(nl2br(print_r($cart,true)));
      
      foreach((array)$cart as $item) {
        $output .= ",";
        $product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$item['prodid']." LIMIT 1",ARRAY_A);    
        $skusql = "SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key`= 'sku' AND `product_id` = ".$item['prodid'];  
      //  exit($skusql);
        $skuvalue = $wpdb->get_var($skusql);  
        $variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$item['id']."'";
        $variation_data = $wpdb->get_results($variation_sql,ARRAY_A);
         $variation_count = count($variation_data);
          if($variation_count >= 1) {
            $variation_list = " (";
            $i = 0;
            foreach($variation_data as $variation) {
              if($i > 0) {
                $variation_list .= ", ";
							}
              $value_id = $variation['value_id'];
              $value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
              $variation_list .= $value_data[0]['name'];              
              $i++;
						}
            $variation_list .= ")";
					}
        
      //  exit('<pre>'.print_r($item,true).'</pre>');
        $output .= "\"".$item['quantity']." ".str_replace('"', '\"',$product['name']).$variation_list."\"";
        $output .= ",".$skuvalue;
			}
      $output .= "\n"; // terminates the row/line in the CSV file
		}
    echo $output;
    exit();
	}
}
 if($_REQUEST['wpsc_admin_action'] == 'wpsc_downloadcsv') {
	add_action('admin_init', 'wpsc_purchase_log_csv');
}


function wpsc_admin_ajax() {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();  
  if(is_numeric($_POST['catid'])) {
		/* fill category form */
		echo nzshpcrt_getcategoryform($_POST['catid']);
		exit();
	} else if(is_numeric($_POST['brandid'])) {
		/* fill brand form */   
		echo nzshpcrt_getbrandsform($_POST['brandid']);
		exit();
	} else if(is_numeric($_POST['variation_id'])) {  
		echo nzshpcrt_getvariationform($_POST['variation_id']);
		exit();
	}
	
	
	if ($_POST['action'] == 'product-page-order'){
		$order = $_POST['order'];
		if(!isset($order[0])) {
			$order = $order['normal'];
		} else {
			$order = $order[0];
		}
		
		$order = array_unique(explode(',', $order));
		update_option('wpsc_product_page_order', $order);
		exit(print_r($order,1));
	}
	
		
	if(($_POST['save_image_upload_state'] == "true") && is_numeric($_POST['image_upload_state'])) {
		//get_option('wpsc_image_upload_state');
		$upload_state = (int)(bool)$_POST['image_upload_state'];
		update_option('wpsc_use_flash_uploader', $upload_state);
		exit("done");
	}
      
	if(($_POST['remove_variation_value'] == "true") && is_numeric($_POST['variation_value_id'])) {
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `value_id` = '".(int)$_POST['variation_value_id']."'");
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '".(int)$_POST['variation_value_id']."' LIMIT 1");
		exit();
	}
		

	if(($_POST['edit_variation_value_list'] == 'true') && is_numeric($_POST['variation_id']) && is_numeric($_POST['product_id'])) {
		$variation_id = (int)$_POST['variation_id'];
		$product_id = (int)$_POST['product_id'];
		$variations_processor = new nzshpcrt_variations();
		$variation_values = $variations_processor->falsepost_variation_values($variation_id);
		if(is_array($variation_values)) {
			//echo(print_r($variation_values,true));
			$check_variation_added = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}') AND `variation_id` IN ('{$variation_id}') LIMIT 1");
			//exit("<pre>".print_r($variation_values,true)."<pre>");
			if($check_variation_added == null) {
				$variations_processor->add_to_existing_product($product_id,$variation_values);			
			}
			echo $variations_processor->display_attached_variations($product_id);
			echo $variations_processor->variations_grid_view($product_id); 
		} else {
			echo "false";
		}
		exit();
	}
		

            
	if(($_POST['remove_form_field'] == "true") && is_numeric($_POST['form_id'])) {
		//exit(print_r($user,true));
		if(current_user_can('level_7')) {
			$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `active` = '0' WHERE `id` = %d LIMIT 1 ;", $_POST['form_id']));
			exit(' ');
		}
	}
	
	
	if($_POST['hide_ecom_dashboard'] == 'true') {
		require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss('http://www.instinct.co.nz/feed/');				
		$rss->items = array_slice($rss->items, 0, 5);
		$rss_hash = sha1(serialize($rss->items));				
		update_option('wpsc_ecom_news_hash', $rss_hash);
		exit(1);
	}			
	
	if(($_POST['remove_meta'] == 'true') && is_numeric($_POST['meta_id'])) {
		$meta_id = (int)$_POST['meta_id'];
		$selected_meta = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `id` IN('{$meta_id}') ",ARRAY_A);
		if($selected_meta != null) {
			if($wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `id` IN('{$meta_id}')  LIMIT 1")) {
				echo $meta_id;
				exit();
			}
		}
		echo 0;
		exit();
	}
		
	if(($_REQUEST['log_state'] == "true") && is_numeric($_POST['id']) && is_numeric($_POST['value'])) {
		$newvalue = $_POST['value'];
		if ($_REQUEST['suspend']=='true'){
			if ($_REQUEST['value']==1){
				wpsc_member_dedeactivate_subscriptions($_POST['id']);
			} else {
				wpsc_member_deactivate_subscriptions($_POST['id']);
			}
			exit();
		} else {
		
			$log_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '".$_POST['id']."' LIMIT 1",ARRAY_A);  
			if (($newvalue==2) && function_exists('wpsc_member_activate_subscriptions')){
				wpsc_member_activate_subscriptions($_POST['id']);
			}
			
			$update_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '".$newvalue."' WHERE `id` = '".$_POST['id']."' LIMIT 1";  
			$wpdb->query($update_sql);
			//echo("/*");
			if(($newvalue > $log_data['processed']) && ($log_data['processed'] < 2)) {
				transaction_results($log_data['sessionid'],false);
			}      
			//echo("*/");
			$stage_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_STATUSES."` WHERE `id`='".$newvalue."' AND `active`='1' LIMIT 1";
			$stage_data = $wpdb->get_row($stage_sql,ARRAY_A);
					
			echo "document.getElementById(\"form_group_".$_POST['id']."_text\").innerHTML = '".$stage_data['name']."';\n";
			echo "document.getElementById(\"form_group_".$_POST['id']."_text\").style.color = '#".$stage_data['colour']."';\n";
			
			
			$year = date("Y");
			$month = date("m");
			$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
			$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
			
			echo "document.getElementById(\"log_total_month\").innerHTML = '".addslashes(nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1))."';\n";
			echo "document.getElementById(\"log_total_absolute\").innerHTML = '".addslashes(nzshpcrt_currency_display(admin_display_total_price(),1))."';\n";
			exit();
		}
	}
  
	if(($_POST['list_variation_values'] == "true")) {
   	// retrieve the forms for associating variations and their values with products
		$variation_processor = new nzshpcrt_variations();
		
		$variations_selected = array();
    	foreach((array)$_POST['variations'] as $variation_id => $checked) {
    		$variations_selected[] = (int)$variation_id;
    	}

    	if(is_numeric($_POST['product_id']) && ($_POST['product_id'] > 0)) {
      		$product_id = absint($_POST['product_id']);
					$selected_price = (float)$_POST['selected_price'];
      		
       		// variation values housekeeping
      		$completed_variation_values = $variation_processor->edit_product_values($product_id,$_POST['edit_var_val'], $selected_price);
      

      		// get all the currently associated variations from the database
      		$associated_variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}')", ARRAY_A);
      
      		$variations_still_associated = array();
      		foreach((array)$associated_variations as $associated_variation) {
			  	// remove variations not checked that are in the database
        		if(array_search($associated_variation['variation_id'], $variations_selected) === false) {
          			$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `id` = '{$associated_variation['id']}' LIMIT 1");
          			$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = '{$product_id}' AND `variation_id` = '{$associated_variation['variation_id']}' ");
        		} else {
          			// make an array for adding in the variations next step, for efficiency
          			$variations_still_associated[] = $associated_variation['variation_id'];
        		}
      		}
       
					foreach((array)$variations_selected as $variation_id) {
			  	// add variations not already in the database that have been checked.
        		$variation_values = $variation_processor->falsepost_variation_values($variation_id);
        		if(array_search($variation_id, $variations_still_associated) === false) {
      	  			$variation_processor->add_to_existing_product($product_id,$variation_values);
        		}
      		}
      		//echo "/* ".print_r($variation_values,true)." */\n\r";
					echo "edit_variation_combinations_html = \"".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_grid_view($product_id,  (array)$completed_variation_values)))."\";\n";

    	} else {
      		if(count($variations_selected) > 0) {
        		// takes an array of variations, returns a form for adding data to those variations.
        		if((float)$_POST['selected_price'] > 0) {
          			$selected_price = (float)$_POST['selected_price'];
        		}
						$limited_stock = false;
        		if($_POST['limited_stock'] == 'true') {
							$limited_stock = true;
							if(isset($_POST['current_stock'])) $current_stock = $_POST['current_stock'];
        		}
        		
        		$selected_variation_values = array();
        		foreach($_POST['edit_var_val'] as $variation_value_array) {
							//echo "/* ".print_r($variation_value_array,true)." */\n\r";
        		  $selected_variation_values = array_merge(array_keys($variation_value_array), $selected_variation_values);
        		}
        		
						////echo "/* ".print_r($selected_variation_values,true)." */\n\r";
						echo "edit_variation_combinations_html = \"".__('Edit Variation Set', 'wpsc')."<br />".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_grid_view(0, (array)$variations_selected, (array)$selected_variation_values, $selected_price, $limited_stock,$current_stock)))."\";\n";

      		} else {
        		echo "edit_variation_combinations_html = \"\";\n";
      		}
		}
		exit();
	}
	
    
	if(isset($_POST['language_setting']) && ($_GET['page'] = WPSC_DIR_NAME.'/wpsc-admin/display-options.page.php')) {
		if($user_level >= 7) {
			update_option('language_setting', $_POST['language_setting']);
		}
	}
}


function wpsc_admin_sale_rss() {
  global $wpdb;
  if(($_GET['rss'] == "true") && ($_GET['rss_key'] == 'key') && ($_GET['action'] == "purchase_log")) {
    $sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date`!='' ORDER BY `date` DESC";
    $purchase_log = $wpdb->get_results($sql,ARRAY_A);
    header("Content-Type: application/xml; charset=UTF-8"); 
    header('Content-Disposition: inline; filename="WP_e-Commerce_Purchase_Log.rss"');
    $output = '';
    $output .= "<?xml version='1.0'?>\n\r";
    $output .= "<rss version='2.0'>\n\r";
    $output .= "  <channel>\n\r";
    $output .= "    <title>WP e-Commerce Product Log</title>\n\r";
    $output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php</link>\n\r";
    $output .= "    <description>This is the WP e-Commerce Product Log RSS feed</description>\n\r";
    $output .= "    <generator>WP e-Commerce Plugin</generator>\n\r";
    
    foreach((array)$purchase_log as $purchase) {
      $purchase_link = get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php&amp;purchaseid=".$purchase['id'];
      $output .= "    <item>\n\r";
      $output .= "      <title>Purchase No. ".$purchase['id']."</title>\n\r";
      $output .= "      <link>$purchase_link</link>\n\r";
      $output .= "      <description>This is an entry in the purchase log.</description>\n\r";
      $output .= "      <pubDate>".date("r",$purchase['date'])."</pubDate>\n\r";
      $output .= "      <guid>$purchase_link</guid>\n\r";
      $output .= "    </item>\n\r";
		}
    $output .= "  </channel>\n\r";
    $output .= "</rss>";
    echo $output;
    exit();
	}
}

function wpsc_swfupload_images() {
	global $wpdb, $current_user;
	// xdebug_start_trace();
	$file = $_FILES['async-upload'];
	$product_id = absint($_POST['product_id']);
	$nonce = $_POST['_wpnonce'];
  $output = '';
	// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead, code is from wp-admin/async-upload.php
	if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) ) {
		$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
	} else if ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) ) {
		$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
	}
	unset($current_user);
	require_once(ABSPATH . 'wp-admin/admin.php');

	if(!current_user_can('upload_files') ) {
			exit("status=-1;\n");
	}
	
	if(!wp_verify_nonce($nonce, 'product-swfupload') ) {
		exit("status=-1;\n");
	}

	
	if(function_exists('gold_shpcrt_display_gallery')) {
		// if more than one image is permitted
		$existing_image_data = $wpdb->get_row("SELECT COUNT(*) AS `count`,  MAX(image_order) AS `order` FROM ".WPSC_TABLE_PRODUCT_IMAGES." WHERE `product_id`='".absint($product_id)."' AND `image` NOT IN ('')", ARRAY_A);
		$order = (int)$existing_image_data['order'];
		$count = $existing_image_data['count'];
		
		$previous_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".absint($product_id)."' LIMIT 1");
		//echo "/* $count $previous_image */ ";
		if(($count >  0) && ( (strlen($previous_image) > 0) || (absint($product_id) < 1) ) ) {
			// if there is more than one image
			$success = move_uploaded_file($file['tmp_name'], WPSC_IMAGE_DIR.basename($file['name']));
			if ($product_id == '') {
				copy(WPSC_IMAGE_DIR.basename($file['name']),WPSC_THUMBNAIL_DIR.basename($file['name']));
			}
			$order++;
			if ($success) {
				$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` ( `product_id` , `image` , `width` , `height` , `image_order` ) VALUES( '$product_id','".basename($file['name'])."', '0', '0',  '$order')");
				$id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` LIMIT 1");

				$src = $file['name'];
				$output .= "upload_status=1;\n";
				$output .= "image_src='".$src."';\n";
				$output .= "image_id='$id';\n";
				$output .= "product_id='$product_id';\n";
				$output .= "replace_existing=0;";
			} else {
				$output .= "status=0;\n";
			}
		} else {
			// if thereare no images
			if($product_id > 0) {
				$image_data = wpsc_item_process_image($product_id, $file['tmp_name'], $file['name'], 0, 0, 1, true);
				$src = $image_data['filename'];
				$image_id = $image_data['image_id'];
			} else {
				$success = move_uploaded_file($file['tmp_name'], WPSC_IMAGE_DIR.basename($file['name']));
				copy(WPSC_IMAGE_DIR.basename($file['name']),WPSC_THUMBNAIL_DIR.basename($file['name']));
				$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` ( `product_id` , `image` , `width` , `height` , `image_order` ) VALUES( '$product_id','".basename($file['name'])."', '0', '0', '0')");
				$src = basename($file['name']);
			}
			
			
			if($src != null) {
				if($image_id < 1) {
					$image_id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` LIMIT 1");
					
					if($product_id > 0) {
						$previous_image = $wpdb->get_var("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$image_id}' WHERE `id`='{$product_id}' LIMIT 1");
					}
				}
				$output .= "upload_status=1;\n";
				$output .= "image_src='".$src."';\n";
				$output .= "image_id='$image_ids';\n";
				$output .= "product_id='$product_id';\n";
				$output .= "replace_existing=1;";
			} else {
				$output .= "status=0;\n";
			}
		}
	} else {
		// Otherwise...
		$previous_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1");
		$image_replaced = false;
		if($product_id > 0) {
			$image_data = wpsc_item_process_image($product_id, $file['tmp_name'], $file['name'], 0, 0, 1, true);
			$src = $image_data['filename'];
			$image_id = $image_data['image_id'];
		} else {
			$success = move_uploaded_file($file['tmp_name'], WPSC_IMAGE_DIR.basename($file['name']));
			copy(WPSC_IMAGE_DIR.basename($file['name']),WPSC_THUMBNAIL_DIR.basename($file['name']));
			$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` ( `product_id` , `image` , `width` , `height` , `image_order` ) VALUES( '$product_id','".basename($file['name'])."', '0', '0', '0')");
			$src = basename($file['name']);
		}
		//$src = wpsc_item_process_image($product_id, $file['tmp_name'], $file['name']);
		if($src != null) {
			if($image_id < 1) {
				$image_id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` LIMIT 1");
				if($product_id > 0) {
					$previous_image = $wpdb->get_var("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$image_id}' WHERE `id`='{$product_id}' LIMIT 1");
				}
			}
			$image_replaced = true;
			$output .= "upload_status=1;\n";
			$output .= "image_src='".$src."';\n";
			$output .= "image_id='$image_id';\n";
			$output .= "product_id='$product_id';\n";
			if($image_replaced == true) {
				$output .= "replace_existing=1;\n";
			} else {
				$output .= "replace_existing=0;\n";
			}
		} else {
			$output .= "status=0;\n";
		}
	}

	exit($output);
}


function wpsc_display_invoice() {

	global $body_id;
	
	$body_id = 'wpsc-packing-slip';
  $purchase_id = (int)$_GET['purchaselog_id'];
  include_once(WPSC_FILE_PATH."/admin-form-functions.php");
  // echo "testing";
	require_once(ABSPATH.'wp-admin/includes/media.php');
	wp_iframe('wpsc_packing_slip', $purchase_id);  
  //wpsc_packing_slip($purchase_id);
  exit();
}
 if($_REQUEST['wpsc_admin_action'] == 'wpsc_display_invoice') {
	add_action('admin_init', 'wpsc_display_invoice');
}
 

function wpsc_save_inline_price() {
	global $wpdb;
	$pid = $_POST['id'];
	$new_price = $_POST['value'];
	$new_price1 = str_replace('$','',$new_price);
	$wpdb->query("UPDATE ".WPSC_TABLE_PRODUCT_LIST." SET price='$new_price1' WHERE id='$pid'");
	exit($new_price);
}

if($_GET['inline_price']=='true') {
	add_action('admin_init', 'wpsc_save_inline_price', 0);
}


/**
 * Purchase log ajax code starts here
*/
function wpsc_purchlog_resend_email(){
	global $wpdb;
	$siteurl = get_option('siteurl');
	$log_id = $_GET['email_buyer_id'];
	if(is_numeric($log_id)) {
	
		$selectsql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`= ".$log_id." LIMIT 1";

		$purchase_log = $wpdb->get_row($selectsql,ARRAY_A) ;
		
		if(($purchase_log['gateway'] == "testmode") && ($purchase_log['processed'] < 2))  {
			$message = get_option("wpsc_email_receipt");
			$message_html = "<h2  style='font-size:16px;font-weight:bold;color:#000;border:0px;padding-top: 0px;' >".__('Your Order', 'wpsc')."</h2>";
		} else {
			$message = get_option("wpsc_email_receipt");
			$message_html = $message;
		}
		
		$order_url = $siteurl."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php&amp;purchcaseid=".$purchase_log['id'];

		$cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase_log['id']."";
		$cart = $wpdb->get_results($cartsql,ARRAY_A);
		if($purchase_log['shipping_country'] != '') {
			$billing_country = $purchase_log['billing_country'];
			$shipping_country = $purchase_log['shipping_country'];
		} else {
			$country = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$purchase_log['id']." AND `form_id` = '".get_option('country_form_field')."' LIMIT 1",ARRAY_A);
			$billing_country = $country[0]['value'];
			$shipping_country = $country[0]['value'];
		}
	
		$email_form_field = $wpdb->get_results("SELECT `id`,`type` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
		$email_address = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$purchase_log['id']." AND `form_id` = '".$email_form_field[0]['id']."' LIMIT 1",ARRAY_A);
		$email = $email_address[0]['value'];
	
		$previous_download_ids = array(0); 
	
		if(($cart != null)) {
			foreach($cart as $row) {
				$link = "";
				$productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$row['prodid']."";
				$product_data = $wpdb->get_results($productsql,ARRAY_A) ;
				
				if($product_data[0]['file'] > 0) {
					if($purchase_log['email_sent'] != 1) {
						$wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `active`='1' WHERE `fileid`='".$product_data[0]['file']."' AND `purchid` = '".$purchase_log['id']."' LIMIT 1");
					}
					
					if (($purchase_log['processed'] >= 2)) {
						$download_data = $wpdb->get_results("SELECT *
						FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` INNER JOIN `".WPSC_TABLE_PRODUCT_FILES."`
						ON `".WPSC_TABLE_DOWNLOAD_STATUS."`.`fileid` = `".WPSC_TABLE_PRODUCT_FILES."`.`id`
						WHERE `".WPSC_TABLE_DOWNLOAD_STATUS."`.`active`='1'
						AND `".WPSC_TABLE_DOWNLOAD_STATUS."`.`purchid`='".$purchase_log['id']."'
						AND (
							`".WPSC_TABLE_DOWNLOAD_STATUS."`.`cartid` = '".$row['id']."'
							OR (
								`".WPSC_TABLE_DOWNLOAD_STATUS."`.`cartid` IS NULL
								AND `".WPSC_TABLE_DOWNLOAD_STATUS."`.`fileid` = '{$product_data['file']}'
							)
						)
						 AND `".WPSC_TABLE_DOWNLOAD_STATUS."`.`id` NOT IN ('".implode("','",$previous_download_ids)."')",ARRAY_A);
						$link=array();
							//exit('IM HERE'.$errorcode.'<pre>'.print_r($download_data).'</pre>');
						if(sizeof($download_data) != 0) {
							foreach($download_data as $single_download){
								if($single_download['uniqueid'] == null){// if the uniqueid is not equal to null, its "valid", regardless of what it is
									$link[] = array("url"=>$siteurl."?downloadid=".$single_download['id'], "name" =>$single_download["filename"]);	
								} else {
									$link[] = array("url"=>$siteurl."?downloadid=".$single_download['uniqueid'], "name" =>$single_download["filename"]);
								}
              }
						}
						$previous_download_ids[] = $download_data['id'];
						$order_status= 4;
					}
				}
				do_action('wpsc_confirm_checkout', $purchase_log['id']);
		
				$shipping = nzshpcrt_determine_item_shipping($row['prodid'], $row['quantity'], $shipping_country);
				if (isset($_SESSION['quote_shipping'])){
					$shipping = $_SESSION['quote_shipping'];
				}
				$total_shipping += $shipping;
		
				if($product_data[0]['special']==1) {
					$price_modifier = $product_data[0]['special_price'];
				} else {
					$price_modifier = 0;
				}
		
				$total+=($row['price']*$row['quantity']);
				$message_price = nzshpcrt_currency_display(($row['price']*$row['quantity']), $product_data[0]['notax'], true);

				$shipping_price = nzshpcrt_currency_display($shipping, 1, true);
				
				$variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$row['id']."'";
				$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
				$variation_count = count($variation_data);
		
				if($variation_count > 1) {
					$variation_list = " (";
		
					if($purchase['gateway'] != 'testmode') {
						if($gateway['internalname'] == $purch_data[0]['gateway'] ) {
							$gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
							}
							$i = 0;
							foreach($variation_data as $variation) {
								if($i > 0) {
									$variation_list.= ", ";
								}
								
								$value_id = $variation['value_id'];
								$value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
								$variation_list.= $value_data[0]['name'];
								$i++;	
							}
							$variation_list .= ")";
						} else {
							if($variation_count == 1) {
								$value_id = $variation_data[0]['value_id'];
								$value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
								$variation_list = " (".$value_data[0]['name'].")";
							} else {
								$variation_list = '';
							}
						}
			
						if($link != '') {
							$product_list .= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price ." ".__('Click to download', 'wpsc').":";
							$product_list_html .= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price ."&nbsp;&nbsp;".__('Click to download', 'wpsc').":\n\r";
							foreach($link as $single_link) {
								$product_list .= "\n\r ".$single_link["name"].": ".$single_link["url"]."\n\r";
								$product_list_html .= "<a href='".$single_link["url"]."'>".$single_link["name"]."</a>\n";
							}
						} else {
							$plural = '';
							
							if($row['quantity'] > 1) {
								$plural = "s";
							  }

							$product_list.= " - ".$row['quantity']." ". $product_data[0]['name'].$variation_list ."  ". $message_price ."\n";
							if ($shipping > 0) $product_list .= " - ". __('Shipping', 'wpsc').":".$shipping_price ."\n\r";
							$product_list_html.= " - ".$row['quantity']." ". $product_data[0]['name'].$variation_list ."  ". $message_price ."\n";
							if ($shipping > 0) $product_list_html .= " - ". __('Shipping', 'wpsc').":".$shipping_price ."\n\r";
						}
						
						$report.= " - ". $product_data[0]['name'] .$variation_list."  ".$message_price ."\n";
				}
				
				if($purchase_log['discount_data'] != '') {
					$coupon_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` WHERE coupon_code='".$wpdb->escape($purchase_log['discount_data'])."' LIMIT 1",ARRAY_A);
					if($coupon_data['use-once'] == 1) {
						$wpdb->query("UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `active`='0', `is-used`='1' WHERE `id`='".$coupon_data['id']."' LIMIT 1");
					}
				}
				//$wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `active`='1' WHERE `fileid`='".$product_data[0]['file']."' AND `purchid` = '".$purchase_log['id']."' LIMIT 1");
				$total_shipping += $purchase_log['base_shipping'];

				$total = (($total+$total_shipping) - $purchase_log['discount_value']);
			// $message.= "\n\r";
			$product_list.= "Your Purchase No.: ".$purchase_log['id']."\n\r";
				if($purchase_log['discount_value'] > 0) {
					$discount_email.= __('Discount', 'wpsc').": ".nzshpcrt_currency_display($purchase_log['discount_value'], 1, true)."\n\r";
				}
				$total_shipping_email.= __('Total Shipping', 'wpsc').": ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
				$total_price_email.= __('Total', 'wpsc').": ".nzshpcrt_currency_display($total,1,true)."\n\r";
				$product_list_html.= "Your Purchase No.: ".$purchase_log['id']."\n\n\r";
				if($purchase_log['discount_value'] > 0) {
					$discount_html.= __('Discount', 'wpsc').": ".nzshpcrt_currency_display($purchase_log['discount_value'], 1, true)."\n\r";
				}
				$total_shipping_html.= __('Total Shipping', 'wpsc').": ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
				$total_price_html.= __('Total', 'wpsc').": ".nzshpcrt_currency_display($total, 1,true)."\n\r";
				if(isset($_GET['ti'])) {
					$message.= "\n\r".__('Your Transaction ID', 'wpsc').": " . $_GET['ti'];
					$message_html.= "\n\r".__('Your Transaction ID', 'wpsc').": " . $_GET['ti'];
					$report.= "\n\r".__('Transaction ID', 'wpsc').": " . $_GET['ti'];
				} else {
					$report_id = "Purchase No.: ".$purchase_log['id']."\n\r";
				}
				
				
				
				$message = str_replace('%product_list%',$product_list,$message);
        $message = str_replace('%total_shipping%',$total_shipping_email,$message);
        $message = str_replace('%total_price%',$total_price_email,$message);
        //$message = str_replace('%order_status%',get_option('blogname'),$message);
        $message = str_replace('%shop_name%',get_option('blogname'),$message);
        
        $report = str_replace('%product_list%',$report_product_list,$report);
        $report = str_replace('%total_shipping%',$total_shipping_email,$report);
        $report = str_replace('%total_price%',$total_price_email,$report);
        $report = str_replace('%shop_name%',get_option('blogname'),$report);
        
        $message_html = str_replace('%product_list%',$product_list_html,$message_html);
        $message_html = str_replace('%total_shipping%',$total_shipping_html,$message_html);
        $message_html = str_replace('%total_price%',$total_price_email,$message_html);
        $message_html = str_replace('%shop_name%',get_option('blogname'),$message_html);
 
				
			//	exit($message_html);
				if(($email != '')) {
 					add_filter('wp_mail_from', 'wpsc_replace_reply_address', 0);
 					add_filter('wp_mail_from_name', 'wpsc_replace_reply_name', 0);
					if($purchase_log['processed'] < 2) {
						$payment_instructions = strip_tags(get_option('payment_instructions'));
						$message = __('Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc') . "\n\r" . $payment_instructions ."\n\r". $message;
						$resent = (bool)wp_mail($email, __('Order Pending: Payment Required', 'wpsc'), $message);
						$sent = 1;
					} else {
						$resent = (bool)wp_mail($email, __('Purchase Receipt', 'wpsc'), $message);
						$sent = 1;
					}
					//echo "$message<br />";
					//exit($email);
					remove_filter('wp_mail_from_name', 'wpsc_replace_reply_name');
					remove_filter('wp_mail_from', 'wpsc_replace_reply_address');
				}
		}
	
}
	$sendback = wp_get_referer();

	if ( isset($sent) ) {
		$sendback = add_query_arg('sent', $sent, $sendback);
	}
	wp_redirect($sendback);
	exit();
}



if(isset($_REQUEST['email_buyer_id']) && is_numeric($_REQUEST['email_buyer_id'])) {
	add_action('admin_init', 'wpsc_purchlog_resend_email');
} 
function wpsc_purchlog_clear_download_items(){
	global $wpdb;
//exit('Just about to redirect');
	if(is_numeric($_GET['purchaselog_id'])) {
	  $purchase_id = (int)$_GET['purchaselog_id'];
	  $downloadable_items = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `purchid` IN ('$purchase_id')", ARRAY_A);
	  
	  $clear_locks_sql = "UPDATE`".WPSC_TABLE_DOWNLOAD_STATUS."` SET `ip_number` = '' WHERE `purchid` IN ('$purchase_id')";
	  $wpdb->query($clear_locks_sql);
	  $cleared =true;
	  
		$email_form_field = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1");
		$email_address = $wpdb->get_var("SELECT `value` FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`='{$purchase_id}' AND `form_id` = '{$email_form_field}' LIMIT 1");
		
		foreach((array)$downloadable_items as $downloadable_item) {
		  $download_links .= $siteurl."?downloadid=".$downloadable_item['uniqueid']. "\n";
		}
		
		
		wp_mail($email_address, __('The administrator has unlocked your file', 'wpsc'), str_replace("[download_links]", $download_links, __('Dear CustomerWe are pleased to advise you that your order has been updated and your downloads are now active.Please download your purchase using the links provided below.[download_links]Thank you for your custom.', 'wpsc')), "From: ".get_option('return_email')."");
	  

	$sendback = wp_get_referer();

	if ( isset($cleared) ) {
		$sendback = add_query_arg('cleared', $cleared, $sendback);
	}
	wp_redirect($sendback);
	exit();
	}

}
if($_REQUEST['wpsc_admin_action'] == 'clear_locks') {
	add_action('admin_init', 'wpsc_purchlog_clear_download_items');
}
 
 //call to search purchase logs
 
  function wpsc_purchlog_search_by(){
//  exit('<pre>'.print_r($_POST,true).'</pre>');
 	wpsc_search_purchlog_view($_POST['purchlogs_searchbox']);
 } 
 
 if($_REQUEST['wpsc_admin_action'] == 'purchlogs_search') {
	add_action('admin_init', 'wpsc_purchlog_search_by');
}
 //call to change view for purchase log
 
 function wpsc_purchlog_filter_by(){
 	//exit('filter is triggered'.print_r($_POST, true));
 	wpsc_change_purchlog_view($_POST['view_purchlogs_by'], $_POST['view_purchlogs_by_status']);
 } 
 
 if($_REQUEST['wpsc_admin_action'] == 'purchlog_filter_by') {
	add_action('admin_init', 'wpsc_purchlog_filter_by');
}
 //bulk actions for purchase log
function wpsc_purchlog_bulk_modify(){
	if($_POST['purchlog_multiple_status_change'] != -1){
		if(is_numeric($_POST['purchlog_multiple_status_change']) && $_POST['purchlog_multiple_status_change'] != 'delete'){
			foreach((array)$_POST['purchlogids'] as $purchlogid){
				//exit('<pre>'.print_r($purchlogid,true).'</pre>');
				wpsc_purchlog_edit_status($purchlogid, $_POST['purchlog_multiple_status_change']);
				$updated++;
			}
			
		}elseif($_POST['purchlog_multiple_status_change'] == 'delete'){
			foreach((array)$_POST['purchlogids'] as $purchlogid){
		
				wpsc_delete_purchlog($purchlogid);
				$deleted++;
			}
		}
		
	}
	$sendback = wp_get_referer();
	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if ( isset($deleted) ) {
		$sendback = add_query_arg('deleted', $deleted, $sendback);
	}
	if ( isset($_POST['view_purchlogs_by']) ) {
		$sendback = add_query_arg('view_purchlogs_by', $_POST['view_purchlogs_by'], $sendback);
  }
	if ( isset($_POST['view_purchlogs_by_status']) ) {
		$sendback = add_query_arg('view_purchlogs_by_status', $_POST['view_purchlogs_by_status'], $sendback);
  }
	wp_redirect($sendback);
	exit();
}

if($_REQUEST['wpsc_admin_action2'] == 'purchlog_bulk_modify') {
	add_action('admin_init', 'wpsc_purchlog_bulk_modify');
}
//edit purchase log status function
function wpsc_purchlog_edit_status($purchlog_id='', $purchlog_status='') {
	global $wpdb;
	if(($purchlog_id =='') && ($purchlog_status == '')){
		$purchlog_id = absint($_POST['purchlog_id']);
		$purchlog_status = absint($_POST['purchlog_status']);
	}
		
	$log_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '{$purchlog_id}' LIMIT 1",ARRAY_A);
	if (($purchlog_status==2) && function_exists('wpsc_member_activate_subscriptions')){
		do_action('wpsc_submit_checkout', array("purchase_log_id" => $purchase_log_id, "our_user_id" => $our_user_id));
		wpsc_member_activate_subscriptions($_POST['id']);
	}

	// if the order is marked as failed, remove the claim on the stock
	if($purchlog_status == 5) {
		$wpdb->query("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` = '{$purchlog_id}' AND `cart_submitted` = '1'");
		//echo "DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` = '{$purchlog_id}' AND `cart_submitted` = '1'";
	}

	$wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed='{$purchlog_status}' WHERE id='{$purchlog_id}'");

	if(($purchlog_status > $log_data['processed']) && ($log_data['processed'] <= 2)) {
		transaction_results($log_data['sessionid'],false);
	}
//	exit("1");
}

if($_REQUEST['wpsc_admin_action'] == 'purchlog_edit_status') {
	add_action('admin_init', 'wpsc_purchlog_edit_status');
}
/*
SELECT DISTINCT `products`.*, `category`.`category_id`,`order`.`order`, IF(ISNULL(`order`.`order`), 0, 1) AS `order_state` FROM `wp_wpsc_product_list` AS `products` LEFT JOIN `wp_wpsc_item_category_assoc` AS `category` ON `products`.`id` = `category`.`product_id` LEFT JOIN `wp_wpsc_product_order` AS `order` ON ( ( `products`.`id` = `order`.`product_id` ) AND ( `category`.`category_id` = `order`.`category_id` ) ) WHERE `products`.`publish`='1' AND `products`.`active` = '1' AND `category`.`category_id` IN ('3') ORDER BY `order_state` DESC, `products`.`id` DESC,`order`.`order` ASC LIMIT 0, 8
*/
function wpsc_save_product_order() {
  global $wpdb;
	if(is_numeric($_POST['category_id']) ) {
		$category_id = absint($_POST['category_id']);
		$products = $_POST['product'];
		$order=1;
	//	echo '/*<pre>'.print_r($products,true).'</pre>*/';
		foreach($products as $product_id) {
			$product_id = absint($product_id);
			$wpdb->query("REPLACE INTO `".WPSC_TABLE_PRODUCT_ORDER."`(`category_id`, `product_id`, `order`) VALUES ('{$category_id}', '{$product_id}', '{$order}' )");
	//		echo "/*  REPLACE INTO `".WPSC_TABLE_PRODUCT_ORDER."`(`category_id`, `product_id`, `order`) VALUES ('{$category_id}', '{$product_id}', '$order' ) */\n\r";
			$order++;
		} 
		$success = true;
	} else {
		$success = false; 
	}
	exit((string)$success);
}
 
 
if($_REQUEST['wpsc_admin_action'] == 'save_product_order') {
	add_action('admin_init', 'wpsc_save_product_order');
}

function wpsc_save_checkout_order() {
  global $wpdb;
  		//exit('<pre>'.print_r($_POST, true).'</pre>');
		$checkoutfields = $_POST['checkout'];
		$order=1;
		foreach($checkoutfields as $checkoutfield) {
			$checkoutfield = absint($checkoutfield);
			$wpdb->query("UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `order` = '".$order."' WHERE `id`=".$checkoutfield);

			$order++;
		} 
		$success = true;

	exit((string)$success);
}
 
 
if($_REQUEST['wpsc_admin_action'] == 'save_checkout_order') {
	add_action('admin_init', 'wpsc_save_checkout_order');
}
/* Start Order Notes (by Ben) */
function wpsc_purchlogs_update_notes($purchlog_id = '', $purchlog_notes = '' ) {
	global $wpdb;
	if ( wp_verify_nonce( $_POST['wpsc_purchlogs_update_notes_nonce'], 'wpsc_purchlogs_update_notes' ) ) {
		if ( ($purchlog_id == '') && ($purchlog_notes == '') ) {
			$purchlog_id = absint($_POST['purchlog_id']);
			$purchlog_notes = $wpdb->escape($_POST['purchlog_notes']);
		}
		$wpdb->query("UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET notes='{$purchlog_notes}' WHERE id='{$purchlog_id}'");
	}
}

if ( $_REQUEST['wpsc_admin_action'] == 'purchlogs_update_notes' ) {
	add_action('admin_init', 'wpsc_purchlogs_update_notes');
}
/* End Order Notes (by Ben) */

 
//delete a purchase log
function wpsc_delete_purchlog($purchlog_id='') {
	global $wpdb;
	$deleted = 0;
	if($purchlog_id == ''){
		$purchlog_id = absint($_GET['purchlog_id']);
		check_admin_referer('delete_purchlog_' .  $purchlog_id);
  	}
  
  
	///
	if(is_numeric($purchlog_id)) {
		  
		  $delete_log_form_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$purchlog_id'";
		  $cart_content = $wpdb->get_results($delete_log_form_sql,ARRAY_A);
		  foreach((array)$cart_content as $cart_item) {
		    $cart_item_variations = $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id` = '".$cart_item['id']."'", ARRAY_A);
			}
			
		//echo "DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` = '{$purchlog_id}' AND `cart_submitted` = '1'";
	}
	
	$purchlog_status = $wpdb->get_var("SELECT `processed` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`=".$purchlog_id);
	if($purchlog_status == 5 || $purchlog_status == 1) {
		$wpdb->query("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` = '{$purchlog_id}' AND `cart_submitted` = '1'");
	}
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$purchlog_id'");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` IN ('$purchlog_id')");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='$purchlog_id' LIMIT 1");
		//  return '<div id="message" class="updated fade"><p>'.__('Thanks, the purchase log record has been deleted', 'wpsc').'</p></div>';
		$deleted = 1;
		
 
	////	
	if(is_numeric($_GET['purchlog_id'])){
		$sendback = wp_get_referer();
		$sendback = remove_query_arg('purchaselog_id', $sendback);
		if ( isset($deleted) ) {
			$sendback = add_query_arg('deleted', $deleted, $sendback);
		}
		wp_redirect($sendback);
		
		exit();
	}
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'delete_purchlog') {
	add_action('admin_init', 'wpsc_delete_purchlog');
}
 
 




/*
 * Get Shipping Form ajax call
 */
function wpsc_ajax_get_shipping_form() {
  global $wpdb, $wpsc_shipping_modules;
  $shippingname = $_REQUEST['shippingname'];
  $_SESSION['previous_shipping_name'] = $shippingname;
  $shipping_data = wpsc_get_shipping_form($shippingname);
	$html_shipping_name = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($shipping_data['name']));
	$shipping_form = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($shipping_data['form_fields']));
	echo "shipping_name_html = '$html_shipping_name'; \n\r";
	echo "shipping_form_html = '$shipping_form'; \n\r";
	echo "has_submit_button = '{$shipping_data['has_submit_button']}'; \n\r";
		//echo "<script type='text/javascript'>jQuery('.gateway_settings h3.hndle').livequery(function(){ jQuery(this).html('".$wpsc_shipping_modules[$shippingname]->getName()."')})</script>";
  exit();
}
 
function wpsc_crop_thumbnail_html() {
  include(WPSC_FILE_PATH."/wpsc-admin/includes/crop.php"); 
  exit();
}
 
 
 
if ($_REQUEST['wpsc_admin_action'] == 'crop_image') {
	add_action('admin_init','wpsc_crop_thumbnail_html');
}
 
 
 
if($_REQUEST['wpsc_admin_action'] == 'get_shipping_form') {
	add_action('admin_init', 'wpsc_ajax_get_shipping_form');
}



/*
 *Submit Options from Settings Pages, 
 *takes an array of options checks to see whether it is empty or the same as the exisiting values 
 *and if its not it updates them. 
 */
function wpsc_submit_options($selected='') {
  global $wpdb, $wpsc_gateways;
	//This is to change the Overall target market selection
	check_admin_referer('update-options', 'wpsc-update-options');

	 if($_POST['countrylist2'] != null || $selected != ''){
    	$AllSelected = false;
    	if( $selected == 'all'){
    		$wpdb->query("UPDATE `".WPSC_TABLE_CURRENCY_LIST."` SET visible = '1'");
			$AllSelected = true;
			return;
    	}
    	if( $selected == 'none'){
    		$wpdb->query("UPDATE `".WPSC_TABLE_CURRENCY_LIST."` SET visible = '0'");
			$AllSelected = true;
			return;
    	}
    	if($AllSelected != true){
			$countrylist = $wpdb->get_col("SELECT id FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY country ASC ");
			//find the countries not selected
			$unselectedCountries = array_diff($countrylist, $_POST['countrylist2']);
			foreach($unselectedCountries as $unselected){
				$wpdb->query("UPDATE `".WPSC_TABLE_CURRENCY_LIST."` SET visible = 0 WHERE id = '".$unselected."' LIMIT 1");
			}

			//find the countries that are selected
			$selectedCountries = array_intersect($countrylist, $_POST['countrylist2']);
			foreach($selectedCountries as $selected){
				$wpdb->query("UPDATE `".WPSC_TABLE_CURRENCY_LIST."` SET visible = 1	WHERE id = '".$selected."' LIMIT 1");
			}
 		}
	}
  	//this is to change the base country and tax code for the shop
  	 if((is_numeric($_POST['country_id']) && is_numeric($_POST['country_tax']))) {
	      $wpdb->query("UPDATE `".WPSC_TABLE_CURRENCY_LIST."` SET `tax` = '".$_POST['country_tax']."' WHERE `id` = '".$_POST['country_id']."' LIMIT 1 ;");
	 }


	$previous_currency = get_option('currency_type');
	 
	//To update options
	if(isset($_POST['wpsc_options'])){
		foreach($_POST['wpsc_options'] as $key=>$value){
			if($value != get_option($key)) {
				update_option($key, $value);
				$updated++;
			}
		}
	}

 	if($previous_currency != get_option('currency_type')) {
		$currency_code = $wpdb->get_var("SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` IN ('".absint(get_option('currency_type'))."')");

		$selected_gateways = get_option('custom_gateway_options');
		$already_changed = array();
		foreach($selected_gateways as $selected_gateway) {
			if(isset($wpsc_gateways[$selected_gateway]['supported_currencies'])) {
				if(in_array($currency_code, $wpsc_gateways[$selected_gateway]['supported_currencies']['currency_list'])) {

					$option_name = $wpsc_gateways[$selected_gateway]['supported_currencies']['option_name'];

					if(!in_array($option_name, $already_changed)) {
						//echo $option_name;
						update_option($option_name, $currency_code);
						$already_changed[] = $option_name;
					}
				}
			}
		}


		//exit("<pre>".print_r($selected_gateways,true)."</pre>");

 	}

	foreach($GLOBALS['wpsc_shipping_modules'] as $shipping) {
		$shipping->submit_form();
	}


	//This is for submitting shipping details to the shipping module
 	if($_POST['update_gateways'] == 'true') {

		update_option('custom_shipping_options', $_POST['custom_shipping_options']);


		foreach($GLOBALS['wpsc_shipping_modules'] as $shipping) {
			foreach((array)$_POST['custom_shipping_options'] as $shippingoption){
				//echo $shipping->getInternalName().' == '.$shippingoption;
				if($shipping->getInternalName() == $shippingoption) {
 					//$shipping->submit_form();
					$shipadd++;
				}
			}
		}
 	}
	$sendback = wp_get_referer();

	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if ( isset($shipadd) ) {
		$sendback = add_query_arg('shipadd', $shipadd, $sendback);
	}
	if(isset($_SESSION['wpsc_settings_curr_page'])){
			$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], $sendback);
	}

	$sendback = add_query_arg('page', 'wpsc-settings', $sendback);
	wp_redirect($sendback);
	exit();
}
 
 

 if($_REQUEST['wpsc_admin_action'] == 'submit_options') {
	add_action('admin_init', 'wpsc_submit_options');
}

function wpsc_change_currency(){
	if(is_numeric($_POST['currencyid'])){
		$currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$_POST['currencyid']."' LIMIT 1",ARRAY_A) ;
		$price_out = null;
		if($currency_data[0]['symbol'] != '') {
			$currency_sign = $currency_data[0]['symbol_html'];
		} else {
			$currency_sign = $currency_data[0]['code'];
		}
		echo $currency_sign;
	}
}
 if($_REQUEST['wpsc_admin_action'] == 'change_currency') {
	add_action('admin_init', 'wpsc_change_currency');
}

function wpsc_rearrange_images() {
	global $wpdb;
	$height = get_option('product_image_height');
	$width  = get_option('product_image_width');
	$images = explode(",",$_POST['order']);
	$product_id = absint($_POST['product_id']);
	$timestamp = time();

	$new_main_image = null;
	$have_set_first_item = false;
	$i = 0;
	foreach($images as $image) {
		if($image > 0) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `image_order`='$i' WHERE `id`='".absint($image)."' LIMIT 1");
			if($have_set_first_item == false) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image`='".absint($image)."' WHERE `id`='{$product_id}' LIMIT 1");
				$have_set_first_item = true;
				$new_main_image = $image;
				$image_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image}' LIMIT 1", ARRAY_A);
				
				$image_input = WPSC_IMAGE_DIR . $image_data['file'];
				$image_output = WPSC_THUMBNAIL_DIR . $image_data['file'];
				if(($product['file'] != '') and file_exists($image_input)) {
					image_processing($image_input, $image_output, $width, $height);
					update_product_meta($product_id, 'thumbnail_width', $width);
					update_product_meta($product_id, 'thumbnail_height', $height);
				} 
			}
			$i++;
		}
	}
	$output = wpsc_main_product_image_menu($product_id);
	echo "image_menu='".str_replace(array("\n", "\r"), array('\n', '\r'), addslashes($output))."';\n\r";
	echo "image_id='".$new_main_image."';\n\r";
	exit();
}
if($_REQUEST['wpsc_admin_action'] == 'rearrange_images') {
	add_action('admin_init', 'wpsc_rearrange_images');
}


function wpsc_delete_images() {
	global $wpdb;
	$product_id = absint($_POST['product_id']);
	$element_id = $_POST['del_img_id'];
	$image_id = absint(str_replace("product_image_", '', $element_id));
	if($image_id > 0) {
	  $deletion_success = $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`='{$image_id}' LIMIT 1");
		echo "element_id = '$element_id';\n";
		//echo "/*\n";
		//print_r($deletion_success);
		//echo "*/\n";
	
		if(($product_id > 0) && ($deletion_success == true)) {
			$next_image = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '{$product_id}' ORDER BY `image_order` ASC LIMIT 1",ARRAY_A);
			if(count($next_image) > 0) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$next_image['id']}' WHERE `id` = '{$product_id}' LIMIT 1");
				$output = wpsc_main_product_image_menu($product_id);
							
				$height = get_option('product_image_height');
				$width  = get_option('product_image_width');
				
				$image_input = WPSC_IMAGE_DIR . $next_image['file'];
				$image_output = WPSC_THUMBNAIL_DIR . $next_image['file'];
				if(($product['file'] != '') and file_exists($image_input)) {
					image_processing($image_input, $image_output, $width, $height);
					update_product_meta($product_id, 'thumbnail_width', $width);
					update_product_meta($product_id, 'thumbnail_height', $height);
				} 
				echo "image_menu='".str_replace(array("\n", "\r"), array('\n', '\r'), addslashes($output))."';\n";
				echo "image_id='".$next_image['id']."';\n";
			} else {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = NULL WHERE `id` = '{$product_id}' LIMIT 1");
			}
		}
	}
	
	exit();
}

if($_REQUEST['wpsc_admin_action'] == 'delete_images') {
	add_action('admin_init', 'wpsc_delete_images');
}

function wpsc_update_page_urls(){
	global $wpdb;

  $wpsc_pageurl_option['product_list_url'] = '[productspage]';
  $wpsc_pageurl_option['shopping_cart_url'] = '[shoppingcart]';
  $check_chekout = $wpdb->get_var("SELECT `guid` FROM `{$wpdb->posts}` WHERE `post_content` LIKE '%[checkout]%' LIMIT 1");
  if($check_chekout != null) {
		$wpsc_pageurl_option['checkout_url'] = '[checkout]';
	} else {
		$wpsc_pageurl_option['checkout_url'] = '[checkout]';
	}
  $wpsc_pageurl_option['transact_url'] = '[transactionresults]';
  $wpsc_pageurl_option['user_account_url'] = '[userlog]';
  $changes_made = false;
  foreach($wpsc_pageurl_option as $option_key => $page_string) {
    $post_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN('page','post') AND `post_content` LIKE '%$page_string%' LIMIT 1");
    $the_new_link = get_permalink($post_id);
    if(stristr(get_option($option_key), "https://")) {
      $the_new_link = str_replace('http://', "https://",$the_new_link);
    }
       
    update_option($option_key, $the_new_link);
    $updated;
	}
	$sendback = wp_get_referer();

	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if(isset($_SESSION['wpsc_settings_curr_page'])){
		$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], $sendback);
	}
	wp_redirect($sendback);

exit();
}
 if($_REQUEST['wpsc_admin_action'] == 'update_page_urls') {
	add_action('admin_init', 'wpsc_update_page_urls');
}

function wpsc_clean_categories(){
global $wpdb, $wp_rewrite;
  //exit("<pre>".print_r($check_category_names,true)."</pre>");
  $sql_query = "SELECT `id`, `name`, `active` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."`";
	$sql_data = $wpdb->get_results($sql_query,ARRAY_A);
	foreach((array)$sql_data as $datarow) {	
	  if($datarow['active'] == 1) {
	    $tidied_name = trim($datarow['name']);
			$tidied_name = strtolower($tidied_name);
			$url_name = sanitize_title($tidied_name);            
			$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` REGEXP '^($url_name){1}(\d)*$' AND `id` NOT IN ('{$datarow['id']}') ",ARRAY_A);
			$extension_number = '';
			if($similar_names['count'] > 0) {
				$extension_number = (int)$similar_names['max_number']+2;
			}
			$url_name .= $extension_number;
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `nice-name` = '$url_name' WHERE `id` = '{$datarow['id']}' LIMIT 1 ;");
			$updated;
	  } else if($datarow['active'] == 0) {
		  $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `nice-name` = '' WHERE `id` = '{$datarow['id']}' LIMIT 1 ;");
		  $updated;
	  }
	}
	$wp_rewrite->flush_rules();
	$sendback = wp_get_referer();

	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if(isset($_SESSION['wpsc_settings_curr_page'])){
			$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], $sendback);
	}
	wp_redirect($sendback);

exit();
}
 if($_REQUEST['wpsc_admin_action'] == 'clean_categories') {
	add_action('admin_init', 'wpsc_clean_categories');
}

//change the regions tax settings
function wpsc_change_region_tax(){
global $wpdb; 
	if(is_array($_POST['region_tax'])) {
	  foreach($_POST['region_tax'] as $region_id => $tax) {
	    if(is_numeric($region_id) && is_numeric($tax)) {
	      $previous_tax = $wpdb->get_var("SELECT `tax` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` = '$region_id' LIMIT 1");
	      if($tax != $previous_tax) {
	        $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `tax` = '$tax' WHERE `id` = '$region_id' LIMIT 1");
	        $changes_made = true;
	        
	      }
	    }
	  }
	  $sendback = wp_get_referer();
	   $sendback = remove_query_arg(array('isocode'),$sendback);
	  if($changes_made){
	  	$sendback = add_query_arg('updated', $changes_made, $sendback);
	  }
//		
		     
		//$sendback = remove_query_arg('isocode', $sendback);
		wp_redirect($sendback);
	}
}

if($_REQUEST['wpsc_admin_action'] == 'change_region_tax') {
	add_action('admin_init', 'wpsc_change_region_tax');
}

//change the gateway settings
function wpsc_gateway_settings(){
	global $wpdb;
	//To update options
	if(isset($_POST['wpsc_options'])){
		foreach($_POST['wpsc_options'] as $key=>$value){
			if($value != get_option($key)){
				update_option($key, $value);
				$updated++;
			}
		}
		unset($_POST['wpsc_options']);
	}
	
	
	
	if(is_array($_POST['user_defined_name'])) {
		$payment_gateway_names = get_option('payment_gateway_names');
		if(!is_array($payment_gateway_names)) {
		  $payment_gateway_names = array();
		}
	  $payment_gateway_names = array_merge($payment_gateway_names, (array)$_POST['user_defined_name']);
		update_option('payment_gateway_names', $payment_gateway_names);	
	}
	
	//exit('<pre>'.print_r($payment_gateway_names,true).'</pre>');
	
	foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
		if($gateway['internalname'] == get_option('payment_gateway')) {
			$gateway['submit_function']();
			$changes_made = true;
		}
	}
	if(($_POST['payment_gw'] != null)) {
	  update_option('payment_gateway', $_POST['payment_gw']);
	}
	$sendback = wp_get_referer();

	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if(isset($_SESSION['wpsc_settings_curr_page'])){
		$sendback = add_query_arg('page', 'wpsc-settings', $sendback);		
		$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], $sendback);
	}
	//sexit($sendback);
	wp_redirect($sendback);
	exit();

}
  if($_REQUEST['wpsc_gateway_settings'] == 'gateway_settings') {
	add_action('admin_init', 'wpsc_gateway_settings');
}
function wpsc_check_form_options(){
	global $wpdb;

	$id = $wpdb->escape($_POST['form_id']);
	$sql = 'SELECT `options` FROM `'.WPSC_TABLE_CHECKOUT_FORMS.'` WHERE `id`='.$id;
//	exit($sql);
	$options = $wpdb->get_var($sql);
	if($options != ''){

		$options = maybe_unserialize($options);
		if(!is_array($options)){
			$options = unserialize($options);		
		}
	//exit('<pre>'.print_r($options,true).'</pre>');
		$output =  "<tr class='wpsc_grey'><td></td><td colspan='5'>Please Save your changes before trying to Order your Checkout Forms again.</td></tr>\r\n<tr  class='wpsc_grey'><td></td><th>Label</th><th >Value</th><td colspan='3'><a href=''  class='wpsc_add_new_checkout_option'  title='form_options[".$id."]'>+ New Layer</a></td></tr>";
	
		foreach((array)$options as $key=>$value){
			$output .="<tr class='wpsc_grey'><td></td><td><input type='text' value='".$key."' name='wpsc_checkout_option_label[".$id."][]' /></td><td colspan='4'><input type='text' value='".$value."' name='wpsc_checkout_option_value[".$id."][]' />&nbsp;<a class='wpsc_delete_option' href='' <img src='".WPSC_URL."/images/trash.gif' alt='".__('Delete', 'wpsc')."' title='".__('Delete', 'wpsc')."' /></a></td></tr>";
		
		}

	}else{
		$output = '';
	}
	exit($output);
}	
if($_REQUEST['wpsc_admin_action']=='check_form_options'){
	add_action('admin_init','wpsc_check_form_options');
}

//handles the editing and adding of new checkout fields
function wpsc_checkout_settings(){
	global $wpdb;
	$wpdb->show_errors = true;
    if(!empty($_POST['selected_form_set'])){
    	$filter = $wpdb->escape($_POST['selected_form_set']);
    }else{
    	$filter = 0;
    }
	  
	  if($_POST['new_form_set'] != null) {
	  	$new_form_set = $wpdb->escape(stripslashes($_POST['new_form_set']));
      $form_set_key = sanitize_title($new_form_set);
			$checkout_sets = get_option('wpsc_checkout_form_sets');

			$checkout_sets[$form_set_key] = $new_form_set;
			update_option('wpsc_checkout_form_sets', $checkout_sets);
	  }

	       $options = array();
	    if(is_array($_POST['wpsc_checkout_option_label'])){
	    		
	    	foreach($_POST['wpsc_checkout_option_label'] as $form_id=> $values){
				$options = array();
					foreach((array)$values as $key => $form_option){
						$form_option = str_ireplace("'", "",$form_option);
						$form_val = str_ireplace("'", "",sanitize_title($_POST['wpsc_checkout_option_value'][$form_id][$key]));
						$options[$form_option] = $form_val;
					}
				$options = serialize($options);
				$sql = "UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `options`='".$options."' WHERE id=".$form_id;
				$wpdb->query($sql);
			}
			

//			exit('<pre>'.print_r($options,true).'</pre>');						
		/*
	$options = serialize($options);

			$sql = "UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `options`='".$options."' WHERE id=".$form_id;
*/



	    }
	    
	
		if($_POST['form_name'] != null) {
		    foreach($_POST['form_name'] as $form_id => $form_name) {
					$form_name = $wpdb->escape($form_name);
		      $form_type = $wpdb->escape($_POST['form_type'][$form_id]);
		      $form_mandatory = 0;
		      if($_POST['form_mandatory'][$form_id] == 1) {  $form_mandatory = 1;  }
		      $form_display_log = 0;
		      if($_POST['form_display_log'][$form_id] == 1) {  $form_display_log = 1;  }
		      $unique_name = '';
		      if($_POST['unique_names'][$form_id] != '-1'){ $unique_name = $_POST['unique_names'][$form_id];  }
		    //  $form_order = $_POST['form_order'][$form_id];
		      $wpdb->query("UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `name` = '$form_name', `type` = '$form_type', `mandatory` = '$form_mandatory', `display_log` = '$form_display_log',`unique_name`='".$unique_name."' WHERE `id` ='".$form_id."' LIMIT 1 ;");
		      //echo "UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `name` = '$form_name', `type` = '$form_type', `mandatory` = '$form_mandatory', `display_log` = '$form_display_log',`unique_name`='".$unique_name."', `checkout_set`='".$filter."' WHERE `id` ='".$form_id."' LIMIT 1 ;";

		      //echo "<br />";
			}
		}
		
	  if($_POST['new_form_name'] != null) {
	    foreach($_POST['new_form_name'] as $form_id => $form_name) {
	      $form_type = $_POST['new_form_type'][$form_id];
	      $form_mandatory = 0;
	      if($_POST['new_form_mandatory'][$form_id] == 1) {  $form_mandatory = 1;  }
	      $form_display_log = 0;
	      if($_POST['new_form_display_log'][$form_id] == 1) {  $form_display_log = 1;  }		 
	      if($_POST['new_form_unique_name'][$form_id] != '-1') {  $form_unique_name = $_POST['new_form_unique_name'][$form_id];  }
	    
	      $max_order_sql = "SELECT MAX(`order`) AS `order` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1';";
	      
	      if($_POST['new_form_order'][$form_id] != '') {
	        $order_number = $_POST['new_form_order'][$form_id];
				} else {
					$max_order_sql = $wpdb->get_results($max_order_sql,ARRAY_A);
					$order_number = $max_order_sql[0]['order'] + 1;
				}
				$wpdb->query("INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` , `unique_name`, `checkout_set`) VALUES ( '$form_name', '$form_type', '$form_mandatory', '$form_display_log', '', '1','".$order_number."','".$form_unique_name."','".$filter."');");
				$added++;
			}
		}
		//echo "INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` , `unique_name`, `checkout_set`) VALUES ( '$form_name', '$form_type', '$form_mandatory', '$form_display_log', '', '1','".$order_number."','".$form_unique_name."','".$filter."');";
	  //exit('<pre>'.print_r($_POST, true).'</pre>');
	   
	   
		if(isset($_POST['wpsc_options'])){
			foreach($_POST['wpsc_options'] as $key=>$value){
				if($value != get_option($key)){
					update_option($key, $value);
					$updated++;
				}
			}
		}
	//}
	$sendback = wp_get_referer();
	if(isset($form_set_key)) {
		$sendback = add_query_arg('checkout-set', $form_set_key, $sendback);
	} else if(isset($_POST['wpsc_form_set'])){
		$filter = $_POST['wpsc_form_set'];
		$sendback = add_query_arg('checkout-set', $filter, $sendback);
	}
	
	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if ( isset($added) ) {
		$sendback = add_query_arg('added', $added, $sendback);
	}
	if(isset($_SESSION['wpsc_settings_curr_page'])){
		$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], $sendback);
	}
	$sendback = add_query_arg('page', 'wpsc-settings', $sendback);
	wp_redirect($sendback);
	exit();
}


if($_REQUEST['wpsc_admin_action'] == 'checkout_settings') {
	add_action('admin_init', 'wpsc_checkout_settings');
}


function wpsc_google_shipping_settings(){
	if (isset($_POST['submit'])) {
		foreach((array)$_POST['google_shipping'] as $key=>$country) {
			if ($country == 'on') {
				$google_shipping_country[]=$key;
				$updated++;
			}
		}
		update_option('google_shipping_country',$google_shipping_country);
		$sendback = wp_get_referer();
		$sendback = remove_query_arg('googlecheckoutshipping', $sendback);
		
		if ( isset($updated) ) {
			$sendback = add_query_arg('updated', $updated, $sendback);
		}

		wp_redirect($sendback);
		exit();
	}
}

if($_REQUEST['wpsc_admin_action'] == 'google_shipping_settings') {
	add_action('admin_init', 'wpsc_google_shipping_settings');
}

//for ajax call of settings page tabs
function wpsc_settings_page_ajax(){
  global $wpdb;
  $modified_page_title = $_POST['page_title'];
  check_admin_referer($modified_page_title);
	$page_title = str_replace("tab-","",$modified_page_title);

	
	//require_once('includes/settings-pages/'.$functionname1.'.php');
	//$functionname = "wpsc_options_".$functionname1;
	//$html = $functionname();

	switch($page_title) {
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
	$_SESSION['wpsc_settings_curr_page'] = $page_title;
	exit($html);
}
  
if($_REQUEST['wpsc_admin_action'] == 'settings_page_ajax') {
	add_action('admin_init', 'wpsc_settings_page_ajax');
}


function wpsc_trigger_copy_themes(){
  global $wpdb;
  check_admin_referer('copy_themes');
  wpsc_copy_themes_to_uploads();

  $_SESSION['wpsc_themes_copied'] = true;
	$sendback = wp_get_referer();
	$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], remove_query_arg('tab', $sendback));
	wp_redirect($sendback);
	exit();
}

if($_REQUEST['wpsc_admin_action'] == 'copy_themes') {
	add_action('admin_init', 'wpsc_trigger_copy_themes');
}

function wpsc_mass_resize_thumbnails() {
  global $wpdb;
  check_admin_referer('mass_resize');

	if(isset($_GET['wpsc_options'])){
		foreach($_GET['wpsc_options'] as $key=>$value){
			if(($value != get_option($key)) and (absint($value) > 0)){
				update_option($key, absint($value));
			}
		}
	}

	$height = get_option('product_image_height');
	$width  = get_option('product_image_width');
  
	$product_data = $wpdb->get_results("SELECT `product`.`id`, `product`.`image` AS `image_id`, `images`.`image` AS `file`  FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `product` INNER JOIN  `".WPSC_TABLE_PRODUCT_IMAGES."` AS `images` ON `product`.`image` = `images`.`id` WHERE `product`.`image` > 0 ",ARRAY_A);
	foreach((array)$product_data as $product) {
		$image_input = WPSC_IMAGE_DIR . $product['file'];
		$image_output = WPSC_THUMBNAIL_DIR . $product['file'];
		if(($product['file'] != '') and file_exists($image_input)) {
			image_processing($image_input, $image_output, $width, $height);
			update_product_meta($product['id'], 'thumbnail_width', $width);
			update_product_meta($product['id'], 'thumbnail_height', $height);
		} else {
			$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` IN('{$product['image_id']}') LIMIT 1");
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = NULL WHERE `id` = '".$product['id']."' LIMIT 1");
		}
		
	}
	//$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` IN('0')");
	
  $_SESSION['wpsc_thumbnails_resized'] = true;
	$sendback = wp_get_referer();
	$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], remove_query_arg('tab', $sendback));
	wp_redirect($sendback);
	exit();
}
  
if($_REQUEST['wpsc_admin_action'] == 'mass_resize_thumbnails') {
	add_action('admin_init', 'wpsc_mass_resize_thumbnails');
}
 
function wpsc_delete_variation_set() {
  global $wpdb;
  check_admin_referer('delete-variation');
  
	if(is_numeric($_GET['deleteid'])){
	  $deleteid = absint($_GET['deleteid']);
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `variation_id` = '{$deleteid}'");
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `variation_id` = '{$deleteid}'");
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` = '{$deleteid}';");
		$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` WHERE `id`='{$deleteid}' LIMIT 1");
		$deleted = 1;
	}
	
	$sendback = wp_get_referer();
	if ( isset($deleted) ) {
		$sendback = add_query_arg('deleted', $deleted, $sendback);
	}
	wp_redirect($sendback);	
	exit();
}
 
 

//other actions are here
if($_GET['display_invoice']=='true') {
  add_action('admin_init', 'wpsc_display_invoice', 0);
}



 if($_REQUEST['wpsc_admin_action'] == 'wpsc_add_image') {
	add_action('admin_init','wpsc_swfupload_images');
}

 if($_REQUEST['wpsc_admin_action'] == 'edit_product') {
	add_action('admin_init', 'wpsc_admin_submit_product');
}
 

if($_GET['action'] == "purchase_log") {
	add_action('admin_init', 'wpsc_admin_sale_rss');
}


if($_GET['purchase_log_csv'] == "true") {
	add_action('admin_init', 'wpsc_purchase_log_csv');
}

if(($_REQUEST['ajax'] == "true") && ($_REQUEST['admin'] == "true")) {
	add_action('admin_init', 'wpsc_admin_ajax');
}

  // Variation set deleting init code starts here
 if($_REQUEST['wpsc_admin_action'] == 'wpsc-delete-variation-set') {
	add_action('admin_init', 'wpsc_delete_variation_set');
}

 // Variation set adding init code starts here
 if($_REQUEST['wpsc_admin_action'] == 'wpsc-variation-set') {
	add_action('admin_init', 'wpsc_save_variation_set');
}

?>