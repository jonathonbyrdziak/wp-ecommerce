<?php
/**
 * WP eCommerce misc functions
 *
 * These are the WPSC miscellaneous functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 
/**
 * WPSC get state by id function, gets either state code or state name depending on param
 *
 * @since 3.7
 * $param int $id the id for the region
 * @param string $return_value either 'name' or 'code' depending on what you want returned
 */
function wpsc_get_state_by_id($id, $return_value){
	global $wpdb;
	
	$sql = "SELECT `".$return_value."` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id`=".$id;
	$value = $wpdb->get_var($sql);
	return $value;
}


/**
 * WPSC add new user function, validates and adds a new user, for the 
 *
 * @since 3.7
 *
 * @param string $user_login The user's username.
 * @param string $password The user's password.
 * @param string $user_email The user's email (optional).
 * @return int The new user's ID.
 */
 function wpsc_add_new_user($user_login,$user_pass, $user_email) {
	require_once(ABSPATH . WPINC . '/registration.php');
	$errors = new WP_Error();
 	$user_login = sanitize_user( $user_login );
	$user_email = apply_filters( 'user_registration_email', $user_email );

	// Check the username
	if ( $user_login == '' ) {
		$errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.'));
	} elseif ( !validate_username( $user_login ) ) {
		$errors->add('invalid_username', __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.'));
		$user_login = '';
	} elseif ( username_exists( $user_login ) ) {
		$errors->add('username_exists', __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));
	}

	// Check the e-mail address
	if ($user_email == '') {
		$errors->add('empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.'));
	} elseif ( !is_email( $user_email ) ) {
		$errors->add('invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
		$user_email = '';
	} elseif ( email_exists( $user_email ) ) {
		$errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));
	}

	if ( $errors->get_error_code() ) {
		return $errors;
	}
 	$user_id = wp_create_user( $user_login, $user_pass, $user_email );
	if ( !$user_id ) {
		$errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email')));
		return $errors;
	}
	$credentials = array('user_login' => $user_login, 'user_password' => $user_pass, 'remember' => true);
	$user = wp_signon($credentials);
	return $user;

	//wp_new_user_notification($user_id, $user_pass);
 }



/**
 * WPSC product has variations function
 * @since 3.7
 * @param int product id
 * @return bool true or false
 */

function wpsc_product_has_variations($product_id) {
  global $wpdb;
  if($product_id > 0) {
		$variation_count = $wpdb->get_var("SELECT COUNT(`id`) FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN('product') AND `associated_id` IN('{$product_id}')");
		if($variation_count > 0) {
			return true;  
		}
  }
	return false;  
}




function wpsc_post_title_seo($title) {
	global $wpdb, $page_id, $wp_query;
	$new_title = wpsc_obtain_the_title();
	if($new_title != '') {
	  $title = $new_title;
	}
	return stripslashes($title);
}


add_filter('single_post_title','wpsc_post_title_seo');


/**
 * WPSC canonical URL function
 * Needs a recent version 
 * @since 3.7
 * @param int product id
 * @return bool true or false
 */
function wpsc_change_canonical_url($url) {
  global $wpdb, $wpsc_query, $post;

  // Only change the URL is we're viewing a WP e-Commerce page
  if(stristr($post->post_content,'[productspage]')) {

        if (isset($wpsc_query->query_vars['product_url_name'])) {
                $product_url_name = $wpsc_query->query_vars['product_url_name'];
        } else {
                $product_url_name = '';
        }

        // Viewing a single product page
        if ($product_url_name != '') {
                if(!is_numeric($_GET['product_id'])) {
                        $product_id = $wpdb->get_var($wpdb->prepare("SELECT product_id FROM ".WPSC_TABLE_PRODUCTMETA." WHERE meta_key = 'url_name'  AND meta_value = %s ORDER BY product_id DESC LIMIT 1", $product_url_name));
                } else {
                        $product_id = absint($_GET['product_id']);
                }
                if($product_id > 0){
                        $url = wpsc_product_url($product_id);
                }else{
                        $url = get_option('product_list_url');
                }

        // Viewing a category page
        } elseif (absint($wpsc_query->query_vars['category_id']) > 0) {
                $url = wpsc_category_url(absint($wpsc_query->query_vars['category_id']));

                if ( $wpsc_query->query_vars['page'] > 1 ) {
                        if ( get_option( 'permalink_structure' ) ) {
                                $url .= "page/{$wpsc_query->query_vars['page']}/";
                        } else {
                                $url .= "&page_number={$wpsc_query->query_vars['page']}";
                                $url = html_entity_decode( $url );
                        }
                }
        }
  }
  return $url;
}
add_filter('aioseop_canonical_url', 'wpsc_change_canonical_url');



