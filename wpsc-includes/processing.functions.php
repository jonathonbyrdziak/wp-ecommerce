<?php	
/**
	* wpsc_decrement_claimed_stock method 
	*
	* @param float a price
	* @return string a price with a currency sign
*/
function wpsc_decrement_claimed_stock($purchase_log_id) {
  global $wpdb;
  $all_claimed_stock = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN('%s') AND `cart_submitted` IN('1')", $purchase_log_id), ARRAY_A);
	
	foreach((array)$all_claimed_stock as $claimed_stock) {
	  // for people to have claimed stock, it must have been available to take, no need to look at the existing stock, just subtract from it
	  // If this is ever wrong, and you get negative stock, do not fix it here, go find the real cause of the problem 
		if($claimed_stock['variation_stock_id'] > 0) {
			$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_VARIATION_PROPERTIES."` SET `stock` = (`stock` - %s)  WHERE `id` = '%d' LIMIT 1", $claimed_stock['stock_claimed'], $claimed_stock['variation_stock_id']));
		$sql_query = "SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '" . $claimed_stock['variation_stock_id'] . "'";
			$remaining_stock = $wpdb->get_row($sql_query, ARRAY_A);
			$sql_query1 = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '" . $remaining_stock['product_id'] . "'";
			$product_data = $wpdb->get_row($sql_query1, ARRAY_A);
			if($product_data["quantity_limited"] == 1 && $remaining_stock["stock"]==0 && get_product_meta($product_data['id'],'unpublish_oos',true) == 1){
				$sql_query = "SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE product_id='" . $product_data['id'] . "'";
				$variation_data = $wpdb->get_results($sql_query, ARRAY_A);
				$real_stock = 0;
				foreach ((array)$variation_data as $possible_stock){
					$real_stock += $possible_stock["stock"] * $possible_stock["visibility"];
				}
				if ($real_stock == 0)
				{
					wp_mail(get_option('admin_email'), $product_data["name"] . __(' is out of stock', 'wpsc'), __('Remaining stock of ', 'wpsc') . $product_data["name"] . __(' and its variations is 0. Product was unpublished.', 'wpsc'));
					$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `publish` = '0'  WHERE `id` = '%d' LIMIT 1", $product_data['id']));
				}
				else{
				$sql_query2 = "SELECT `".WPSC_TABLE_VARIATION_VALUES."`.`name`, `".WPSC_TABLE_VARIATION_COMBINATIONS."`.`value_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` INNER JOIN `".WPSC_TABLE_VARIATION_VALUES."` ON `".WPSC_TABLE_VARIATION_COMBINATIONS."`.`value_id`=`".WPSC_TABLE_VARIATION_VALUES."`.`id` WHERE `".WPSC_TABLE_VARIATION_COMBINATIONS."`.`priceandstock_id` = '" . $remaining_stock['id'] . "'";
				$variation_data = $wpdb->get_row($sql_query2, ARRAY_A);
				wp_mail(get_option('admin_email'), $product_data["name"] . " " . $variation_data["name"] . __(' is out of stock', 'wpsc'), __('Remaining stock of ', 'wpsc') . $product_data["name"] . " " . $variation_data["name"] . __(' is 0. Product variation was set to invisible.', 'wpsc'));
				$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` SET `visible` = '0'  WHERE `value_id` = '%d' AND `product_id` = '%d' LIMIT 1", $variation_data['value_id'], $product_data["id"]));
				}
			}
		} 
		else {
			$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `quantity` = (`quantity` - %s)  WHERE `id` = '%d' LIMIT 1", $claimed_stock['stock_claimed'], $claimed_stock['product_id']));
			$sql_query = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '" . $claimed_stock['product_id'] . "'";
			$remaining_stock = $wpdb->get_row($sql_query, ARRAY_A);
			if($remaining_stock["quantity_limited"] == 1 && $remaining_stock["quantity"]==0 && get_product_meta($claimed_stock['product_id'],'unpublish_oos',true) == 1){
				wp_mail(get_option('admin_email'), $remaining_stock["name"] . __(' is out of stock', 'wpsc'), __('Remaining stock of ', 'wpsc') . $remaining_stock["name"] . __(' is 0. Product was unpublished.', 'wpsc'));
				$wpdb->query($wpdb->prepare("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `publish` = '0'  WHERE `id` = '%d' LIMIT 1", $claimed_stock['product_id']));}
			
		}

	}
	$wpdb->query($wpdb->prepare("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN ('%s')", $purchase_log_id));
}
  
/**
 *	wpsc_get_currency_symbol
 *	@param does not receive anything
 *  @return returns the currency symbol used for the shop
*/  
function wpsc_get_currency_symbol(){
	global $wpdb;
	$currency_type = get_option('currency_type');
	$wpsc_currency_data = $wpdb->get_var("SELECT `symbol` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1") ;
	return  $wpsc_currency_data;}  
  
/**
* All the code below here needs commenting and looking at to see if it needs to be altered or disposed of.
* Correspondingly, all the code above here has been commented, uses the wpsc prefix, and has been made for or modified to work with the object oriented cart code.
*/


function nzshpcrt_currency_display($price_in, $tax_status, $nohtml = false, $id = false, $no_dollar_sign = false) {
  global $wpdb, $wpsc_currency_data;
  $currency_sign_location = get_option('currency_sign_location');
  $currency_type = get_option('currency_type');
  if(count($wpsc_currency_data) < 3) {
		$wpsc_currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
  }
  $price_out = null;

  $price_out =  number_format($price_in, 2, '.', ',');

  if($wpsc_currency_data['symbol'] != '') {
    if($nohtml == false) {
      $currency_sign = $wpsc_currency_data['symbol_html'];
		} else {
			$currency_sign = $wpsc_currency_data['symbol'];
		}
	} else {
		$currency_sign = $wpsc_currency_data['code'];
	}

  switch($currency_sign_location) {
    case 1:
    $output = $price_out.$currency_sign;
    break;

    case 2:
    $output = $price_out.' '.$currency_sign;
    break;

    case 4:
    $output = $currency_sign.'  '.$price_out;
    break;
    
    case 3:
    default:
    $output = $currency_sign.$price_out;
    break;
	}

  if($nohtml == true) {
    $output = "".$output."";
	} else {
		$output = "<span class='pricedisplay'>".$output."</span>";
    //$output = "".$output."";
	}
      
  if($no_dollar_sign == true) {
    return $price_out;
	}
  return $output;
}
  
  
  function nzshpcrt_find_total_price($purchase_id,$country_code) {
    global $wpdb;
    if(is_numeric($purchase_id)) {
      $purch_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='".$purchase_id."'";
      $purch_data = $wpdb->get_row($purch_sql,ARRAY_A) ;

      $cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase_id."";
      $cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
      if($cart_log != null) {
        $all_donations = true;
        $all_no_shipping = true;
        foreach($cart_log as $cart_row) {
          $productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$cart_row['prodid']."";
          $product_data = $wpdb->get_results($productsql,ARRAY_A); 
        
          $variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$cart_row['id']."'";
          $variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
          $variation_count = count($variation_data);
          $price = ($cart_row['price'] * $cart_row['quantity']);          
          
          if($purch_data['shipping_country'] != '') {
            $country_code = $purch_data['shipping_country'];
					}
            
          if($cart_row['donation'] == 1) {
            $shipping = 0;
					} else {
            $all_donations = false;
					}
          
          if($cart_row['no_shipping'] == 1) {
            $shipping = 0;
					} else {
            $all_no_shipping = false;
					}

          if(($cart_row['donation'] != 1) && ($cart_row['no_shipping'] != 1)) {
            $shipping = nzshpcrt_determine_item_shipping($cart_row['prodid'], $cart_row['quantity'], $country_code);
					}
          $endtotal += $shipping + $price;
				}
        if(($all_donations == false) && ($all_no_shipping == false)){
          if($purch_data['base_shipping'] > 0) {
						$base_shipping = $purch_data['base_shipping'];
					} else {
						$base_shipping = nzshpcrt_determine_base_shipping(0, $country_code);
					}
					$endtotal += $base_shipping;
				}
        
        if($purch_data['discount_value'] > 0) {
					$endtotal -= $purch_data['discount_value'];
					if($endtotal < 0) {
						$endtotal = 0;
					}
        }
          
			}
      return $endtotal;
		}
	}
	
	function nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code) {    
    global $wpdb;
    if(is_numeric($product_id) && (get_option('do_not_use_shipping') != 1) && ($_SESSION['quote_shipping_method'] == 'flatrate')) {
      $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='$product_id' LIMIT 1";
      $product_list = $wpdb->get_row($sql,ARRAY_A) ;
      if($product_list['no_shipping'] == 0) {
        //if the item has shipping
        if($country_code == get_option('base_country')) {
          $additional_shipping = $product_list['pnp'];
				} else {
          $additional_shipping = $product_list['international_pnp'];
				}          
        $shipping = $quantity * $additional_shipping;
			} else {
        //if the item does not have shipping
        $shipping = 0;
			}
		} else {
      //if the item is invalid or all items do not have shipping
			$shipping = 0;
		}
    return $shipping;    
	}
  function nzshpcrt_determine_base_shipping($per_item_shipping, $country_code) {    
    global $wpdb, $wpsc_shipping_modules;
		$custom_shipping = get_option('custom_shipping_options');
    if((get_option('do_not_use_shipping') != 1) && (count($custom_shipping) > 0)) {
			if(array_search($_SESSION['quote_shipping_method'], (array)$custom_shipping) === false) {
			  //unset($_SESSION['quote_shipping_method']);
			}
			
			$shipping_quotes = null;
			if($_SESSION['quote_shipping_method'] != null) {
				// use the selected shipping module
			  $shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
			} else {
			  // otherwise select the first one with any quotes
				foreach((array)$custom_shipping as $shipping_module) {
					// if the shipping module does not require a weight, or requires one and the weight is larger than zero
					if(($custom_shipping[$shipping_module]->requires_weight != true) or (($custom_shipping[$shipping_module]->requires_weight == true) and (shopping_cart_total_weight() > 0))) {
						$_SESSION['quote_shipping_method'] = $shipping_module;
						$shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
						if(count($shipping_quotes) > 0) { // if we have any shipping quotes, break the loop.
							break;
						}
					}
				}
			}
			
			//echo "<pre>".print_r($_SESSION['quote_shipping_method'],true)."</pre>";
			if(count($shipping_quotes) < 1) {
			$_SESSION['quote_shipping_option'] = '';
			}
			if(($_SESSION['quote_shipping_option'] == null) && ($shipping_quotes != null)) {
				$_SESSION['quote_shipping_option'] = array_pop(array_keys(array_slice($shipping_quotes,0,1)));
			}
			foreach((array)$shipping_quotes as $shipping_quote) {
				foreach((array)$shipping_quote as $key=>$quote) {
					if($key == $_SESSION['quote_shipping_option']) {
					  $shipping = $quote;
					}
				}
			}
		} else {
      $shipping = 0;
		}
    return $shipping;
	}
  
function admin_display_total_price($start_timestamp = '', $end_timestamp = '') {
  global $wpdb;
  if(($start_timestamp != '') && ($end_timestamp != '')) {
    $sql = "SELECT SUM(`totalprice`) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN (2,3,4) AND `date` BETWEEN '$start_timestamp' AND '$end_timestamp'";
	} else {
		$sql = "SELECT SUM(`totalprice`) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN (2,3,4) AND `date` != ''";
	}
  $total = $wpdb->get_var($sql);
  return $total;
}
  

function calculate_product_price($product_id, $variations = false, $no_special=false) {
  global $wpdb;
  if(is_numeric($product_id)) {
    if(is_array($variations) && (count($variations) >= 1)) {
      $variation_count = count($variations);
      $variations = array_values($variations);
      array_walk($variations, 'wpsc_sanitise_keys');
		}
	
    /// the start of the normal price determining code.
    if($variation_count >= 1) {
      // if we have variations, grab the individual price for them. 
      $variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variations)."')");
      asort($variation_ids);         
      $all_variation_ids = implode(",", $variation_ids);
      
      
      $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '$product_id' AND `value_id` IN ( '".implode("', '",$variations )."' ) AND `all_variation_ids` IN('$all_variation_ids') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variations)."' LIMIT 1");
      
      
      $price = $wpdb->get_var("SELECT `price` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1");
    } else {	
      $product_data = $wpdb->get_row("SELECT `price`,`special`,`special_price` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$product_id."' LIMIT 1",ARRAY_A);
     // echo '<span style="color:#f00;">'.print_r($product_data, true).'</span><br />'.;

     
      if(($product_data['special_price'] > 0) && (($product_data['price'] - $product_data['special_price']) >= 0) && ($no_special == false)) {
        $price = $product_data['price'] - $product_data['special_price'];
      } else {
        $price = $product_data['price'];
       
      }
    }
	} else {
		$price = false;
	}
  $price = apply_filters('wpsc_do_convert_price', $price);
  return $price;
}
  
function check_in_stock($product_id, $variations, $item_quantity = 1) {
  global $wpdb;
  $product_id = (int)$product_id;
  $item_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
  
  $item_stock = null;
  $variation_count = count($variations);
  if($variation_count > 0) {
    foreach($variations as $variation_id) {
      if(is_numeric($variation_id)) {
        $variation_ids[] = $variation_id;
			}
		}
    if(count($variation_ids) > 0) {
      
        $actual_variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variation_ids)."')");
        asort($actual_variation_ids);         
        $all_variation_ids = implode(",", $actual_variation_ids);
    
    
      $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product_id}' AND `value_id` IN ( '".implode("', '",$variation_ids )."' ) AND `all_variation_ids` IN('$all_variation_ids') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variation_ids)."' LIMIT 1");
      
      $variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
      
      $item_stock = $variation_stock_data['stock'];
		}
	}
    
  if($item_stock === null) {
    $item_stock = $item_data['quantity'];
	}
  
  if((($item_data['quantity_limited'] == 1) && ($item_stock > 0) && ($item_stock >= $item_quantity)) || ($item_data['quantity_limited'] == 0))  {
    $output = true;
	} else {
		$output = false;
	}
  return $output;
}
 
  
  
function wpsc_item_process_image($id, $input_file, $output_filename, $width = 0, $height = 0, $resize_method = 1, $return_imageid = false) {
//  the function for processing images, takes a product_id, input_file outout file name, height and width
	global $wpdb;
	//$_FILES['image']['tmp_name']
	//$_FILES['image']['name']
	if(preg_match("/\.(gif|jp(e)*g|png){1}$/i",$output_filename) && apply_filters( 'wpsc_filter_file', $input_file )) {
		//$active_signup = apply_filters( 'wpsc_filter_file', $_FILES['image']['tmp_name'] );
		if(function_exists("getimagesize")) {
			$image_name = basename($output_filename);
			if(is_file((WPSC_IMAGE_DIR.$image_name))) {
				$name_parts = explode('.',basename($image_name));
				$extension = array_pop($name_parts);
				$name_base = implode('.',$name_parts);
				$dir = glob(WPSC_IMAGE_DIR."$name_base*");
				
				foreach($dir as $file) {
					$matching_files[] = basename($file);
				}
				$image_name = null;
				$num = 2;
				//  loop till we find a free file name, first time I get to do a do loop in yonks
				do {
					$test_name = "{$name_base}-{$num}.{$extension}";
					if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
						$image_name = $test_name;
					}
					$num++;
				} while ($image_name == null);
			}			
			
			//exit("<pre>".print_r($image_name,true)."</pre>");
			
			$new_image_path = WPSC_IMAGE_DIR.$image_name;
			
			// sometimes rename doesn't work, if the file is recently uploaded, use move_uploaded_file instead
			if(is_uploaded_file($input_file)) {
				move_uploaded_file($input_file, $new_image_path);
			} else {
				rename($input_file, $new_image_path);
			}
			$stat = stat( dirname( $new_image_path ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( $new_image_path, $perms );
			
			switch($resize_method) {
				case 2:
				if($height < 1) {
					$height = get_option('product_image_height');
				}
				if($width < 1) {
					$width  = get_option('product_image_width');
				}
				break;


				case 0:
				$height = (int)null;
				$width  = (int)null;
				break;

				case 1:
				default:
				$height = (int)get_option('product_image_height');
				$width  = (int)get_option('product_image_width');
				break;
			}
					if($width < 1) {
						$width = 96;
					}
					if($height < 1) {
						$height = 96;
					}	     
				image_processing($new_image_path, (WPSC_THUMBNAIL_DIR.$image_name), $width, $height);
// 			}
			$sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$id}', '{$image_name}', '{$width}', '{$height}' )";
			$wpdb->query($sql);
			$image_id = (int) $wpdb->insert_id;			
			$updatelink_sql = "UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$image_id."', `thumbnail_image` = '".$thumbnail_image."'  WHERE `id` = '$id'";
			$wpdb->query($updatelink_sql);
			//exit($sql.'<br />image is about to be stored in the DB<br />'.$updatelink_sql);

			if(function_exists('getimagesize')) {
				$imagetype = getimagesize(WPSC_THUMBNAIL_DIR.$image_name);
				update_product_meta($id, 'thumbnail_width', $imagetype[0]);
				update_product_meta($id, 'thumbnail_height', $imagetype[1]);
			}
			
			
			$image = $wpdb->escape($image_name);
		} else {
			$image_name = basename($output_filename);
			if(is_file((WPSC_IMAGE_DIR.$image_name))) {
				$name_parts = explode('.',basename($image_name));
				$extension = array_pop($name_parts);
				$name_base = implode('.',$name_parts);
				$dir = glob(WPSC_IMAGE_DIR."$name_base*");
				
				foreach($dir as $file) {
					$matching_files[] = basename($file);
				}
				$image_name = null;
				$num = 2;
				//  loop till we find a free file name
				do {
					$test_name = "{$name_base}-{$num}.{$extension}";
					if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
						$image_name = $test_name;
					}
					$num++;
				} while ($image_name == null);
			}
			$new_image_path = WPSC_IMAGE_DIR.$image_name;
			move_uploaded_file($input_file, $new_image_path);
			$stat = stat( dirname( $new_image_path ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( $new_image_path, $perms );
			$image = $wpdb->escape($image_name);
		}
	} else {
			$image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".(int)$id."' LIMIT 1",ARRAY_A);
		  $image = false;
	}
	if($return_imageid == true) {
		return array('image_id' => $image_id, 'filename' => $image);
	} else {
		return $image;
  }
}

function old_wpsc_item_process_file($mode = 'add') {
  global $wpdb;
  	$files = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_PRODUCT_FILES." ORDER BY id ASC", ARRAY_A);
		if (is_array($files)){
			foreach($files as $file){
				$file_names[] = $file['filename'];
				$file_hashes[] = $file['idhash'];
			}
		}
		
	if(apply_filters( 'wpsc_filter_file', $_FILES['file']['tmp_name'] )) {
	  // initialise $idhash to null to prevent issues with undefined variables and error logs
	  $idhash = null;
		switch($mode) {
			case 'edit':
	   		/* if we are editing, grab the current file and ID hash */ 
			$product_id = $_POST['prodid'];
			$fileid_data = $wpdb->get_results("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
			
			case 'add':
			default:
			/* if we are adding, make a new file row and get the ID of it */
			$timestamp = time();
			$query_results = $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_FILES."` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '', '', '', '$timestamp');");
			$fileid = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `".WPSC_TABLE_PRODUCT_FILES."`");
			break;
		}
	
		/* if there is no idhash, generate it */
		if($idhash == null) {
			$idhash = sha1($fileid);
			if($idhash == '') {
			  // if sha1 doesnt spit an error, but doesnt return anything either (it has done so on some servers)
				$idhash = md5($fileid);
			}
		}
		// if needed, we can add code here to stop hash doubleups in the unlikely event that they shoud occur
	
		$mimetype = wpsc_get_mimetype($_FILES['file']['tmp_name']);
		
		$filename = basename($_FILES['file']['name']);
		
		
		if (in_array($_FILES['file']['name'],(array)$file_names)){
			$i=0;
			$new_name = $_FILES['file']['name'].".old";
			while(file_exists(WPSC_FILE_DIR.$new_name)){
				$new_name = $_FILES['file']['name'].".old_".$i;
				$i++;
			}
			$old_idhash_id = array_search($_FILES['file']['name'],(array)$file_names);
			$old_idhash = $file_hashes[$old_idhash_id];
			while(!file_exists(WPSC_FILE_DIR.$old_idhash)){
				unset($file_hashes[$old_idhash_id]);
				unset($file_names[$old_idhash_id]);
				
				$old_idhash_id = array_search($_FILES['file']['name'],(array)$file_names);
				$old_idhash = $file_hashes[$old_idhash_id];
			}
			copy(WPSC_FILE_DIR.$old_idhash, WPSC_FILE_DIR.$new_name);
			unlink(WPSC_FILE_DIR.$old_idhash);
		}
		if(move_uploaded_file($_FILES['file']['tmp_name'],(WPSC_FILE_DIR.$idhash)))	{
			$stat = stat( dirname( (WPSC_FILE_DIR.$idhash) ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( (WPSC_FILE_DIR.$idhash), $perms );	
			if(function_exists("make_mp3_preview"))	{
				if((!isset($_FILES['preview_file']['tmp_name']))) {
				  // if we can generate a preview file, generate it (most can't due to sox being rare on servers and sox with MP3 support being even rarer), thus this needs to be enabled by editing code
					make_mp3_preview((WPSC_FILE_DIR.$idhash), (WPSC_PREVIEW_DIR.$idhash.".mp3"));
					$preview_filepath = (WPSC_PREVIEW_DIR.$idhash.".mp3");
				} else if(file_exists($_FILES['preview_file']['tmp_name'])) {    
					$preview_filename = basename($_FILES['preview_file']['name']);
					$preview_mimetype = wpsc_get_mimetype($_FILES['preview_file']['tmp_name']);
					copy($_FILES['preview_file']['tmp_name'], (WPSC_PREVIEW_DIR.$preview_filename));
					$preview_filepath = (WPSC_PREVIEW_DIR.$preview_filename);
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '$fileid' LIMIT 1");
				}
				$stat = stat( dirname($preview_filepath));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $preview_filepath, $perms );	
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `filename` = '".$wpdb->escape($filename)."', `mimetype` = '$mimetype', `idhash` = '$idhash' WHERE `id` = '$fileid' LIMIT 1");
		}
		if($mode == 'edit') {			
      //if we are editing, update the file ID in the product row, this cannot be done for add because the row does not exist yet.
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		}
		return $fileid;
  } else {
		return false;
  }
}

function old_wpsc_item_reassign_file($selected_product_file, $mode = 'add') {
  global $wpdb;
	// initialise $idhash to null to prevent issues with undefined variables and error logs
	$idhash = null;
	if($mode == 'edit') {
		/* if we are editing, grab the current file and ID hash */ 
		$product_id = (int)$_POST['prodid'];
		if($selected_product_file == '.none.') {
			// unlikely that anyone will ever upload a file called .none., so its the value used to signify clearing the product association
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '0' WHERE `id` = '$product_id' LIMIT 1");
			return null;
		}
		
		// if we already use this file, there is no point doing anything more.
		$current_fileid = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
		if($current_fileid > 0) {
			$current_file_data = $wpdb->get_row("SELECT `id`,`idhash` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id` = '$current_fileid' LIMIT 1",ARRAY_A);
			if(basename($selected_product_file) == $file_data['idhash']) {
				return $current_fileid;
			}
		}
	}

	
	$selected_product_file = basename($selected_product_file);
	if(file_exists(WPSC_FILE_DIR.$selected_product_file)) {
		$timestamp = time();
		$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `idhash` IN('".$wpdb->escape($selected_product_file)."') LIMIT 1", ARRAY_A);
		$fileid = (int)$file_data['id'];
		if($fileid < 1) { // if the file does not have a database row, add one.
		  $mimetype = wpsc_get_mimetype(WPSC_FILE_DIR.$selected_product_file);
		  $filename = $idhash = $selected_product_file;
			$timestamp = time();
			$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_FILES."` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '{$filename}', '{$mimetype}', '{$idhash}', '{$timestamp}');");
			$fileid = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `date` = '{$timestamp}' AND `filename` IN ('{$filename}')");
		}
		if($mode == 'edit') {
      //if we are editing, update the file ID in the product row, this cannot be done for add because the row does not exist yet.
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		}
	}	
	return $fileid;
}

