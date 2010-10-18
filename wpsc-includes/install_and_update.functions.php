<?php
function wpsc_auto_update() {
  global $wpdb;
  wpsc_create_or_update_tables();  
  include(WPSC_FILE_PATH.'/updates/updating_tasks.php');
  wpsc_create_upload_directories();
  wpsc_product_files_htaccess();  
  wpsc_check_and_copy_files();
  
  if((get_option('wpsc_version') < WPSC_VERSION) || (get_option('wpsc_version') == WPSC_VERSION) && (get_option('wpsc_minor_version') < WPSC_MINOR_VERSION)) {
    update_option('wpsc_version', WPSC_VERSION);
    update_option('wpsc_minor_version', WPSC_MINOR_VERSION);
	}
}

function wpsc_install() {
	global $wpdb, $user_level, $wp_rewrite, $wp_version;
	$table_name = $wpdb->prefix . "wpsc_product_list";

  $first_install = false;
  $result = mysql_list_tables(DB_NAME);
  $tables = array();
  while($row = mysql_fetch_row($result)) {
    $tables[] = $row[0];
	}
  if(!in_array($table_name, $tables)) {
    $first_install = true;
		add_option('wpsc_purchaselogs_fixed',true);
	}    

  if(get_option('wpsc_version') == null) {
    add_option('wpsc_version', WPSC_VERSION, 'wpsc_version', 'yes');
	}

  // run the create or update code here.
  wpsc_create_or_update_tables();  
  wpsc_create_upload_directories();
  
  if(!wp_get_schedule("wpsc_hourly_cron_tasks")) {
		wp_schedule_event(time(), 'hourly', 'wpsc_hourly_cron_tasks');
	}
	
  if(!wp_get_schedule("wpsc_daily_cron_tasks")) {
		wp_schedule_event(time(), 'daily', 'wpsc_daily_cron_tasks');
	}
  //wp_get_schedule( $hook, $args )

	
  /* all code to add new database tables and columns must be above here */  
  if((get_option('wpsc_version') < WPSC_VERSION) || (get_option('wpsc_version') == WPSC_VERSION) && (get_option('wpsc_minor_version') < WPSC_MINOR_VERSION)) {
    update_option('wpsc_version', WPSC_VERSION);
    update_option('wpsc_minor_version', WPSC_MINOR_VERSION);
	}


  $add_initial_category = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."`;",ARRAY_A);
  if($add_initial_category[0]['count'] == 0) {
		$wpdb->query("INSERT INTO `".WPSC_TABLE_CATEGORISATION_GROUPS."` (`id`, `name`, `description`, `active`, `default`) VALUES (1, 'Categories', 'Product Categories', '1', '1')");
		$wpdb->query("INSERT INTO `".WPSC_TABLE_CATEGORISATION_GROUPS."` (`id`, `name`, `description`, `active`, `default`) VALUES (2, 'Brands', 'Product Brands', '1', '0')");			
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_CATEGORIES."` (`group_id`, `name` , `description`, `active`) VALUES ('1', '".__('Example category', 'wpsc')."', '".__('Example details', 'wpsc')."', '1');");    
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_CATEGORIES."` (`group_id`, `name` , `description`, `active`) VALUES ('2', '".__('Example Brand', 'wpsc')."', '".__('Example details', 'wpsc')."', '1');");
	}
  
  $purchase_statuses_data  = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PURCHASE_STATUSES."`",ARRAY_A);
  if($purchase_statuses_data[0]['count'] == 0) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PURCHASE_STATUSES."` (`name` , `active` , `colour` ) 
    VALUES
    ('".__('Order Received', 'wpsc')."', '1', ''),
    ('".__('Accepted Payment', 'wpsc')."', '1', ''),
    ('".__('Job Dispatched', 'wpsc')."', '1', ''),
    ('".__('Closed Order', 'wpsc')."', '1', '');");
	}

  $check_category_assoc = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`;",ARRAY_A);
  if($check_category_assoc[0]['count'] == 0) {
    $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`=1";
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    foreach((array)$product_list as $product) {
      $results = $wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` (`product_id` , `category_id` ) VALUES ('".$product['id']."', '".$product['category']."');");
		}
	}
    

	
	

  add_option('show_thumbnails', 1, __('Show Thumbnails', 'wpsc'), "yes");
  add_option('show_products_rss', '1', __('Show Product List RSS', 'wpsc'), 'yes');

  add_option('product_image_width', '', __('product image width', 'wpsc'), 'yes');
  add_option('product_image_height', '', __('product image height', 'wpsc'), 'yes');

  add_option('category_image_width', '', __('product group image width', 'wpsc'), 'yes');
  add_option('category_image_height', '', __('product group image height', 'wpsc'), 'yes');

  add_option('product_list_url', '', __('The location of the product list', 'wpsc'), 'yes');
  add_option('shopping_cart_url', '', __('The location of the shopping cart', 'wpsc'), 'yes');
  add_option('checkout_url', '', __('The location of the checkout page', 'wpsc'), 'yes');
  add_option('transact_url', '', __('The location of the transaction detail page', 'wpsc'), 'yes');
  add_option('payment_gateway', '', __('The payment gateway to use', 'wpsc'), 'yes');
  if(function_exists('register_sidebar') ) {
    add_option('cart_location', '4', __('Cart Location', 'wpsc'), 'yes');
	} else {
    add_option('cart_location', '1', __('Cart Location', 'wpsc'), 'yes');
	}

  if ( function_exists('register_sidebar') ) {
    add_option('cart_location', '4', __('Cart Location', 'wpsc'), 'yes');
  } else {
		add_option('cart_location', '1', __('Cart Location', 'wpsc'), 'yes');
  }

  //add_option('show_categorybrands', '0', __('Display categories or brands or both', 'wpsc'), 'yes');

  add_option('currency_type', '156', __('Currency type', 'wpsc'), 'yes');
  add_option('currency_sign_location', '3', __('Currency sign location', 'wpsc'), 'yes');

  add_option('gst_rate', '1', __('the GST rate', 'wpsc'), 'yes');

  add_option('max_downloads', '1', __('the download limit', 'wpsc'), 'yes');

  add_option('display_pnp', '1', __('Display or hide postage and packaging', 'wpsc'), 'yes');

  add_option('display_specials', '1', __('Display or hide specials on the sidebar', 'wpsc'), 'yes');
  add_option('do_not_use_shipping', '0', 'do_not_use_shipping', 'yes');

  add_option('postage_and_packaging', '0', __('Default postage and packaging', 'wpsc'), 'yes');
  
  add_option('purch_log_email', '', __('Email address that purchase log is sent to', 'wpsc'), 'yes');
  add_option('return_email', '', __('Email address that purchase reports are sent from', 'wpsc'), 'yes');
  add_option('terms_and_conditions', '', __('Checkout terms and conditions', 'wpsc'), 'yes');

	add_option('google_key', 'none', __('Google Merchant Key', 'wpsc'), 'yes');
	add_option('google_id', 'none', __('Google Merchant ID', 'wpsc'), 'yes');
 
   add_option('default_brand', 'none', __('Default Brand', 'wpsc'), 'yes');
   add_option('wpsc_default_category', 'all', __('Select what product group you want to display on the products page', 'wpsc'), 'yes');
   
   add_option('product_view', 'default', "", 'yes');
   add_option('add_plustax', 'default', "", '1');
    
   
	add_option('nzshpcrt_first_load', '0', "", 'yes');
  
  if(!((get_option('show_categorybrands') > 0) && (get_option('show_categorybrands') < 3))) {
    update_option('show_categorybrands', 2);
	}
  //add_option('show_categorybrands', '0', __('Display categories or brands or both', 'wpsc'), 'yes');
  /* PayPal options */
  add_option('paypal_business', '', __('paypal business', 'wpsc'), 'yes');
  add_option('paypal_url', '', __('paypal url', 'wpsc'), 'yes');
  add_option('paypal_ipn', '1', __('paypal url', 'wpsc'), 'yes');
  //update_option('paypal_url', "https://www.sandbox.paypal.com/xclick");
  
  
  add_option('paypal_multiple_business', '', __('paypal business', 'wpsc'), 'yes');
  
  if(get_option('paypal_multiple_url') == null) {
    add_option('paypal_multiple_url', __('paypal url', 'wpsc'), 'yes');
    update_option('paypal_multiple_url', "https://www.paypal.com/cgi-bin/webscr");
	}

  add_option('product_ratings', '0', __('Show Product Ratings', 'wpsc'), 'yes');
  add_option('wpsc_email_receipt', __('Thank you for purchasing with %shop_name%, any items to be shipped will be processed as soon as possible, any items that can be downloaded can be downloaded using the links on this page.All prices include tax and postage and packaging where applicable.You ordered these items:%product_list%%total_shipping%%total_price%', 'wpsc'), 'yes');
  add_option('wpsc_email_admin', __('%product_list%%total_shipping%%total_price%', 'wpsc'), 'yes');
  if(get_option('wpsc_selected_theme') == '') {
    add_option('wpsc_selected_theme', 'default', 'Selected Theme', 'yes');
    update_option('wpsc_selected_theme', "default");
	}
	
	if(!get_option('product_image_height')) {
		update_option('product_image_height', '96');
		update_option('product_image_width', '96');
	}
			
	if(!get_option('category_image_height')) {
		update_option('category_image_height', '96');
		update_option('category_image_width', '96');
	}
	
	if(!get_option('single_view_image_height')) {
		update_option('single_view_image_height', '128');
		update_option('single_view_image_width', '128');
	}
	
	if(!get_option('wpsc_gallery_image_height')) {
		update_option('wpsc_gallery_image_height', '96');
		update_option('wpsc_gallery_image_width', '96');
	}
		
	if(!is_array(get_option('custom_gateway_options'))) {
		update_option('custom_gateway_options', array('testmode'));	
	}
	
  add_option("wpsc_category_url_cache", array(), '', 'yes');
  
  wpsc_product_files_htaccess();
  
	/*
	* This part creates the pages and automatically puts their URLs into the options page.
	* As you can probably see, it is very easily extendable, just pop in your page and the deafult content in the array and you are good to go.
	*/
  $post_date =date("Y-m-d H:i:s");
  $post_date_gmt =gmdate("Y-m-d H:i:s");
  
  $num=0;
  $pages[$num]['name'] = 'products-page';
  $pages[$num]['title'] = __('Products Page', 'wpsc');
  $pages[$num]['tag'] = '[productspage]';
  $pages[$num]['option'] = 'product_list_url';
  
  $num++;
  $pages[$num]['name'] = 'checkout';
  $pages[$num]['title'] = __('Checkout', 'wpsc');
  $pages[$num]['tag'] = '[shoppingcart]';
  $pages[$num]['option'] = 'shopping_cart_url';
  
//   $num++;
//   $pages[$num]['name'] = 'enter-details';
//   $pages[$num]['title'] = __('Enter Your Details', 'wpsc');
//   $pages[$num]['tag'] = '[checkout]';
//   $pages[2$num]['option'] = 'checkout_url';

  $num++;
  $pages[$num]['name'] = 'transaction-results';
  $pages[$num]['title'] = __('Transaction Results', 'wpsc');
  $pages[$num]['tag'] = '[transactionresults]';
  $pages[$num]['option'] = 'transact_url';
  
  $num++;
  $pages[$num]['name'] = 'your-account';
  $pages[$num]['title'] = __('Your Account', 'wpsc');
  $pages[$num]['tag'] = '[userlog]';
  $pages[$num]['option'] = 'user_account_url';
  
  $newpages = false;
  $i = 0;
  $post_parent = 0;
  foreach($pages as $page) {
    $check_page = $wpdb->get_row("SELECT * FROM `".$wpdb->posts."` WHERE `post_content` LIKE '%".$page['tag']."%'  AND `post_type` NOT IN('revision') LIMIT 1",ARRAY_A);
    if($check_page == null) {
      if($i == 0) {
        $post_parent = 0;
			} else {
        $post_parent = $first_id;
			}
      
      if($wp_version >= 2.1) {
        $sql ="INSERT INTO ".$wpdb->posts."
        (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_type)
        VALUES
        ('1', '$post_date', '$post_date_gmt', '".$page['tag']."', '', '".$page['title']."', '', 'publish', 'closed', 'closed', '', '".$page['name']."', '', '', '$post_date', '$post_date_gmt', '$post_parent', '0', 'page')";
			} else {      
        $sql ="INSERT INTO ".$wpdb->posts."
        (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order)
        VALUES
        ('1', '$post_date', '$post_date_gmt', '".$page['tag']."', '', '".$page['title']."', '', 'static', 'closed', 'closed', '', '".$page['name']."', '', '', '$post_date', '$post_date_gmt', '$post_parent', '0')";
			}
      $wpdb->query($sql);
      $post_id = $wpdb->insert_id;
      if($i == 0) {
        $first_id = $post_id;
        }
      $wpdb->query("UPDATE $wpdb->posts SET guid = '" . get_permalink($post_id) . "' WHERE ID = '$post_id'");
      update_option($page['option'],  get_permalink($post_id));
      if($page['option'] == 'shopping_cart_url') {
        update_option('checkout_url',  get_permalink($post_id));
			}
      $newpages = true;
      $i++;
		}
	}
  if($newpages == true) {
    wp_cache_delete('all_page_ids', 'pages');
    $wp_rewrite->flush_rules();
	}
   
   
   /* adds nice names for permalinks for products */
   $check_product_names = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_LIST."`.`id`, `".WPSC_TABLE_PRODUCT_LIST."`.`name`, `".WPSC_TABLE_PRODUCTMETA."`.`meta_key` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_PRODUCTMETA."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_PRODUCTMETA."`.`product_id` WHERE (`".WPSC_TABLE_PRODUCTMETA."`.`meta_key` IN ('url_name') AND  `".WPSC_TABLE_PRODUCTMETA."`.`meta_value` IN (''))  OR ISNULL(`".WPSC_TABLE_PRODUCTMETA."`.`meta_key`)", ARRAY_A);  
  if($check_product_names != null) {
    foreach((array)$check_product_names as $datarow) {
      $tidied_name = trim($datarow['name']);
      $tidied_name = strtolower($tidied_name);
			$url_name = sanitize_title($tidied_name);
      $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` LIKE 'url_name' AND `meta_value` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
      $extension_number = '';
      if($similar_names['count'] > 0) {
        $extension_number = (int)$similar_names['max_number']+1;
			}      
      if(get_product_meta($datarow['id'], 'url_name') != false) {
        $current_url_name = get_product_meta($datarow['id'], 'url_name');
        if($current_url_name != $url_name) {
          $url_name .= $extension_number;
          update_product_meta($datarow['id'], 'url_name', $url_name);
				}
			} else {
        $url_name .= $extension_number;
        add_product_meta($datarow['id'], 'url_name', $url_name, true);
			}
		}
	}
    
  
  /* adds nice names for permalinks for categories */
  $check_category_names = $wpdb->get_results("SELECT DISTINCT `nice-name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` IN ('') AND `active` IN ('1')");
  if($check_category_names != null) {
    $sql_query = "SELECT `id`, `name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN('1')";
    $sql_data = $wpdb->get_results($sql_query,ARRAY_A);    
    foreach((array)$sql_data as $datarow) {
      $tidied_name = trim($datarow['name']);
      $tidied_name = strtolower($tidied_name);
			$url_name = sanitize_title($tidied_name);    
      $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
      $extension_number = '';
      if($similar_names['count'] > 0) {
        $extension_number = (int)$similar_names['max_number']+1;
			}
      $url_name .= $extension_number;
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `nice-name` = '$url_name' WHERE `id` = '".$datarow['id']."' LIMIT 1 ;");
		}
		$wp_rewrite->flush_rules();
	}
    
    
  
  /* Moves images to thumbnails directory */
   // this code should no longer be needed, as most people will be using a sufficiently new version
  $image_dir = WPSC_FILE_PATH."/images/";
  $product_images = WPSC_IMAGE_DIR;
  $product_thumbnails = WPSC_THUMBNAIL_DIR;
  if(!is_dir($product_thumbnails)) {
    @ mkdir($product_thumbnails, 0775);
	}
  $product_list = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `image` != ''",ARRAY_A);
  foreach((array)$product_list as $product) {
    if(!glob($product_thumbnails.$product['image'])) {
      $new_filename = $product['id']."_".$product['image'];
      if(file_exists($image_dir.$product['image'])) {
        copy($image_dir.$product['image'], $product_thumbnails.$new_filename);
        if(file_exists($product_images.$product['image'])) {
          copy($product_images.$product['image'], $product_images.$new_filename);
				}
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$new_filename."' WHERE `id`='".$product['id']."' LIMIT 1");
			} else {
        $imagedir = $product_thumbnails;
        $name = $new_filename;
        $new_image_path = $product_images.$product['image'];
        $imagepath = $product['image'];
        $height = get_option('product_image_height');
        $width  = get_option('product_image_width');
        if(file_exists($product_images.$product['image'])) {
          include("extra_image_processing.php");
          copy($product_images.$product['image'], $product_images.$new_filename);
          $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$new_filename."' WHERE `id`='".$product['id']."' LIMIT 1");
				}
			}
		}
	}	// */
   
}