function wpsc_insert_canonical_url() {
	$wpsc_url = wpsc_change_canonical_url(null);
//	exit($wpsc_url);
	echo "<link rel='canonical' href='$wpsc_url' />\n";
}

function wpsc_canonical_url() {
	$wpsc_url = wpsc_change_canonical_url(null);
	if(($wpsc_url != null) && ((count($aioseop_options) <= 1) || (($aioseop_options['aiosp_can'] != '1' && $aioseop_options['aiosp_can'] != 'on'))) ) {
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', 'wpsc_insert_canonical_url');
	}
}
add_action( 'template_redirect', 'wpsc_canonical_url' );





// check for all in one SEO pack and the is_static_front_page function
if(is_callable(array("All_in_One_SEO_Pack",  'is_static_front_page'))) {
  function wpsc_change_aioseop_home_title($title) {
  	global $aiosp, $aioseop_options;
  	
  	if((get_class($aiosp) == 'All_in_One_SEO_Pack') && $aiosp->is_static_front_page()) {
			$aiosp_home_title = $aiosp->internationalize($aioseop_options['aiosp_home_title']);
			$new_title = wpsc_obtain_the_title();
			if($new_title != '') {
				$title = str_replace($aiosp_home_title, $new_title, $title);
			}
		}
		return $title;
  }
  
	add_filter('aioseop_home_page_title', 'wpsc_change_aioseop_home_title');
	//add_filter('aioseop_title_page', 'wpsc_change_aioseop_home_title');
}

function wpsc_set_aioseop_description($data) {
	$replacement_data = wpsc_obtain_the_description();
	if($replacement_data != '') {
	  $data = $replacement_data;
	}
  return $data;
}

add_filter('aioseop_description', 'wpsc_set_aioseop_description');


function wpsc_set_aioseop_keywords($data) {
  global $wpdb, $wp_query, $wpsc_title_data, $aioseop_options;
  
	if(isset($wp_query->query_vars['product_url_name'])) {
	  $product_name = $wp_query->query_vars['product_url_name'];
		$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '{$wp_query->query_vars['product_url_name']}' ) ORDER BY `id` DESC LIMIT 1");
		
		$replacement_data = '';
		$replacement_data_array = array();
		if($aioseop_options['aiosp_use_categories']) {
			$category_list = $wpdb->get_col("SELECT `categories`.`name` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `assoc` , `".WPSC_TABLE_PRODUCT_CATEGORIES."` AS `categories` WHERE `assoc`.`product_id` IN ('{$product_id}') AND `assoc`.`category_id` = `categories`.`id` AND `categories`.`active` IN('1')");
			$replacement_data_array += $category_list;
		}
		$replacement_data_array += wp_get_object_terms($product_id, 'product_tag', array('fields' => 'names'));
		$replacement_data .= implode(",", $replacement_data_array);
		if($replacement_data != '') {
			$data = strtolower($replacement_data);
		}
  }
  
  return $data;
}
add_filter('aioseop_keywords', 'wpsc_set_aioseop_keywords');




