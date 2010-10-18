<?php
/**
 * WP eCommerce Debug page and functions
 *
 * This is debugging and unsafe updating code to debug or fix specific problems on some sites that is either not safe to run automatically or not usually needed
 * It is unwise to use anything on this page unless you know exactly what it will do and why you need to run it.
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_debug_page() {
	global $wpdb;
	$fixpage = get_option('siteurl').'/wp-admin/admin.php?page=wpsc-sales-logs&amp;subpage=upgrade-purchase-logs';
	?>
	<div class="wrap">
	  <h2>Debugging Page</h2>
	  <p>
	  This is debugging and unsafe updating code to debug or fix specific problems on some sites that is either not safe to run automatically or not usually needed<br />
    It is unwise to use anything on this page unless you know exactly what it will do and why you need to run it.
    </p>
	  <h4>Action List</h4>
		<ul>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=download_links'>Update Download Links</a>
			</li>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=images_reupload'>Image reassociate</a>
			</li>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=product_url_names'>Clean Duplicate Product URL names</a>
			</li>
			
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=redo_product_url_names'>Redo Product URL names</a>
			</li>
			
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=test_copying_themes'>Copy Themes to New Theme Directory</a>
			</li>
			
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=resize_thumbnails'>Resize all Thumbnails and Clean Empty Image Records</a>
			</li>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=filters'>Display filters</a>
			</li>
			
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=phpinfo'>Display phpinfo</a>
			</li>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=wp-cron'>Display wp-cron tasks</a>
			</li>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=wpsc_expire_subscriptions'>Expire Subscriptions</a>
			</li>
			<li>
				<a href='<?php echo $fixpage; ?>'>Fix Purchaselogs</a>
			</li>
		</ul>
		<?php
		if (defined('WPSC_ADD_DEBUG_PAGE') && (constant('WPSC_ADD_DEBUG_PAGE') == true)) {
			?>
			<h4>Development Code List</h4>
			<p> And this code is probably useless for anything other than working out how to write better code to do the same thing,  unless you want to do that, leave it alone</p>
			<ul>
<!--				<li>
					<a href='?page=wpsc-debug&amp;wpsc_debug_action=test_making_product_url_names'>Test Making Product URL Names</a>
				</li>-->
				<li>
					<a href='?page=wpsc-debug&amp;wpsc_debug_action=test_variation_grid'>Test Variation Grid</a>
				</li>

				<li>
					<a href='?page=wpsc-debug&amp;wpsc_debug_action=wp_get_object_terms'>Test wp_get_object_terms</a>
				</li>

				<li>
					<a href='?page=wpsc-debug&amp;wpsc_debug_action=create_also_bought_list'>Create also bought list</a>
				</li>
			</ul>
			<?php
		}
		?>
		<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif; font-size:8px;'><?php
		switch($_GET['wpsc_debug_action']) {
			case 'download_links':
			wpsc_group_and_update_download_links();
			break;

			case 'product_url_names':
			wpsc_clean_product_url_names();
			break;

			case 'redo_product_url_names':
			wpsc_redo_product_url_names();
			break;


			case 'test_copying_themes':
			wpsc_test_copying_themes();
			break;

			case 'test_making_product_url_names':
			wpsc_test_making_product_url_names();
			break;

			case 'resize_thumbnails':
			wpsc_mass_resize_thumbnails_and_clean_images();
			break;
		
			case 'images_reupload':
			wpsc_update_image_records(true);
			break;

			case 'filters':
			global $wp_filter, $merged_filters;
			print_r($wp_filter);
			
			break;
			
			case 'wpsc_expire_subscriptions':
			if(function_exists('wpsc_expire_subscriptions')) {
				wpsc_expire_subscriptions();
			}
			break;
			
			case 'phpinfo':
			echo "</pre>";
			phpinfo();
			echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif; font-size:8px;'>";
			break;
			
			case 'wp-cron':
			$cron = get_option('cron');
			print_r($cron);
			break;
			
			case 'wp_get_object_terms':
			global $wp_taxonomies;
			//register_taxonomy('product_tag', 'product');
			//print_r($wp_taxonomies);
			$tags =  wp_get_object_terms(108, 'product_tag', array('fields' => 'names'));
//  			 wp_set_object_terms(103, $tags, 'product_tag');
			print_r($tags);
			break;
			
			case 'create_also_bought_list':
			//  wpsc_populate_also_bought_list();
			
			break;



			case 'test_variation_grid':
			$variations_processor = new nzshpcrt_variations;
			$product_data['id'] = 106;
			 $output = $variations_processor->variations_grid_view($product_data['id']);
 			echo "</pre>";
 			echo $output;
 			echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif; font-size:8px;'>";
			break;
		}
		?></pre>
	</div>
	<?php
}

function wpsc_test_copying_themes() {
  $old_theme_path = WPSC_FILE_PATH . "/themes/";
	$new_theme_path = WPSC_THEMES_PATH;
  $new_dir = @opendir($new_theme_path);
  $num = 0;
  $file_names = array();
  while(($file = @readdir($new_dir)) !== false) {
    if(is_dir($new_theme_path.$file) && ($file != "..") && ($file != ".")){
			$file_names[] = $file;
    }
  }
  if(count($file_names) < 1) {
    $old_dir = @opendir($old_theme_path);
		while(($file = @readdir($old_dir)) !== false) {
			if(is_dir($old_theme_path.$file) && ($file != "..") && ($file != ".")){
				$success = wpsc_recursive_copy($old_theme_path.$file, $new_theme_path.$file);
				echo "old_file:".$old_theme_path.$file."<br />";
				echo "new_file:".$new_theme_path.$file."<br />";
				echo "<pre>".print_r($success,true)."</pre>";
			}
		}
  }

}


function wpsc_group_and_update_download_links() {
	global $wpdb;
	$unique_file_names = $wpdb->get_col("SELECT DISTINCT `filename` FROM  `".WPSC_TABLE_PRODUCT_FILES."`");
	foreach((array)$unique_file_names as $filename) {
		echo "$filename \n";		
		$file_id_list = array();
		$file_data = $wpdb->get_results("SELECT * FROM  `".WPSC_TABLE_PRODUCT_FILES."` WHERE `filename` IN ('$filename')", ARRAY_A);
		foreach($file_data as $file_row) {
			$file_id_list[] = $file_row['id'];
		}
		$product_data = $wpdb->get_row("SELECT * FROM  `".WPSC_TABLE_PRODUCT_LIST."` WHERE `file` IN ('".implode("', '", $file_id_list)."') AND `active` IN('1') ORDER BY `id` DESC LIMIT 1 ",ARRAY_A);
		$product_id = $product_data['id'];
		if($product_id > 0) {
			if($wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `product_id` = '{$product_id}' WHERE `id` IN ('".implode("', '", $file_id_list)."')")) {	
				if($wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `product_id` = '{$product_id}' WHERE `fileid` IN ('".implode("', '", $file_id_list)."')")) {
					echo "$filename done \n";
				}
			}
		}
	}
}

/**
* wpsc_clean_product_url_names, cleans dupicates
*/
function wpsc_clean_product_url_names() {
	global $wpdb;
	
	$duplicated_meta_data = $wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('url_name') GROUP BY `meta_value` HAVING COUNT(`meta_value`) > 1 ");
	
	$product_data = $wpdb->get_results("SELECT DISTINCT `products`.* FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` LEFT JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `products` ON `meta`.`product_id` =  `products`.`id` WHERE `meta`.`meta_key` IN('url_name') AND `meta`.`meta_value` IN('".implode("', '", $duplicated_meta_data)."') AND `products`.`active` = '1' ORDER BY `meta`.`meta_value` DESC", ARRAY_A);
	
	foreach((array)$product_data as $product_row) {
		if($product_row['name'] != '') {
			$tidied_name = strtolower(trim(stripslashes($product_row['name'])));
			$url_name = sanitize_title($tidied_name);
			$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ('url_name') AND `meta_value` REGEXP '^($url_name){1}[[:digit:]]*$' ",ARRAY_A);
			$extension_number = '';
			if($similar_names['count'] > 0) {
				$extension_number = (int)$similar_names['max_number']+1;
			}
			$url_name .= $extension_number;
			echo "{$product_row['name']} => {$url_name}\n\r";
			update_product_meta($product_row['id'], 'url_name', $url_name);
		}
	}	
}