function wpsc_product_files_htaccess() {
  if(!is_file(WPSC_FILE_DIR.".htaccess")) {
		$htaccess = "order deny,allow\n\r";
		$htaccess .= "deny from all\n\r";
		$htaccess .= "allow from none\n\r";
		$filename = WPSC_FILE_DIR.".htaccess";
		$file_handle = @ fopen($filename, 'w+');
		@ fwrite($file_handle, $htaccess);
		@ fclose($file_handle);
		@ chmod($file_handle, 0665);
  }
}



function wpsc_check_and_copy_files() {
  $upload_path = 'wp-content/plugins/'.WPSC_DIR_NAME;
  
	$wpsc_dirs['files']['old'] = ABSPATH."{$upload_path}/files/";
	$wpsc_dirs['files']['new'] = WPSC_FILE_DIR;
	
	$wpsc_dirs['previews']['old'] = ABSPATH."{$upload_path}/preview_clips/";
	$wpsc_dirs['previews']['new'] = WPSC_PREVIEW_DIR;
	
	// I don't include the thumbnails directory in this list, as it is a subdirectory of the images directory and is moved along with everything else
	$wpsc_dirs['images']['old'] = ABSPATH."{$upload_path}/product_images/";
	$wpsc_dirs['images']['new'] = WPSC_IMAGE_DIR;
	
	$wpsc_dirs['categories']['old'] = ABSPATH."{$upload_path}/category_images/";
	$wpsc_dirs['categories']['new'] = WPSC_CATEGORY_DIR;
	$incomplete_file_transfer = false;
	foreach($wpsc_dirs as $wpsc_dir) {
	  if(is_dir($wpsc_dir['old'])) {
	    $files_in_dir = glob($wpsc_dir['old']."*");
			$stat = stat($wpsc_dir['new']);
			
	    if(count($files_in_dir) > 0) {
	      foreach($files_in_dir as $file_in_dir) {
	        $file_name = str_replace($wpsc_dir['old'], '', $file_in_dir);
	        if( @ rename($wpsc_dir['old'].$file_name, $wpsc_dir['new'].$file_name) ) {
	          if(is_dir($wpsc_dir['new'].$file_name)) {
							$perms = $stat['mode'] & 0000775;
	          } else { $perms = $stat['mode'] & 0000665; }
	          
	          @ chmod( ($wpsc_dir['new'].$file_name), $perms );	
	        } else {
	          $incomplete_file_transfer = true;
	        }
	      }
	    }
	  }
	}
	if($incomplete_file_transfer == true) {
		add_option('wpsc_incomplete_file_transfer', 'default', "", 'true');
	}
	
}