function wpsc_get_mimetype($file, $check_reliability = false) {
  // Sometimes we need to know how useless the result from this is, hence the "check_reliability" parameter
	if(file_exists($file)) {
		if(function_exists('finfo_open') && function_exists('finfo_file')) { 
			// fileinfo apparently works best, wish it was included with PHP by default
			$finfo_handle = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo_handle,$file);
			$is_reliable = true;
		} else if(function_exists('mime_content_type') && (mime_content_type($file) != '')) {
			//obsolete, but probably second best due to completeness
			$mimetype = mime_content_type($file);
			$is_reliable = true;
		} else {
			//included with plugin, uses the extention, limited and odd list, last option
			$mimetype_class = new mimetype();
			$mimetype = $mimetype_class->getType($file);
			$is_reliable = false;
		}
	} else {
		$mimetype = false;
		$is_reliable = false;
	}
	if($check_reliability == true) {
		return array('mime_type' =>$mimetype, 'is_reliable' => $is_reliable );
	} else {
		return $mimetype;
	}
}


function shopping_cart_total_weight() {
	global $wpdb;
	$cart = $_SESSION['nzshpcrt_cart'];
	$total_weight=0;
	foreach((array)$cart as $item) {
	  $weight = array();
		$variations = $item->product_variations;
		if(count($variations) > 0) {
			$variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variations)."')");
			asort($variation_ids);
			$all_variation_ids = implode(",", $variation_ids);
			$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '".(int)$item->product_id."' AND `value_id` IN ( '".implode("', '",$variations )."' ) AND `all_variation_ids` IN('{$all_variation_ids}') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variations)."' LIMIT 1");
			$weight = $wpdb->get_row("SELECT `weight`, `weight_unit` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
			
		}
		
		if(($weight == null) || ($weight['weight'] == null) && ($weight['weight_unit'] == null)) {
			$weight=$wpdb->get_row("SELECT `weight`, `weight_unit` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE id='{$item->product_id}'", ARRAY_A);
		}
		
		switch($weight['weight_unit']) {
			case "kilogram":
			$weight = $weight['weight'] / 0.45359237;
			break;
			
			case "gram":
			$weight = $weight['weight'] / 453.59237;
			break;
		
			case "once":
			case "ounce":
			$weight = $weight['weight'] / 16;
			break;
			
			default:
			$weight = $weight['weight'];
			break;
		}
		$subweight = $weight*$item->quantity;
		$total_weight+=$subweight;
	}
	return $total_weight;
}