/**
* wpsc_redo_product_url_names, deletes all product URL names, then remakes then
*/


function wpsc_redo_product_url_names() {
	global $wpdb;
	
	$product_data = $wpdb->get_results("SELECT DISTINCT `products`.* FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` LEFT JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `products` ON `meta`.`product_id` =  `products`.`id` WHERE `products`.`active` = '1' ORDER BY `meta`.`meta_value` DESC", ARRAY_A);
	
	foreach((array)$product_data as $product_row) {
	  $product_id = $product_row['id'];
	  $post_data = $product_row;

	  
		if($post_data['name'] != '') {
			$existing_name = get_product_meta($product_id, 'url_name');
			$tidied_name = strtolower(trim(stripslashes($post_data['name'])));
			$url_name =  sanitize_title($tidied_name);
			
  		$similar_names = (array)$wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` NOT IN('{$product_id}}') AND `meta_key` IN ('url_name') AND `meta_value` REGEXP '^(".$wpdb->escape(preg_quote($url_name))."){1}[[:digit:]]*$' ");

			echo "<strong>Product {$product_id}:</strong> {$product_row['name']}\n";
			echo "Current Name: {$existing_name}\n";
			echo "Originally Proposed Name: {$url_name}\n";
			
			if(array_search($url_name, $similar_names) !== false) {
				$i = 0;
				do {
					$i++;
					echo "Proposed Name No.$i: ".($url_name.$i)."\n";
				} while(array_search(($url_name.$i), $similar_names) !== false);
				$url_name .= $i;
			}
			echo "Accepted Name: {$url_name}\n";

		
			if($existing_name != $url_name) {
				update_product_meta($product_id, 'url_name', $url_name);
			}
			
			echo "\n";
		}
	}	
}
 
function wpsc_recreate_product_url_names() {
  global $wpdb;
	$product_data = $wpdb->get_results("SELECT `id`, `name` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1')", ARRAY_A);
	//echo "<pre>".print_r($product_data,true)."</pre>";
	echo "<pre>";
	foreach($product_data as $product_row) {
		$product_id = $product_row['id'];
		$tidied_name = trim($product_row['name']);
		$tidied_name = strtolower($tidied_name);
		$url_name = sanitize_title($tidied_name);

		echo "<strong>Product {$product_id}:</strong> {$product_row['name']}\n";

		echo "Originally Proposed Name: {$url_name}\n";
		$similar_names = (array)$wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` NOT IN('{$product_id}}') AND `meta_key` IN ('url_name') AND `meta_value` REGEXP '^(".$wpdb->escape(preg_quote($url_name))."){1}[[:digit:]]*$' ");

		if(array_search($url_name, $similar_names) !== false) {
			// If it is, try to add a number to the end, if that is taken, try the next highest number...
			$i = 0;
			do {
				$i++;
				if($i > 100) {
					break;
				}
				echo "Proposed Name No.$i: ".($url_name.$i)."\n";
			} while(array_search(($url_name.$i), $similar_names) !== false);
			// Concatenate the first number found that wasn't taken
			$url_name .= $i;
		}

		echo "Accepted Name: {$url_name}\n";
		$existing_name = get_product_meta($product_id, 'url_name', true);
		if(is_array($existing_name )) {
			$existing_name = array_pop($existing_name);
		}
		if($existing_name != $url_name) {
			update_product_meta($product_id, 'url_name', $url_name);
		}

		echo "\n\n\n";
	}

}
 
function wpsc_mass_resize_thumbnails_and_clean_images(){
  global $wpdb;
	$height = get_option('product_image_height');
	$width  = get_option('product_image_width');
  
	$product_data = $wpdb->get_results("SELECT `product`.`id`, `product`.`image` AS `image_id`, `images`.`image` AS `file`  FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `product` INNER JOIN  `".WPSC_TABLE_PRODUCT_IMAGES."` AS `images` ON `product`.`image` = `images`.`id` WHERE `product`.`image` > 0 ",ARRAY_A);
	//print_r($product_data);
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
	$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` IN('0')");
}
?>