/**
* wpsc_populate_also_bought_list function, runs on checking out, populates the also bought list.
*/
function wpsc_populate_also_bought_list() {
  global $wpdb, $wpsc_cart, $wpsc_coupons;
	//exit("<pre>".print_r($variations,true)."</pre>");
	$new_also_bought_data = array();
	foreach($wpsc_cart->cart_items as $outer_cart_item) {
		$new_also_bought_data[$outer_cart_item->product_id] =  array();
		foreach($wpsc_cart->cart_items as $inner_cart_item) {
			if($outer_cart_item->product_id != $inner_cart_item->product_id) {
				$new_also_bought_data[$outer_cart_item->product_id][$inner_cart_item->product_id] = $inner_cart_item->quantity;
			} else  {
			  continue;
			}
		}
	}

	$insert_statement_parts = array();
	foreach($new_also_bought_data as $new_also_bought_id => $new_also_bought_row) {
		$new_other_ids = array_keys($new_also_bought_row); 
		$also_bought_data = $wpdb->get_results("SELECT `id`, `associated_product`, `quantity` FROM `".WPSC_TABLE_ALSO_BOUGHT."` WHERE `selected_product` IN('$new_also_bought_id') AND `associated_product` IN('".implode("','", $new_other_ids)."')", ARRAY_A);
		$altered_new_also_bought_row = $new_also_bought_row;
		
		foreach((array)$also_bought_data as $also_bought_row) {
			$quantity = $new_also_bought_row[$also_bought_row['associated_product']] + $also_bought_row['quantity'];
			
			unset($altered_new_also_bought_row[$also_bought_row['associated_product']]);
			$wpdb->query("UPDATE `".WPSC_TABLE_ALSO_BOUGHT."` SET `quantity` = {$quantity} WHERE `id` = '{$also_bought_row['id']}' LIMIT 1;");
		}
		
		
		if(count($altered_new_also_bought_row) > 0 ) {
			foreach($altered_new_also_bought_row as $associated_product => $quantity) {
				$insert_statement_parts[] = "(".absint($new_also_bought_id).",".absint($associated_product).",".absint($quantity).")";
			}
		}
	}
	
	if(count($insert_statement_parts) > 0) {
		$insert_statement = "INSERT INTO `".WPSC_TABLE_ALSO_BOUGHT."` (`selected_product`, `associated_product`, `quantity`) VALUES ".implode(",\n ", $insert_statement_parts);
		$wpdb->query($insert_statement);
		//echo $insert_statement;
		}
}



function add_product_meta($product_id, $key, $value, $unique = false, $custom = false) {
  global $wpdb, $post_meta_cache, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
    if(($unique == true) && $wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE meta_key = '$key' AND product_id = '$product_id'")) {
      return false;
		}
		if(!is_string($value)) {
			$value = maybe_serialize($value);
		} else {
			$value = $wpdb->escape($value);
		}
    
    if(!$wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE meta_key = '$key' AND product_id = '$product_id'")) {
      $custom = (int)$custom;
      $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (product_id,meta_key,meta_value, custom) VALUES ('$product_id','$key','$value', '$custom')");
		} else {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET meta_value = '$value' WHERE meta_key = '$key' AND product_id = '$product_id'");
		}
    return true;
	}
  return false; 
}
  
function delete_product_meta($product_id, $key, $value = '') {
  global $wpdb, $post_meta_cache, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
    if ( empty($value) ) {
      $meta_id = $wpdb->get_var("SELECT id FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key'");      
      if(is_numeric($meta_id) && ($meta_id > 0)) {
        $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key'");
        }
      } else {
      $meta_id = $wpdb->get_var("SELECT id FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key' AND meta_value = '$value'");
      if(is_numeric($meta_id) && ($meta_id > 0)) {
        $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key' AND meta_value = '$value'");
        }        
      }
  }
  return true;
}


function get_product_meta($product_id, $key, $single = false) {
  global $wpdb, $post_meta_cache, $blog_id;  
  $product_id = (int)$product_id;
  if($product_id > 0) {
    $meta_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id' LIMIT 1");
    //exit($meta_id);
    if(is_numeric($meta_id) && ($meta_id > 0)) {      
      if($single != false) {
        $meta_values = maybe_unserialize($wpdb->get_var("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id' LIMIT 1"));
			} else {
        $meta_values = $wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id'");
				$meta_values = array_map('maybe_unserialize', $meta_values);
			}
		}
	} else {
    $meta_values = false;
	}
	if (is_array($meta_values) && (count($meta_values) == 1)) {
		return array_pop($meta_values);
	} else {
		return $meta_values;
	}
}