function wpsc_convert_weights($weight, $unit) {
	if (is_array($weight)) {
		$weight = $weight['weight'];
	}
	switch($unit) {
		case "kilogram":
		$weight = $weight / 0.45359237;
		break;
		
		case "gram":
		$weight = $weight / 453.59237;
		break;
	
		case "once":
		case "ounce":
		$weight = $weight / 16;
		break;
		
		default:
		$weight = $weight;
		break;
	}
	return $weight;
}



function wpsc_ping() {
	$services = get_option('ping_sites');
	$services = explode("\n", $services);
	foreach ( (array) $services as $service ) {
		$service = trim($service);
		if($service != '' ) {
			wpsc_send_ping($service);
		}
	}
}

function wpsc_send_ping($server) {
	global $wp_version;
	include_once(ABSPATH . WPINC . '/class-IXR.php');

	// using a timeout of 3 seconds should be enough to cover slow servers
	$client = new IXR_Client($server, ((!strlen(trim($path)) || ('/' == $path)) ? false : $path));
	$client->timeout = 3;
	$client->useragent .= ' -- WordPress/'.$wp_version;

	// when set to true, this outputs debug messages by itself
	$client->debug = false;
	$home = trailingslashit( get_option('product_list_url') );
	$rss_url = get_option('siteurl')."/index.php?rss=true&amp;action=product_list";
	if ( !$client->query('weblogUpdates.extendedPing', get_option('blogname'), $home, $rss_url ) ) {
		$client->query('weblogUpdates.ping', get_option('blogname'), $home);
	}
}