function wpsc_create_upload_directories() {

  // Create the required folders
  $folders = array(
  	WPSC_UPLOAD_DIR,
  	WPSC_FILE_DIR,
  	WPSC_PREVIEW_DIR,
  	WPSC_IMAGE_DIR,
  	WPSC_THUMBNAIL_DIR,
  	WPSC_CATEGORY_DIR,
  	WPSC_USER_UPLOADS_DIR,
  	WPSC_CACHE_DIR,
  	WPSC_UPGRADES_DIR,
  	WPSC_THEMES_PATH,
  );
  foreach ($folders as $folder) {
  	wp_mkdir_p($folder);
    @ chmod( $folder, 0775 );
  //	echo 'Folder:'.$folder.' Creation attempted';
  }
  
	//wpsc_copy_themes_to_uploads();
}


function wpsc_copy_themes_to_uploads() {
  $old_theme_path = WPSC_FILE_PATH . "/themes/";
	$new_theme_path = WPSC_THEMES_PATH;
  $new_dir = @ opendir($new_theme_path);
  $num = 0;
  $file_names = array();
  while(($file = @ readdir($new_dir)) !== false) {
    if(is_dir($new_theme_path.$file) && ($file != "..") && ($file != ".")){
			$file_names[] = $file;
    }
  }
  if(count($file_names) < 1) {
    $old_dir = @ opendir($old_theme_path);
		while(($file = @ readdir($old_dir)) !== false) {
			if(is_dir($old_theme_path.$file) && ($file != "..") && ($file != ".")){
				@ wpsc_recursive_copy($old_theme_path.$file, $new_theme_path.$file);
			}
		}
  }
}

	
 
 /**
  *wpsc_update_remove_product_nulls function,  converts values to decimal to satisfy mySQL strict mode
* * @return boolean true on success, false on failure
 */