function update_product_meta($product_id, $key, $value, $prev_value = '') {
  global $wpdb, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
		if(!is_string($value)) {
			$value = $wpdb->escape(maybe_serialize($value));
		} else {
			$value = $wpdb->escape($value);
		}
	
	if(!empty($prev_value)) {
    $prev_value = $wpdb->escape(maybe_serialize($prev_value));
	}

	
	
  if($wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND product_id = '$product_id'")) {
    if (empty($prev_value)) {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_value` = '$value' WHERE `meta_key` IN('$key') AND product_id = '$product_id'");
      } else {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_value` = '$value' WHERE `meta_key` IN('$key') AND product_id = '$product_id' AND meta_value = '$prev_value'");
      }
    } else {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (product_id,meta_key,meta_value) VALUES ('$product_id','$key','$value')");
    }
  return true;
  }
}


  

 function wpsc_get_country($country_code) {
  global $wpdb;
  $country = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN ('".$country_code."') LIMIT 1");
  return $country; 
}

 function wpsc_get_region($region_code) {
  global $wpdb;
  $region = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` IN('$region_code')");
  return $region; 
}

function nzshpcrt_display_preview_image() {
	  global $wpdb;
	  if(($_GET['wpsc_request_image'] == 'true') || is_numeric($_GET['productid']) || is_numeric($_GET['image_id'])|| isset($_GET['image_name'])) {

	  
		if(function_exists("getimagesize")) {
			if(is_numeric($_GET['productid'])) {
				$product_id = (int)$_GET['productid'];
				$image_data = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1");
				
				if(is_numeric($image_data)) {
					$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_data}' LIMIT 1");
					$imagepath = WPSC_IMAGE_DIR . $image;
				} else {
					$imagepath = WPSC_IMAGE_DIR . $imagedata['image'];
				}
			} else if($_GET['image_id']) {
				$image_id = (int)$_GET['image_id'];
				$results = $wpdb->get_row("SELECT `image`,`product_id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_id}' LIMIT 1");
				$image = $results->image;
				$pid = $results->product_id;
				$thumbnail_info = $wpdb->get_row("SELECT `thumbnail_state`,`image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$pid}' LIMIT 1");
				$thumbnail_state = $thumbnail_info->thumbnail_state;
				$thumbnail_image = $thumbnail_info->image;
				if (($thumbnail_state == 3) && ($image_id == $thumbnail_image)) {
					$imagepath = WPSC_THUMBNAIL_DIR . $image;
				} else {
					$imagepath = WPSC_IMAGE_DIR . $image;
				}

			} else if( $_GET['image_name']) {
				$image = basename($_GET['image_name']);
				$imagepath = WPSC_USER_UPLOADS_DIR . $image;
			} else if( $_GET['category_id']) {
				$category_id = absint($_GET['category_id']);
				$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` = '{$category_id}' LIMIT 1");
				if($image != '') {
					$imagepath = WPSC_CATEGORY_DIR.$image;
				}
			}
			
			if(!is_file($imagepath)) {
				$imagepath = WPSC_FILE_PATH."/images/no-image-uploaded.gif";
			}
			$image_size = @getimagesize($imagepath);
			if(is_numeric($_GET['height']) && is_numeric($_GET['width'])) {
				$height = (int)$_GET['height'];
				$width = (int)$_GET['width'];
			} else {
				$width = $image_size[0];
				$height = $image_size[1];
			}
			if(!(($height > 0) && ($height <= 1024) && ($width > 0) && ($width <= 1024))) { 
				$width = $image_size[0];
				$height = $image_size[1];
			}
			if($product_id > 0) {
				$cache_filename = basename("product_{$product_id}_{$height}x{$width}");
			} else if ($category_id > 0 ) {
				$cache_filename = basename("category_{$category_id}_{$height}x{$width}");
			} else {
				$cache_filename = basename("product_img_{$image_id}_{$height}x{$width}");
			}
			//echo "<pre>".print_r($_GET, true)."</pre>";
			//exit($cache_filename);
			$imagetype = @getimagesize($imagepath);
			$use_cache = false;
			switch($imagetype[2]) {
				case IMAGETYPE_JPEG:
				$extension = ".jpg";
				break;

				case IMAGETYPE_GIF:
				$extension = ".gif";
				break;

				case IMAGETYPE_PNG:
				$extension = ".png";
				break;
			}
			if(file_exists(WPSC_CACHE_DIR.$cache_filename.$extension)) {
				$original_modification_time = filemtime($imagepath);
				$cache_modification_time = filemtime(WPSC_CACHE_DIR.$cache_filename.$extension);
				if($original_modification_time < $cache_modification_time) {
					$use_cache = true;
				}
			}

			if($use_cache === true ) {
				$cache_url = WPSC_CACHE_URL;
				if(is_ssl()) {
					$cache_url = str_replace("http://", "https://", $cache_url);
				}
				header("Location: ".$cache_url.$cache_filename.$extension);
				exit('');
			} else {
				switch($imagetype[2]) {
					case IMAGETYPE_JPEG:
					//$extension = ".jpg";
					$src_img = imagecreatefromjpeg($imagepath);
					$pass_imgtype = true;
					break;

					case IMAGETYPE_GIF:
					//$extension = ".gif";
					$src_img = imagecreatefromgif($imagepath);
					$pass_imgtype = true;
					break;

					case IMAGETYPE_PNG:
					//$extension = ".png";
					$src_img = imagecreatefrompng($imagepath);
					$pass_imgtype = true;
					break;

					default:
					$pass_imgtype = false;
					break;
				}

				if($pass_imgtype === true) {
					$source_w = imagesx($src_img);
					$source_h = imagesy($src_img);

					//Temp dimensions to crop image properly
					$temp_w = $width;
					$temp_h = $height;

					// select our scaling method
					$scaling_method = 'cropping';

					//list($source_h, $source_w) = array($source_w, $source_h);

					// set both offsets to zero
					$offset_x = $offset_y = 0;

					// Here are the scaling methods, non-cropping causes black lines in tall images, but doesnt crop images.
					switch($scaling_method) {
						case  'cropping':
							// if the image is wider than it is high and at least as wide as the target width.
							if (($source_h <= $source_w)) {
								if ($height < $width ) {
									$temp_h = ($width / $source_w) * $source_h;
								} else {
									$temp_w = ($height / $source_h) * $source_w;
								}
							} else {
								$temp_h = ($width / $source_w) * $source_h;
							}
						break;

						case 'non-cropping':
						default:
							if ($height < $width ) {
								$temp_h = ($width / $source_w) * $source_h;
							} else {
								$temp_w = ($height / $source_h) * $source_w;
							}
						break;
					}

					// Create temp resized image
					$temp_img = ImageCreateTrueColor( $temp_w, $temp_h );
					$bgcolor = ImageColorAllocate( $temp_img, 255, 255, 255 );
					ImageFilledRectangle( $temp_img, 0, 0, $temp_w, $temp_h, $bgcolor );
					ImageAlphaBlending( $temp_img, TRUE );
					ImageCopyResampled( $temp_img, $src_img, 0, 0, 0, 0, $temp_w, $temp_h, $source_w, $source_h );

					$dst_img = ImageCreateTrueColor($width,$height);
					$bgcolor = ImageColorAllocate( $dst_img, 255, 255, 255 );
					ImageFilledRectangle( $dst_img, 0, 0, $width, $height, $bgcolor );
					ImageAlphaBlending($dst_img, TRUE );
					if (($imagetype[2]==IMAGETYPE_PNG) ||($imagetype[2]==IMAGETYPE_GIF)){
						//imagecolortransparent($dst_img, $bgcolor);
					}

					// X & Y Offset to crop image properly
					if($temp_w < $width) {
						$w1 = ($width/2) - ($temp_w/2);
					} else if($temp_w == $width) {
						$w1 = 0;
					} else {
						$w1 = ($width/2) - ($temp_w/2);
					}

					if($temp_h < $height) {
						$h1 = ($height/2) - ($temp_h/2);
					} else if($temp_h == $height) {
						$h1 = 0;
					} else {
						$h1 = ($height/2) - ($temp_h/2);
					}

					switch($scaling_method) {
						case  'cropping':
							ImageCopy( $dst_img, $temp_img, $w1, $h1, 0, 0, $temp_w, $temp_h );
						break;

						case 'non-cropping':
						default:
							ImageCopy( $dst_img, $temp_img, 0, 0, 0, 0, $temp_w, $temp_h );
						break;
					}


					ImageAlphaBlending($dst_img, false);
					switch($imagetype[2]) {
						case IMAGETYPE_JPEG:
						header("Content-type: image/jpeg");
						ImagePNG($dst_img);
						ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".jpg");
						@ chmod( WPSC_CACHE_DIR.$cache_filename.".jpg", 0775 );
						break;

						case IMAGETYPE_GIF:
						header("Content-type: image/gif");
						ImagePNG($dst_img);
						ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".gif");
						@ chmod( WPSC_CACHE_DIR.$cache_filename.".gif", 0775 );
						break;

						case IMAGETYPE_PNG:
						header("Content-type: image/png");
						ImagePNG($dst_img);
						ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".png");
						@ chmod( WPSC_CACHE_DIR.$cache_filename.".png", 0775 );
						break;

						default:
						$pass_imgtype = false;
						break;
					}
					exit();
				}
			}
		}
	}
}

add_action('init', 'nzshpcrt_display_preview_image');

//function added to preserve backwards compatibility
function nzshpcrt_listdir($dirname){
	return wpsc_list_dir($dirname);
}

function wpsc_list_dir($dirname) {
  /*
  lists the provided directory, was nzshpcrt_listdir
  */
  $dir = @opendir($dirname);
  $num = 0;
  while(($file = @readdir($dir)) !== false) {
    //filter out the dots and any backup files, dont be tempted to correct the "spelling mistake", its to filter out a previous spelling mistake.
    if(($file != "..") && ($file != ".") && !stristr($file, "~") && !stristr($file, "Chekcout") && !( strpos($file, ".") === 0 )) {
      $dirlist[$num] = $file;
      $num++;
    }
  }
  if($dirlist == null) {
    $dirlist[0] = "paypal.php";
    $dirlist[1] = "testmode.php";
  }
  return $dirlist; 
}

/**
 * wpsc_recursive_copy function, copied from here and renamed: http://nz.php.net/copy
	* Why doesn't PHP have one of these built in?
 
*/
    
 function wpsc_recursive_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					wpsc_recursive_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					@ copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
    }
    closedir($dir);
}



/**
 * wpsc_replace_reply_address function,
 * Replace the email address for the purchase receipts
*/
function wpsc_replace_reply_address($input) {
  $output = get_option('return_email');
  if($output == '') {
    $output = $input;
  }
  return $output;
}

/**
 * wpsc_replace_reply_address function,
 * Replace the email address for the purchase receipts
*/
function wpsc_replace_reply_name($input) {
  $output = get_option('return_name');
  if($output == '') {
    $output = $input;
  }
  return $output;
}

/**
* wpsc_object_to_array, recusively converts an object to an array, for usage with SOAP code

* Copied from here, then modified:
* http://www.phpro.org/examples/Convert-Object-To-Array-With-PHP.html

*/

function wpsc_object_to_array( $object ) {
		if( !is_object( $object ) && !is_array( $object ) ) {
				return $object;
		} else if( is_object( $object ) )	{
				$object = get_object_vars( $object );
		}
		return array_map( 'wpsc_object_to_array', $object );
}


function wpsc_readfile_chunked($filename, $retbytes = true) {
	$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
	$buffer = '';
	$cnt = 0;
	$handle = fopen($filename, 'rb');
	if($handle === false) {
		return false;
	}
	while (!feof($handle)) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
		if($retbytes)	{
			$cnt += strlen($buffer);
		}
	}
	$status = fclose($handle);
	if($retbytes && $status) {
		return $cnt; // return num. bytes delivered like readfile() does.
	}
	return $status;
}

/**
* wpsc_clear_stock_claims, clears the stock claims, runs using wp-cron
*/

function wpsc_clear_stock_claims( ) {
	global $wpdb;
	///wp_mail('thomas.howard@gmail.com', 'test hourly cron', 'wpsc_clear_stock_claims ran');
	/// Delete the old claims on stock
	$old_claimed_stock_timestamp = mktime((date('H') - 1), date('i'), date('s'), date('m'), date('d'), date('Y'));
	$old_claimed_stock_datetime = date("Y-m-d H:i:s", $old_claimed_stock_timestamp);
	$wpdb->query("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `last_activity` < '{$old_claimed_stock_datetime}' AND `cart_submitted` IN ('0')");
}
add_action('wpsc_daily_cron_tasks', 'wpsc_clear_stock_claims');
add_action('wpsc_hourly_cron_tasks', 'wpsc_clear_stock_claims');
/**
 * Description Check PHP version to Compare
 * @access public
 *
 * @param string of version to compare
 * @return boolean true or false
 */
function phpMinV($v)
{
    $phpV = PHP_VERSION;

    if ($phpV[0] >= $v[0]) {
        if (empty($v[2]) || $v[2] == '*') {
            return true;
        } elseif ($phpV[2] >= $v[2]) {
            if (empty($v[4]) || $v[4] == '*' || $phpV[4] >= $v[4]) {
                return true;
            }
        }
    }

    return false;
}
?>
