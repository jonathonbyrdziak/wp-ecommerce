<?php
/**
 * WP eCommerce category display functions
 *
 * These are functions for the wp-eCommerce categories
 * I would like to use an object and the theme engine for this, but it uses a recursive function, and I cannot think of a way to make that work with an object like the rest of the theme engine.
 *
 * @package wp-e-commerce
 * @since 3.7
*/


/// category template tags start here

/**
* wpsc starts category query function
* gets passed the query and makes it into a global variable, then starts capturing the html for the category loop
*/
function wpsc_start_category_query($arguments = array()) {
  global $wpdb, $wpsc_category_query;
  $wpsc_category_query = $arguments;
  ob_start();
}

/**
* wpsc print category name function
* places the shortcode for the category name
*/
function wpsc_print_category_name() {
	echo "[wpsc_category_name]";
}

/**
* wpsc print category description function
* places the shortcode for the category description, accepts parameters for the description container
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_category_description($start_element = '', $end_element = '') {
  global $wpsc_category_query;
  $wpsc_category_query['description_container'] = array('start_element' => $start_element, 'end_element' =>  $end_element);
	echo "[wpsc_category_description]";
}

/**
* wpsc print category url function
* places the shortcode for the category URL
*/
function wpsc_print_category_url() {
	echo "[wpsc_category_url]";
}

/**
* wpsc print category id function
* places the shortcode for the category URL
*/
function wpsc_print_category_id() {
	echo "[wpsc_category_id]";
}

/**
* wpsc print category classes function
* places classes for the category including selected state
*/
function wpsc_print_category_classes() {
	echo "[wpsc_category_classes]";
}

/**
* wpsc print product list function
* places the shortcode for the product list
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_product_list() {
  global $wpsc_category_query;
	if (get_option('catsprods_display_type') == 1) {
		echo "[wpsc_category_product_list]";
  }
}


/**
* wpsc print subcategory function
* places the shortcode for the subcategories, accepts parameters for the subcategories container, have this as <ul> and </ul> if using a list
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_subcategory($start_element = '', $end_element = '') {
  global $wpsc_category_query;
  $wpsc_category_query['subcategory_container'] = array('start_element' => $start_element, 'end_element' =>  $end_element);
  echo "[wpsc_subcategory]";
}


/**
* wpsc print category image function
* places the shortcode for the category image, accepts parameters for width and height
* @param integer width
* @param integer height
*/
function wpsc_print_category_image($width = null, $height = null) {
  global $wpsc_category_query;
  $wpsc_category_query['image_size'] = array('width' => $width, 'height' =>  $height);
	echo "[wpsc_category_image]";
}

/**
* wpsc print category products count function
* places the shortcode for the category product count, accepts parameters for the container element
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_category_products_count($start_element = '', $end_element = '') {
  global $wpsc_category_query;
  $wpsc_category_query['products_count'] = array('start_element' => $start_element, 'end_element' =>  $end_element);
	echo "[wpsc_category_products_count]";
}

/**
* wpsc end category query function
*/
function wpsc_end_category_query() {
	global $wpdb, $wpsc_category_query;
  $category_html = ob_get_clean();
  echo wpsc_display_category_loop($wpsc_category_query, $category_html);
  unset($GLOBALS['wpsc_category_query']);
}