function wpsc_update_remove_nulls($colname) {
  global $wpdb;
  //echo "UPDATE  `".WPSC_TABLE_PRODUCT_LIST."` SET `$colname` = CONVERT(`$colname`, DECIMAL)";
	if($wpdb->query("UPDATE  `".WPSC_TABLE_PRODUCT_LIST."` SET `$colname` = 0 WHERE `$colname` NOT REGEXP '^[0-9]+(\.)*[0-9]*'")) {
	  return true;
	} else {
		return false;
	}
} 

/**
  *wpsc_update_image_records function,  moves product images to the images table
* * @return boolean true on success, false on failure
 */
function wpsc_update_image_records($colname) {
  global $wpdb;
  $product_data = $wpdb->get_results("SELECT `id`, `image` FROM  `".WPSC_TABLE_PRODUCT_LIST."` WHERE `image` NOT REGEXP '^[0-9]+$'", ARRAY_A);
  
  foreach((array)$product_data as $product_row) {
		if(file_exists(WPSC_IMAGE_DIR.$product_row['image'])) {
			$image_id = $wpdb->get_var("SELECT `id` FROM  `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '{$product_row['id']}' AND `image` LIKE '{$product_row['image']}' LIMIT 1");
			if($image_id < 1) {
				$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$product_row['id']}', '{$product_row['image']}', null, null )");			
				$image_id = (int) $wpdb->insert_id;
			}
			if($image_id > 0) {
			$success_state[] = 	$wpdb->query("UPDATE  `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$image_id}' WHERE `id` = '{$product_row['id']}' LIMIT 1");
		  }
		} else {
		  $success_state[] = $wpdb->query("UPDATE  `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = null WHERE `id` = '{$product_row['id']}' LIMIT 1");
		}
  }
  //echo "<pre>".print_r($success_state,true)."</pre>";
  //exit();
	// 	if() {
	// 	  return true;
	// 	} else {
	// 		return false;
	// 	}
}