function wpsc_add_product($product_values) {
    global $wpdb;
		// takes an array, inserts it into the database as a product
		$success = false;
		
		
		$insertsql = "INSERT INTO `".WPSC_TABLE_PRODUCT_LIST."` SET";
		$insertsql .= "`name` = '".$wpdb->escape($product_values['name'])."',";
		$insertsql .= "`description`  = '".$wpdb->escape($product_values['description'])."',";
		$insertsql .= "`additional_description`  = '".$wpdb->escape($product_values['additional_description'])."',";
				
		$insertsql .= "`price` = '".$wpdb->escape($product_values['price'])."',";
		
		$insertsql .= "`quantity_limited` = '".$wpdb->escape($product_values['quantity_limited'])."',";
		$insertsql .= "`quantity` = '".$wpdb->escape($product_values['quantity'])."',";
		
		$insertsql .= "`special` = '".$wpdb->escape($product_values['special'])."',";
		$insertsql .= "`special_price` = '".$wpdb->escape($product_values['special_price'])."',";
		
		$insertsql .= "`weight` = '".$wpdb->escape($product_values['weight'])."',";
		$insertsql .= "`weight_unit` = '".$wpdb->escape($product_values['weight_unit'])."',";
		
		$insertsql .= "`no_shipping` = '".$wpdb->escape($product_values['no_shipping'])."',";	
		$insertsql .= "`pnp` = '".$wpdb->escape($product_values['pnp'])."',";
		$insertsql .= "`international_pnp` = '".$wpdb->escape($product_values['international_pnp'])."',";
		
		$insertsql .= "`donation` = '".$wpdb->escape($product_values['donation'])."',";
		$insertsql .= "`display_frontpage` = '".$wpdb->escape($product_values['display_frontpage'])."',";
		$insertsql .= "`notax` = '".$wpdb->escape($product_values['notax'])."',";
		
		$insertsql .= "`image` = '0',";
		$insertsql .= "`file` = '0',";
		$insertsql .= "`thumbnail_state` = '0' ;";
		
		
		//Insert the data
		if($wpdb->query($insertsql)) {  
		  // if we succeeded, we have a product id, we wants it for the next stuff
			$product_id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_LIST."` LIMIT 1");
			
			// add the tags
			if(function_exists('wp_insert_term')) {
				product_tag_init();
				$tags = $product_values['product_tag'];
				if ($tags!="") {
					$tags = explode(',',$tags);
					foreach($tags as $tag) {
						$tt = wp_insert_term((string)$tag, 'product_tag');
					}
					$return = wp_set_object_terms($product_id, $tags, 'product_tag');
				}
			}
			
			$image = wpsc_item_process_image($product_id, $product_values['image_path'], basename($product_values['image_path']), $product_values['width'], $product_values['height'], $product_values['image_resize']);
						
			if(($image != null)) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$wpdb->escape($image)."' WHERE `id`='".$product_id."' LIMIT 1");
			}
		
			
			// add the product meta values
			if($product_values['productmeta_values'] != null) {
				foreach((array)$product_values['productmeta_values'] as $key => $value) {
					if(get_product_meta($product_id, $key) != false) {
						update_product_meta($product_id, $key, $value);
					} else {
						add_product_meta($product_id, $key, $value);
					}
				}
			}
			
			// and the custom meta values		
			if($product_values['new_custom_meta'] != null) {
				foreach((array)$product_values['new_custom_meta']['name'] as $key => $name) {
					$value = $product_values['new_custom_meta']['value'][(int)$key];
					if(($name != '') && ($value != '')) {
						add_product_meta($product_id, $name, $value, false, true);
					}
				}
			}
			
			// Add the tidy url name 
			$tidied_name = trim($product_values['name']);
			$tidied_name = strtolower($tidied_name);
			$url_name = sanitize_title($tidied_name);
			$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '".$wpdb->escape($url_name)."', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ('url_name') AND `meta_value` REGEXP '^(".$wpdb->escape($url_name)."){1}(\d)*$' ",ARRAY_A);
			$extension_number = '';
			if($similar_names['count'] > 0) {
				$extension_number = (int)$similar_names['max_number']+1;
			}
			$url_name .= $extension_number;
			add_product_meta($product_id, 'url_name', $url_name,true);
			
			// Add the varations and associated values
			$variations_procesor = new nzshpcrt_variations;
			if($product_values['variation_values'] != null) {
				$variations_procesor->add_to_existing_product($product_id,$product_values['variation_values']);
			}
				
			if($product_values['variation_priceandstock'] != null) {
				$variations_procesor->update_variation_values($product_id, $product_values['variation_priceandstock']);
			}
						
			// Add the selelcted categories
			$item_list = '';
			if(count($product_values['category']) > 0) {
				foreach($product_values['category'] as $category_id) {
				  $category_id = (int)$category_id;
					$check_existing = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` = ".$product_id." AND `category_id` = '$category_id' LIMIT 1");
					if($check_existing == null) {
						$wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ( `product_id` , `category_id` ) VALUES ( '".$product_id."', '".$category_id."');");        
					}
				}
			}
		$success = true;
		}
	return $success;
}

function wpsc_sanitise_keys($value) {
  /// Function used to cast array items to integer.
  return (int)$value;
}



/*
 * this function checks every product on the products page to see if it has any stock remaining
 * it is executed through the wpsc_product_alert filter
 */
function wpsc_check_stock($state, $product) {
	global $wpdb;
	// if quantity is enabled and is zero
	$out_of_stock = false;
	// only do anything if the quantity is limited.
	if($product['quantity_limited'] == 1) {
	  $excluded_values = '';
	  // get the variation IDs  associated with this product
		$variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product['id']}')");
		// if there are any, look through them for items out of stock
		if(count($variation_ids) > 0) { 
		  // sort and comma seperate them
			asort($variation_ids);
			$all_variation_ids = implode(",", $variation_ids);
			
			// get the visible variation values associated with this product
			$enabled_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$product['id']}') AND `visible` IN ('1')");
			
			// get the priceandstock IDs using the variation and variation value IDs
			$priceandstock_ids = $wpdb->get_col("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product['id']}'  AND `all_variation_ids` IN('$all_variation_ids') AND `value_id` IN (".implode(",", $enabled_values).")  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variation_ids)."'");
			
			// count the variation combinations with a stock of zero
			if(count($priceandstock_ids) > 0) {
				$items_out_of_stock = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` IN(".implode(",", $priceandstock_ids).") AND `stock` IN (0)");
			}
			if($items_out_of_stock > 0) {
				$out_of_stock = true;
			}
		} else if(($product['quantity'] == 0)) { // otherwise, use the stock from the products list table
		  $out_of_stock = true;
		}
	}
		if($out_of_stock === true) {
			$state['state'] = true;
			$state['messages'][] = __('This product has no available stock', 'wpsc');
		}
	
	return array('state' => $state['state'], 'messages' => $state['messages']);
}