/**
* wpsc category loop function
* This function recursively loops throuh the categories to display the category tree.
* WARNING: this function is recursive, be careful what you do with it.
* @param array the category query
* @param string the category html
* @return string - the finished category html
*/
function wpsc_display_category_loop($query, $category_html){
	global $wpdb, $wpsc_query;
	$category_sql_segment = array();
	$category_sql_segment[] = "`active`='1'";

	if(is_numeric($query['category_group']) ) {
	  $category_group = absint($query['category_group']);
      $category_sql_segment[] = "`group_id`='$category_group'";
	} elseif($query['category_group']=='all' ||$query['category_group']=='all+list') {
	  $category_group = 1;
	}

	
	/// select by parent category
	$category_sql_segment[] = "`category_parent` = '".absint($query['parent_category_id'])."'";

	if(!isset($query['order_by'])) {
		$query['order_by'] = array("column" => 'name', "direction" =>'asc');
	}
  $column = $wpdb->escape($query['order_by']['column']);

	if(strtolower($query['order_by']['direction']) == "desc") {
		$order = "DESC";
	} else {
		$order = "ASC";
	}
	$category_sql_segment = apply_filters('wpsc_display_category_loop_category_sql_segments', $category_sql_segment); 

  $category_data = $wpdb->get_results("SELECT  `id`, `name`, `nice-name`, `description`, `image` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE ".implode(" AND ", $category_sql_segment)." ORDER BY `{$column}` $order",ARRAY_A);

  $output ='';
  foreach((array)$category_data as $category_row) {
    $modified_query = $query;
    $modified_query['parent_category_id'] = $category_row['id'];
         
    $category_count = $wpdb->get_var("SELECT COUNT(`p`.`id`) FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `a` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `p` ON `a`.`product_id` = `p`.`id` WHERE `a`.`category_id` IN ('{$category_row['id']}') AND `p`.`active` IN ('1') AND `p`.`publish` IN('1')");

		$start_element = $query['products_count']['start_element'];
		$end_element = $query['products_count']['end_element'];
    $category_count_html =  $start_element.$category_count.$end_element;

    
    $category_description = '';
		if($category_row['description'] != '') {
      $start_element = $query['description_container']['start_element'];
      $end_element = $query['description_container']['end_element'];
			$category_description =  $start_element.wpautop(wptexturize( wp_kses(stripslashes($category_row['description']), $allowedtags ))).$end_element;
		}
	
	$category_classes = 'wpsc-cat-item wpsc-cat-item-' . $category_row['id'];
	
	if ( $wpsc_query->query_vars['category_id'] == $category_row['id']) {
		$category_classes .= ' wpsc-current-cat';
	}
	foreach ( $wpsc_query->breadcrumbs as $breadcrumb ) {
		if ( $breadcrumb['id'] == $category_row['id'] ) {
			$category_classes .= ' wpsc-cat-ancestor';
			break;
		}
	}
	
	
    $sub_categories = wpsc_display_category_loop($modified_query, $category_html);
    if($sub_categories != '') {
      $start_element = $query['subcategory_container']['start_element'];
      $end_element = $query['subcategory_container']['end_element'];
			$sub_categories = $start_element.$sub_categories.$end_element;
    }
    
    $category_image = wpsc_place_category_image($category_row['id'], $modified_query);
    
    $width = $query['image_size']['width'] - 4;
    $height = $query['image_size']['height'] - 4;
    
		$category_image_html = '';
    if(($query['show_thumbnails'] == 1)) {
      if(($category_row['image'] != '') && is_file(WPSC_CATEGORY_DIR.$category_row['image'])) {
				$category_image_html = "<img src='$category_image' alt='{$category_row['name']}' title='{$category_row['name']}' class='wpsc_category_image' />";
      } else {
				$category_image_html = "";
				$category_image_html .= "				<span class='wpsc_category_image item_no_image ' style='width: {$width}px; height: {$height}px;'>\n\r";
				$category_image_html .= "					<span class='link_substitute' >\n\r";
				$category_image_html .= "						<span>".__('N/A', 'wpsc')."</span>\n\r";
				$category_image_html .= "					</span>\n\r";
				$category_image_html .= "				</span>\n\r";
      }
      
    }

    $category_product_list = wpsc_category_product_list($category_row['id']);
    
    $tags_to_replace = array('[wpsc_category_name]',
		'[wpsc_category_description]',
		'[wpsc_category_url]',
		'[wpsc_category_id]',
		'[wpsc_category_classes]',
		'[wpsc_category_image]',
		'[wpsc_subcategory]',
		'[wpsc_category_products_count]',
		'[wpsc_category_product_list]');

    $content_to_place = array(
    htmlentities($category_row['name'],ENT_QUOTES, 'UTF-8'),
    $category_description,
    wpsc_category_url($category_row['id']),
    $category_row['id'],
    $category_classes,
    $category_image_html,
    $sub_categories,
    $category_count_html,
    $category_product_list);

    
		$output .= str_replace($tags_to_replace, $content_to_place ,$category_html);

		
	}
	return $output;
}