/**
  *wpsc_set_product_creation_dates function,  converts values to decimal to satisfy mySQL strict mode
* * @return boolean true on success, false on failure
 */
function wpsc_set_product_creation_dates($colname) {
  global $wpdb;
	if($wpdb->query("UPDATE  `".WPSC_TABLE_PRODUCT_LIST."` SET `$colname` = NOW() WHERE `$colname` = '0000-00-00 00:00:00'")) {
	  return true;
	} else {
		return false;
	}
}
 

/**
  *wpsc_add_currency_list function,  converts values to decimal to satisfy mySQL strict mode
* * @return boolean true on success, false on failure
 */
function wpsc_add_currency_list() {
  global $wpdb;
  require_once(WPSC_FILE_PATH."/currency_list.php");
  $currency_data  = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_CURRENCY_LIST."`");
  if($currency_data == 0) {
    $currency_array = explode("\n",$currency_sql); 
    foreach($currency_array as $currency_row) {
      $wpdb->query($currency_row);
		}
	}
}
/**
  *wpsc_add_region_list function,  converts values to decimal to satisfy mySQL strict mode
* * @return boolean true on success, false on failure
 */
function wpsc_add_region_list() {
  global $wpdb;
  $add_regions = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_REGION_TAX."`");
  // exit($add_regions);
  if($add_regions < 1) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Alberta', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'British Columbia', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Manitoba', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'New Brunswick', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Newfoundland', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Northwest Territories', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Nova Scotia', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Nunavut', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Ontario', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Prince Edward Island', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Quebec', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Saskatchewan', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Yukon', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Alabama', 'AL', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Alaska', 'AK', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Arizona', 'AZ', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Arkansas', 'AR', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'California', 'CA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Colorado', 'CO', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Connecticut', 'CT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Delaware', 'DE', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Florida', 'FL', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Georgia', 'GA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Hawaii', 'HI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Idaho', 'ID', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Illinois', 'IL', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Indiana', 'IN', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Iowa', 'IA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Kansas', 'KS', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Kentucky', 'KY', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Louisiana', 'LA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Maine', 'ME', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Maryland', 'MD', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Massachusetts', 'MA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Michigan', 'MI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Minnesota', 'MN', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Mississippi', 'MS', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Missouri', 'MO', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Montana', 'MT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Nebraska', 'NE', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Nevada', 'NV', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New Hampshire', 'NH', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New Jersey', 'NJ', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New Mexico', 'NM', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New York', 'NY', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'North Carolina', 'NC', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'North Dakota', 'ND', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Ohio', 'OH', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Oklahoma', 'OK', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Oregon', 'OR', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Pennsylvania', 'PA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Rhode Island', 'RI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'South Carolina', 'SC', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'South Dakota', 'SD', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Tennessee', 'TN', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Texas', 'TX', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Utah', 'UT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Vermont', 'VT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Virginia', 'VA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Washington', 'WA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Washington DC', 'DC', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'West Virginia', 'WV', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Wisconsin', 'WI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Wyoming', 'WY', '0')");
	}
	     