/*
 * if UPS is on, this function checks every product on the products page to see if it has a weight
 * it is executed through the wpsc_product_alert filter
 */
function wpsc_check_weight($state, $product) {
	global $wpdb;
	$custom_shipping = (array)get_option('custom_shipping_options');
	$has_no_weight = false;
	// only do anything if UPS is on and shipping is used
	$weightRelatedShippingModules = array('ups','usps','weightrate');

	if((array_intersect($weightRelatedShippingModules, $custom_shipping))&& ($product['no_shipping'] != 1)) {
	//	exit('true?');
		$excluded_values = '';
		// get the variation IDs  associated with this product
		$variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product['id']}')");
		// if there are any, look through them for itemswith no weight
		if(count($variation_ids) > 0) { 
			// sort and comma seperate them
			asort($variation_ids);
			$all_variation_ids = implode(",", $variation_ids);
			
			// get the visible variation values associated with this product
			$enabled_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$product['id']}') AND `visible` IN ('1')");
			
			// get the priceandstock IDs using the variation and variation value IDs
			$priceandstock_ids = $wpdb->get_col("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product['id']}'  AND `all_variation_ids` IN('$all_variation_ids') AND `value_id` IN (".implode(",", $enabled_values).")  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variation_ids)."'");
			
			// count the variation combinations with a weight of zero
			if(count($priceandstock_ids) > 0) {
				$unweighted_items = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` IN(".implode(",", $priceandstock_ids).") AND `weight` IN (0)");
				if($unweighted_items > 0) {
					$has_no_weight = true;
				}
			}
		} else if(($product['weight'] == 0)) { // otherwise, use the stock from the products list table
			$has_no_weight = true;
			//echo "<pre>".print_r($product,true)."</pre>";
		}
		if($has_no_weight === true) {
			$state['state'] = true;
			$state['messages'][] = __('One or more of your shipping modules does not support products without a weight set. Please either disable shipping for this product or give it a weight', 'wpsc');
		}
	}
	return array('state' => $state['state'], 'messages' => $state['messages']);
}

add_filter('wpsc_product_alert', 'wpsc_check_stock', 10, 2);
add_filter('wpsc_product_alert', 'wpsc_check_weight', 10, 2);


?>