/**
* wpsc category image function
* if no parameters are passed, the category is not resized, otherwise it is resized to the specified dimensions
* @param integer category id
* @param array category query array
* @return string - the category image URL, or the URL of the resized version
*/
function wpsc_place_category_image($category_id, $query) {
	// show the full sized image for the product, if supplied with dimensions, will resize image to those.
		global $wpsc_query, $wpdb;
		$width = $query['image_size']['width'];
		$height = $query['image_size']['height'];
		//echo "<pre>".print_r($query, true)."</pre>";
		$image_url = "index.php?wpsc_request_image=true&category_id=".$category_id."&width=".$width."&height=".$height;
		return htmlspecialchars($image_url);
}


function wpsc_category_product_list($category_id) {
	global $wpdb;
	$output = '';
	$category_id = (int)$category_id;

	if (get_option('catsprods_display_type') == 1) {
		$product_data = $wpdb->get_results("SELECT `products`.`id`, `products`.`name`
			FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `cats`
			JOIN `".WPSC_TABLE_PRODUCT_LIST."` as `products`
			ON  `cats`.`product_id` = `products`.`id`
			WHERE `cats`.`category_id` = '$category_id'
			AND `products`.`publish`='1'
			AND `products`.`active` = '1'
			ORDER BY `products`.`name` ASC
			", ARRAY_A);
		if(count($product_data) > 0){
			$output .= "<ul class='category-product-list'>\n\r";
			foreach($product_data as $product_row) {
				$output .= "<li class='cat-item'><a class='productlink' href='".wpsc_product_url($product_row['id'],$category_id)."'>".$product_row['name']."</a></li>\n\r";
			} //end foreach
			$output .= "</ul>\n\r";
		} //end if productsIDs
	}
	return $output;
}


/// category template tags end here


// pe.{
// To stick this in sidebar, main page (calling products_page.php) must be called before sidebar.php in the loop (think)
  
function display_subcategories($id) {
  global $wpdb;  
  if(get_option('permalink_structure') != '') {
    $seperator ="?";
  } else {
    $seperator ="&amp;";
	}   
  $subcategory_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '".absint($id)."'  ORDER BY `nice-name`";
  $subcategories = $wpdb->get_results($subcategory_sql,ARRAY_A);
  if($subcategories != null) {
    $output .= "<ul class='SubCategories'>";
    foreach($subcategories as $subcategory) {
			if (get_option('show_category_count') == 1) {
				//show product count for each category
				$count = $wpdb->get_var("SELECT COUNT(`p`.`id`) FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `a` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `p` ON `a`.`product_id` = `p`.`id` WHERE `a`.`category_id` IN ('{$subcategory['id']}') AND `p`.`active` IN ('1') AND `p`.`publish` IN('1')");
				$addCount =  " (".$count.")";
			} //end get_option
      $output .= "<li class='cat-item'><a class='categorylink' href='".wpsc_category_url($subcategory['id'])."'>".stripslashes($subcategory['name'])."</a>$addCount".display_subcategories($subcategory['id'])."</li>";
		} 
    $output .= "</ul>";
	} else {
		return '';
	}
  return $output;
  }


 // Marked for removal
function show_cats_brands($category_group = null , $display_method = null, $order_by = 'name', $image = null) {
  global $wpdb; 
  
  if($category_group == null) {
		$category_group =  $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1') AND `default` IN ('1') LIMIT 1 ");
  } else {
    $category_group = (int)$category_group;
  }
  
  // Show cats & brands list if displaying on every page or if on a shop page (bit hacky but out of time).
  if (get_option('cat_brand_loc') != 3 && !function_exists("nzshpcrt_display_categories_groups") && ($display_method != 'sidebar')) {
    return;
  }
  
  if(get_option('permalink_structure') != '') {
    $seperator ="?";
	} else {
    $seperator ="&amp;";
	}

  $output = "<div class='PeSwitcher'>";

  switch(get_option('show_categorybrands')) {
    case 1:
      $output .= "<ul id='PeCatsBrandsBoth' class='category_brand_header'><li id='PeSwitcherFirst'><a href='' onclick='return prodgroupswitch(\"categories\");'>".__('Categories', 'wpsc')."</a> | <a href='' onclick='return prodgroupswitch(\"brands\");'>".__('Brands', 'wpsc')."</a></li></ul>";
      break;
  }
  $output .= "</div>";
  
  $output .= "<div class='PeCatsBrands'>";
  
  
  if((get_option('show_categorybrands') == 1 ) || (get_option('show_categorybrands') == 2)) {
  
  
    $output .= "<div class='PeCategories categorydisplay'>";
    $categories = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('$category_group') AND `active`='1' AND `category_parent` = '0' ORDER BY `".$wpdb->escape($order_by)."` ASC",ARRAY_A);
    if($categories != null) {
      $output .= "<ul class='PeCategories'>";
      foreach($categories as $option) {
        // Adrian - check option for category count
        if (get_option('show_category_count') == 1) {
          //show product count for each category
          $count = $wpdb->get_var("SELECT COUNT(`p`.`id`) FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `a` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `p` ON `a`.`product_id` = `p`.`id` WHERE `a`.`category_id` IN ('{$option['id']}') AND `p`.`active` IN ('1')");
          $addCount =  " (".$count.")";
        } //end get_option
        // No more mootools
        if (get_option('catsprods_display_type') == 1){
          $output .= "<li class='cat-item'><span class='category'><a class='productlink' href='".wpsc_category_url($option['id'])."'>".stripslashes($option['name'])."</a>".$addCount."</span>";
        }else{
        // Adrian - otherwise create normal category text with or without product count
					if (!$image) {
						$output .= "<li class='cat-item'><span class='category'><a class='productlink' href='".wpsc_category_url($option['id'])."'>".stripslashes($option['name'])."</a>".$addCount."</span>";
					} else {
						$output .= "<li class='cat-item'><img src='" . WPSC_CATEGORY_URL . $option['image']."'><br><span class='category'><a class='productlink' href='".wpsc_category_url($option['id'])."'>".stripslashes($option['name'])."</a>".$addCount."</span>";
					}
				}//end get_option
				
				
        $subcategory_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('$category_group') AND `active`='1' AND `category_parent` = '".$option['id']."' ORDER BY `id`";
        $subcategories = $wpdb->get_results($subcategory_sql,ARRAY_A);
        if($subcategories != null) {
					$output .= display_subcategories($option['id']);
        } else {
          // Adrian - check if the user wants categories only or sliding categories
          if (get_option('permalink_structure')!=''){
          	$uri = $_SERVER['REQUEST_URI'];
          	$category = explode('/',$uri);
          	$count = count($category);
          	$category_nice_name = $category[$count-2];
          	$category_nice_name2 = $wpdb->get_var("SELECT `nice-name` FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id='{$option['id']}'");
          	if ($category_nice_name == $category_nice_name2) {
          		$list_product=true;
          	} else {
          		$list_product=false;
          	}
          }
          if ((get_option('catsprods_display_type') == 1) && (($option['id'] == $_GET['category']) || $list_product) ){   
          // Adrian - display all products for that category          
            $product_sql = "SELECT product_id FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `category_id` = '".$option['id']."'";
            $productIDs = $wpdb->get_results($product_sql,ARRAY_A);
            if($productIDs != null){
              $output .= "<ul class='category-product-list'>";
              foreach($productIDs as $productID) {
                $ID = $productID['product_id'];
                $productName_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '".$ID."'";
                $productName = $wpdb->get_results($productName_sql,ARRAY_A);
                if ($productName[0]['active'])
                	$output .= "<li class='cat-item'><a class='productlink' href='".wpsc_product_url($ID,$option['id'])."'>".$productName[0]['name']."</a></li>";
              }//end foreach            
            $output .= "</ul>";         
            }//end if productsIDs
          }//end if get_option        
        }//end else
      $output .= "</li>";
      }
      $output .= "</ul>";
    }
    $output .= "</div>";
  }
  
  if((get_option('show_categorybrands') == 1 ) || (get_option('show_categorybrands') == 3))
  {
    if(get_option('show_categorybrands')  == 1) {
      $output .= "<ul class='PeBrands branddisplay' style='display: none;'>";
		} else {
      $output .= "<ul class='PeBrands branddisplay'>";
		}
    //$output ='';
    $brands = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `order` ASC",ARRAY_A);
    if($brands != null) {
      foreach($brands as $option) {
        $output .= "<li><a class='categorylink' href='".get_option('product_list_url').$seperator."brand=".$option['id']."'>".stripslashes($option['name'])."</a></li>";
      }
    }
    //$output .= $output;
    $output .= "</ul>";
  }
  
  $output .= "</div>";
  echo $output;
}

/**
* wpsc_category_url  function, makes permalink to the category or 
* @param integer category ID, can be 0
* @param boolean permalink compatibility, adds a prefix to prevent permalink namespace conflicts
*/
function wpsc_category_url($category_id, $permalink_compatibility = false) {
  global $wpdb, $wp_rewrite, $wpsc_category_url_cache;
  $home_page_id = get_option('page_on_front');
		
  if(((($wp_rewrite->rules != null) && ($wp_rewrite != null)) || (get_option('rewrite_rules') != null))) {
  	if(!isset($wpsc_category_url_cache[$category_id]) || ($wpsc_category_url_cache[$category_id] == '') ) {
			if($category_id > 0) {
				$category_data = $wpdb->get_row("SELECT `nice-name`,`category_parent` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('".(int)$category_id."') AND `active` IN('1') LIMIT 1", ARRAY_A);
				if($category_data['nice-name'] != '') {
					$category_name[] = $category_data['nice-name'];
				}
				
				
				if($category_data['category_parent'] > 0) {
					$num = 0;
					while($category_data['category_parent'] > 0) {
						$category_data = $wpdb->get_row("SELECT `nice-name`,`category_parent` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('".(int)$category_data['category_parent']."') AND `active` IN('1') LIMIT 1", ARRAY_A);
						$category_name[] = $category_data['nice-name'];			
						if($num > 10) { break; }
						$num++;
					}
				}
			} else {
				$products_page_details = $wpdb->get_row("SELECT `ID`, `post_name` FROM `".$wpdb->posts."` WHERE `post_content` LIKE '%[productspage]%' AND `post_type` NOT IN('revision') LIMIT 1", ARRAY_A);
				$products_page_name = '';
				if($home_page_id == $products_page_details['ID']) {
				$products_page_name = $products_page_details['post_name'];
						if($category_id < 1) {
						$category_name[] = $products_page_name;
					}
				}
			}
			
			$category_name_parts = array_reverse((array)$category_name);
			$category_names = implode($category_name_parts,"/");
			$wpsc_category_url_cache[$category_id] = $category_names;
			
		} else {
			$category_names = $wpsc_category_url_cache[$category_id];
		}
		
		if(!empty($category_names)) {
			if(substr(get_option('product_list_url'), -1, 1) == '/') {
				$category_url = get_option('product_list_url').$category_names."/";
			} else {
				$category_url = get_option('product_list_url')."/".$category_names."/";
			}
		} else {
			$category_url = get_option('product_list_url');
		}
		// if there is no trailing slash, add one
		if(substr($category_url, -1, 1) != '/') {
			$category_url .= "/";
		}
	} else {
	  if($category_id > 0) {
			$category_url = add_query_arg('category', $category_id, get_option('product_list_url'));
		} else {
			$category_url = get_option('product_list_url');
		}
	}
  return htmlentities($category_url, ENT_QUOTES);
}


function wpsc_is_in_category() {
  global $wpdb, $wp_query;
  $category_id = null;
  if($wp_query->query_vars['category_id'] > 0) {
    $category_id = absint($wp_query->query_vars['category_id']);
  } else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
    $category_id = absint($_GET['category']);
  }
  if($category_id > 0) {
    return true;
  }
  return false;
}