if($wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_REGION_TAX."` WHERE `code`=''") > 0){
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'AB' WHERE `name` IN('Alberta') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'BC' WHERE `name` IN('British Columbia') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'MB' WHERE `name` IN('Manitoba') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'NK' WHERE `name` IN('New Brunswick') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'NF' WHERE `name` IN('Newfoundland') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'NT' WHERE `name` IN('Northwest Territories') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'NS' WHERE `name` IN('Nova Scotia') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'ON' WHERE `name` IN('Ontario') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'PE' WHERE `name` IN('Prince Edward Island') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'PQ' WHERE `name` IN('Quebec') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'SN' WHERE `name` IN('Saskatchewan') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'YT' WHERE `name` IN('Yukon') LIMIT 1 ;");
    $wpdb->query("UPDATE `".WPSC_TABLE_REGION_TAX."` SET `code` = 'NU' WHERE `name` IN('Nunavut') LIMIT 1 ;");
}     
  
}


/**
  *wpsc_add_checkout_fields function,  converts values to decimal to satisfy mySQL strict mode
* * @return boolean true on success, false on failure
 */
function wpsc_add_checkout_fields() {
  global $wpdb;
	$data_forms = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_CHECKOUT_FORMS."`",ARRAY_A);
	
	if($data_forms[0]['count'] == 0) { 
	
   $sql = " INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order`, `unique_name`) VALUES ( '".__('1. Your billing/contact details', 'wpsc')."', 'heading', '0', '0', '', '1', 1,''),
	( '".__('First Name', 'wpsc')."', 'text', '1', '1', '', '1', 2,'billingfirstname'),
	( '".__('Last Name', 'wpsc')."', 'text', '1', '1', '', '1', 3,'billinglastname'),
	( '".__('Address', 'wpsc')."', 'address', '1', '0', '', '1', 4,'billingaddress'),
	( '".__('City', 'wpsc')."', 'city', '1', '0', '', '1', 5,'billingcity'),
	( '".__('Country', 'wpsc')."', 'country', '1', '0', '', '1', 7,'billingcountry'),
	( '".__('Postal Code', 'wpsc')."', 'text', '0', '0', '', '1', 8,'billingpostcode'),
	( '".__('Email', 'wpsc')."', 'email', '1', '1', '', '1', 9,'billingemail'),
	( '".__('2. Shipping details', 'wpsc')."', 'heading', '0', '0', '', '1', 10,'delivertoafriend'),
	( '".__('First Name', 'wpsc')."', 'text', '0', '0', '', '1', 11,'shippingfirstname'),
	( '".__('Last Name', 'wpsc')."', 'text', '0', '0', '', '1', 12,'shippinglastname'),
	( '".__('Address', 'wpsc')."', 'address', '0', '0', '', '1', 13,'shippingaddress'),
	( '".__('City', 'wpsc')."', 'city', '0', '0', '', '1', 14,'shippingcity'),
	( '".__('State', 'wpsc')."', 'text', '0', '0', '', '1', 15,'shippingstate'),
	( '".__('Country', 'wpsc')."', 'delivery_country', '0', '0', '', '1', 16,'shippingcountry'),
	( '".__('Postal Code', 'wpsc')."', 'text', '0', '0', '', '1', 17,'shippingpostcode');";
//	exit($sql);
	$wpdb->query($sql);  
		update_option('country_form_field', $country_form_id[0]['id']);
		update_option('email_form_field', $email_form_id[0]['id']);
		$wpdb->query("INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order`, `unique_name` ) VALUES ( '".__('Phone', 'wpsc')."', 'text', '1', '0', '', '1', '8','billingphone');");
	}
		
}

/**
* wpsc_create_or_update_tables count function,
* * @return boolean true on success, false on failure
*/
function wpsc_create_or_update_tables($debug = false) {
  global $wpdb;	
  // creates or updates the structure of the shopping cart tables
  
  include(WPSC_FILE_PATH.'/updates/database_template.php');
  
  $template_hash = sha1(serialize($wpsc_database_template));
  
  // Filter for adding to or altering the wpsc database template, make sure you return the array your function gets passed, else you will break updating the database tables
	$wpsc_database_template = apply_filters('wpsc_alter_database_template', $wpsc_database_template);
  
  if((get_option('wpsc_database_check') == $template_hash) && ($debug == false)) {
    //return true;
  }
  
  $failure_reasons = array();
  $upgrade_failed = false;
  foreach((array)$wpsc_database_template as $table_name => $table_data) {
    // check that the table does not exist under the correct name, then checkk if there was a previous name, if there was, check for the table under that name too.
    if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") && (!isset($table_data['previous_names']) || (isset($table_data['previous_names']) && !$wpdb->get_var("SHOW TABLES LIKE '{$table_data['previous_names']}'")) )) {
      //if the table does not exixt, create the table
      $constructed_sql_parts = array();
      $constructed_sql = "CREATE TABLE `{$table_name}` (\n";
      
      // loop through the columns
      foreach((array)$table_data['columns'] as $column => $properties) {
        $constructed_sql_parts[] = "`$column` $properties";
      }
      // then through the indexes
      foreach((array)$table_data['indexes'] as $properties) {
        $constructed_sql_parts[] = "$properties";
      }
      $constructed_sql .= implode(",\n", $constructed_sql_parts);
      $constructed_sql .= "\n) ENGINE=MyISAM";
      
			
      // if mySQL is new enough, set the character encoding
			if( method_exists($wpdb, 'db_version') &&  version_compare($wpdb->db_version(), '4.1', '>=')) {
				$constructed_sql .= " CHARSET=utf8";
			}
			$constructed_sql .= ";";
    
      if(!$wpdb->query($constructed_sql)) {
        $upgrade_failed = true;
        $failure_reasons[] = $wpdb->last_error;
      }
            
      if(isset($table_data['actions']['after']['all']) && is_callable($table_data['actions']['after']['all'])) {
				$table_data['actions']['after']['all']();
			}
      //echo "<pre>$constructed_sql</pre>";
    } else {
      // check to see if the new table name is in use
			if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") && (isset($table_data['previous_names']) && $wpdb->get_var("SHOW TABLES LIKE '{$table_data['previous_names']}'"))) {
				$wpdb->query("ALTER TABLE  `{$table_data['previous_names']}` RENAME TO `{$table_name}`;");
				//$wpdb->query("RENAME TABLE `{$table_data['previous_names']}`  TO `{$table_name}`;");
				$failure_reasons[] = $wpdb->last_error;
			}
    
      //check to see if the table needs updating
      $existing_table_columns = array();
      //check and possibly update the character encoding      			
			if( method_exists($wpdb, 'db_version') &&  version_compare($wpdb->db_version(), '4.1', '>=')) {
				$table_status_data = $wpdb->get_row("SHOW TABLE STATUS LIKE '$table_name'", ARRAY_A);
				if($table_status_data['Collation'] != 'utf8_general_ci') {
					$wpdb->query("ALTER TABLE `$table_name`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
				}
			}
      //get the column list
      $existing_table_column_data = $wpdb->get_results("SHOW FULL COLUMNS FROM `$table_name`", ARRAY_A);
      
      if(isset($table_data['actions']['before']['all']) && is_callable($table_data['actions']['before']['all'])) {
				$table_data['actions']['before']['all']();
			}

      foreach((array)$existing_table_column_data as $existing_table_column) {
        $column_name = $existing_table_column['Field'];
        $existing_table_columns[] = $column_name;
        
        $null_match = false;
				if($existing_table_column['Null'] = 'NO') {
				  if(stristr($table_data['columns'][$column_name], "NOT NULL") !== false) {
				    $null_match = true;
					}
				} else {
				  if(stristr($table_data['columns'][$column_name], "NOT NULL") === false) {
				    $null_match = true;
					}
				}
				
        if(isset($table_data['columns'][$column_name]) && ((stristr($table_data['columns'][$column_name], $existing_table_column['Type']) === false) || ($null_match != true))) {
          if(isset($table_data['actions']['before'][$column_name]) && is_callable($table_data['actions']['before'][$column_name])) {
            $table_data['actions']['before'][$column_name]($column_name);
          }        
          if(!$wpdb->query("ALTER TABLE `$table_name` CHANGE `$column_name` `$column_name` {$table_data['columns'][$column_name]} ")) {
						$upgrade_failed = true;
						$failure_reasons[] = $wpdb->last_error;
          }
				}
        
      }
      $supplied_table_columns = array_keys($table_data['columns']);
      
      // compare the supplied and existing columns to find the differences
      $missing_or_extra_table_columns = array_diff($supplied_table_columns, $existing_table_columns);
          
      if(count($missing_or_extra_table_columns) > 0) {
        foreach((array)$missing_or_extra_table_columns as $missing_or_extra_table_column) {
          if(isset($table_data['columns'][$missing_or_extra_table_column])) {
            //table column is missing, add it
            $previous_column = $supplied_table_columns[array_search($missing_or_extra_table_column, $supplied_table_columns)-1];
            if($previous_column != '') {
              $previous_column = "AFTER `$previous_column`";
            }
            $constructed_sql = "ALTER TABLE `$table_name` ADD `$missing_or_extra_table_column` ".$table_data['columns'][$missing_or_extra_table_column]." $previous_column;";
            if(!$wpdb->query($constructed_sql)) {
              $upgrade_failed = true;
              $failure_reasons[] = $wpdb->last_error;
            }
            // run updating functions to do more complex work with default values and the like
            //exit($missing_or_extra_table_column);
            if(is_callable($table_data['actions']['after'][$missing_or_extra_table_column])) {
							$table_data['actions']['after'][$missing_or_extra_table_column]($missing_or_extra_table_column);
            }
          }
        }
      }
      
      if(isset($table_data['actions']['after']['all']) && is_callable($table_data['actions']['after']['all'])) {
				$table_data['actions']['after']['all']();
			}
      // get the list of existing indexes
      $existing_table_index_data = $wpdb->get_results("SHOW INDEX FROM `$table_name`", ARRAY_A);
      $existing_table_indexes = array();
      foreach($existing_table_index_data as $existing_table_index) {
        $existing_table_indexes[] = $existing_table_index['Key_name'];
      }
      
      $existing_table_indexes = array_unique($existing_table_indexes);
      $supplied_table_indexes = array_keys($table_data['indexes']);
      
      // compare the supplied and existing indxes to find the differences
      $missing_or_extra_table_indexes = array_diff($supplied_table_indexes, $existing_table_indexes);
      
      if(count($missing_or_extra_table_indexes) > 0) {
        foreach($missing_or_extra_table_indexes as $missing_or_extra_table_index) {
          if(isset($table_data['indexes'][$missing_or_extra_table_index])) {
            $constructed_sql = "ALTER TABLE `$table_name` ADD ".$table_data['indexes'][$missing_or_extra_table_index].";";
            if(!$wpdb->query($constructed_sql)) {
              $upgrade_failed = true;
              $failure_reasons[] = $wpdb->last_error;
            }
          }
        }
      }
    }
  }
  
	if($upgrade_failed !== true) {
		update_option('wpsc_database_check', $template_hash);
		return true;
	} else {
	  return false;
	}
	//echo "<pre>".print_r($missing_or_extra_table_indexes,true)."</pre>";
}
?>