function wpsc_category_image($category_id = null) {
  global $wpdb, $wp_query;
  if($category_id < 1) {
		if($wp_query->query_vars['category_id'] > 0) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
			$category_id = $_GET['category'];
		}
  }
  $category_id = absint($category_id);
  $category_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('{$category_id}') AND `active` IN('1') LIMIT 1");
  $category_path = WPSC_CATEGORY_DIR.basename($category_image);
  $category_url = WPSC_CATEGORY_URL.basename($category_image);
  if(file_exists($category_path) && is_file($category_path)) {
    return $category_url;
  } else {
    return false;
  }
}

function wpsc_category_description($category_id = null) {
  global $wpdb, $wp_query;
  if($category_id < 1) {
		if($wp_query->query_vars['category_id'] > 0) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
			$category_id = $_GET['category'];
		}
  }
  $category_id = absint($category_id);
  $category_description = $wpdb->get_var("SELECT `description` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('{$category_id}') AND `active` IN('1') LIMIT 1");
  return $category_description;
}

function wpsc_category_name($category_id = null) {
  global $wpdb, $wp_query;
  if($category_id < 1) {
		if($wp_query->query_vars['category_id'] > 0) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
			$category_id = $_GET['category'];
		}
  }
  $category_id = absint($category_id);
  $category_name = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('{$category_id}') AND `active` IN('1') LIMIT 1");
  return $category_name;
}



/**
 * WPSC Category Group
 * @modified:     2009-09-28 by Ben
 * @description:  Get a category's group id.
 * @param:        $category_id = Category ID
 * @return:       (Int) Group ID
 */

function wpsc_category_group( $category_id = null ) {
	global $wpdb, $wp_query;
	if ( $category_id < 1 ) {
		if ( $wp_query->query_vars['category_id'] > 0 ) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if ( isset($_GET['category']) && ($_GET['category'] > 0) ) {
			$category_id = $_GET['category'];
		}
	}
	$category_id = absint($category_id);
	// Is it better for this DB query be loaded in the main query or cached?
	$group_id = $wpdb->get_var("SELECT `group_id` FROM `" . WPSC_TABLE_PRODUCT_CATEGORIES . "` WHERE `id` IN ('{$category_id}') AND `active` IN('1') LIMIT 1");
	return absint($group_id);
}



// This function displays the category groups, it is used by the above function
function nzshpcrt_display_categories_groups() {
    global $wpdb;
    if(get_option('permalink_structure') != '') {
      $seperator ="?";
    } else {
      $seperator ="&amp;";
    }

    ob_start();
		$category_settings = array('category_group'=> 1, 'show_thumbnails'=> get_option('show_category_thumbnails'));
		include(wpsc_get_theme_file_path("category_widget.php"));
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

/** wpsc list subcategories function
		used to get an array of all the subcategories of a category.

*/
function wpsc_list_subcategories($category_id = null) {
  global $wpdb,$category_data;
  if(is_numeric($category_id)) {
    $category_list = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `category_parent` = '".$category_id."'");
	}
  if($category_list != null) {
    foreach($category_list as $subcategory_id) {
			$category_list = array_merge((array)$category_list, (array)wpsc_list_subcategories($subcategory_id));
		}
	}
	return $category_list;
}


  
?>