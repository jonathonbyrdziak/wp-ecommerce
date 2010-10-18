<?php
//global $wpsc_category_url_cache;
function wpsc_category_tm(){
 global $wpdb;
 /* START OF TARGET MARKET SELECTION */					
	$countrylist = $wpdb->get_results("SELECT id,country,visible FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY country ASC ",ARRAY_A);
	?>
		<tr>
			<td colspan="2"><h4>Target Market Restrictions</h4></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>
				<?php echo __('Target Markets', 'wpsc'); ?>:
				<br />
			</td>
			<td>
			<?php
			if(@extension_loaded('suhosin') && (@ini_get('suhosin.post.max_vars') > 0) && (@ini_get('suhosin.post.max_vars') < 500)) {
				echo "<em>".__("The Target Markets feature has been disabled because you have the Suhosin PHP extension installed on this server. If you need to use the Target Markets feature then disable the suhosin extension, if you can not do this, you will need to contact your hosting provider.
			",'wpsc')."</em>";
			  
			} else {
				?>
				<span>Select: <a href='<?php echo add_query_arg(array('selected_all' => 'all'))?>' class='wpsc_select_all'>All</a>&nbsp; <a href='<?php echo add_query_arg(array('selected_all' => 'none'))?>' class='wpsc_select_none'>None</a></span><br />
				<div id='resizeable' class='ui-widget-content multiple-select'>

					<?php
					foreach((array)$countrylist as $country){
						if($country['visible'] == 1){
							echo "<input type='checkbox' name='countrylist2[]' value='".$country['id']."'  checked='".$country['visible']."' />".$country['country']."<br />\n\r";
						}else{
							echo "<input type='checkbox' name='countrylist2[]' value='".$country['id']."'  />".$country['country']."<br />\n\r";
						}	
					}
					?>
				</div>
				<br />				<br />
				<span class='wpscsmall description'><?php _e('Select the markets you are selling this category to.'); ?></span>

			<?php
				}
			?>
			</td>
		</tr>
	<?php
}

function admin_categorylist($curent_category) {
  global $wpdb;
  $options = "";
  //$options .= "<option value=''>".__('Select a Product Group', 'wpsc')."</option>\r\n";
  $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` ORDER BY `id` ASC",ARRAY_A);
  foreach($values as $option) {
    if($curent_category == $option['id']) {
      $selected = "selected='selected'";
		}
    $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
    $selected = "";
	}
  $concat .= "<select name='category'>".$options."</select>\r\n";
  return $concat;
}

function display_categories($group_id, $id = null, $level = 0) {
  global $wpdb,$category_data;
  if(is_numeric($id)) {
    $category_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('$group_id') AND `active`='1' AND `category_parent` = '".$id."' ORDER BY `id`";
    $category_list = $wpdb->get_results($category_sql,ARRAY_A);
	} else {
		$category_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('$group_id') AND `active`='1' AND `category_parent` = '0' ORDER BY `id`";
		$category_list = $wpdb->get_results($category_sql,ARRAY_A);
	}
  if($category_list != null) {
    foreach($category_list as $category) {
      display_category_row($category, $level);
      display_categories($group_id, $category['id'], ($level+1));
		}
	}
}

function display_category_row($category,$subcategory_level = 0) {
  echo "     <tr>\n\r";
  echo "       <td colspan='4' class='colspan'>\n\r";
  if($subcategory_level > 0) {
    echo "        <div class='subcategory' style='padding-left: ".(1*$subcategory_level)."em;'>\n\r";
    echo "		        <img class='category_indenter' src='".WPSC_URL."/images/indenter.gif' alt='' title='' />\n\r";
	}
  echo "		        <table class='itemlist'>\n\r";
  echo "		          <tr>\n\r";
  echo "		            <td>\n\r";
  if($category['image'] !=null) {
		echo "		            <img src='".WPSC_CATEGORY_URL.$category['image']."' title='".$category['name']."' alt='".$category['name']."' width='30' height='30' />\n\r";
	} else {
		echo "		            <img style='border-style:solid; border-color: red' src='".WPSC_URL."/images/no-image-uploaded.gif' title='".$category['name']."' alt='".$category['name']."' width='30' height='30'  />\n\r";
	}
  echo "		            </td>\n\r";
  
  echo "		            <td>\n\r";
  echo "".htmlentities(stripslashes($category['name']), ENT_QUOTES, 'UTF-8')."";
  echo "		            </td>\n\r";

  echo "		            <td>\n\r";
  echo "		            		<a href='#' onclick='fillcategoryform(".$category['id'].");return false;'>".__('Edit', 'wpsc')."</a>\n\r";
  echo "		            </td>\n\r";
  echo "		          </tr>\n\r";
  echo "		        </table>\n\r";
  
  if($subcategory_level > 0) {
    echo "		        </div>\n\r";
	}
  echo "       </td>\n\r";
  echo "      </tr>\n\r";
}




function wpsc_display_groups_page() {
  global $wpdb, $wp_rewrite;
	if(!is_numeric($_GET['category_group']) || ((int)$_GET['category_group'] == null)) {
		$current_categorisation =  $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1') AND `default` IN ('1') LIMIT 1 ",ARRAY_A);
	} else {
		$current_categorisation =  $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1') AND `id` IN ('".(int)$_GET['category_group']."') LIMIT 1 ",ARRAY_A);
	}
  if($_POST['submit_action'] == "add") {
    //exit("<pre>".print_r($_POST,true)."</pre>"); 
    if(($_FILES['image'] != null) && preg_match("/\.(gif|jp(e)*g|png){1}$/i",$_FILES['image']['name'])) {
      if(function_exists("getimagesize")) {
				if(((int)$_POST['width'] > 10 && (int)$_POST['width'] < 512) && ((int)$_POST['height'] > 10 && (int)$_POST['height'] < 512) ) {
					$width = (int)$_POST['width'];
					$height = (int)$_POST['height'];
					image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']), $width, $height);
				} else {
					image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
				}  
				$image = $wpdb->escape($_FILES['image']['name']);
			} else {
				$new_image_path = (WPSC_CATEGORY_DIR.basename($_FILES['image']['name']));
				move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path);
				$stat = stat( dirname( $new_image_path ));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $new_image_path, $perms );	
				$image = $wpdb->escape($_FILES['image']['name']);
			}
		} else {
			$image = '';
		}
    
    if(is_numeric($_POST['category_parent'])) {
      $parent_category = (int)$_POST['category_parent'];
		} else {
      $parent_category = 0;
		}
      
   
    //$tidied_name = sanitize_title();
		//$tidied_name = strtolower($tidied_name);
		$url_name = sanitize_title($_POST['name']);
    $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
    $extension_number = '';
    if($similar_names['count'] > 0) {
      $extension_number = (int)$similar_names['max_number']+1;
		}
    $url_name .= $extension_number;
    
    
    
		switch($_POST['display_type']) {
			case "grid":
				$display_type = 'grid';
			break;
			
			case "list":
				$display_type = 'list';
			break;
			
			case "default":
				$display_type = 'default';
			break;
			
			default:
				$display_type = '';
			break;
		}
      
      
    if($_POST['product_height'] > 0) {
      $product_height = (int)$_POST['product_height'];
    } else {
      $product_height = '';
    }
    
    
    if($_POST['product_width'] > 0) {
      $product_width = (int)$_POST['product_width'];
    } else {
      $product_width = '';
    }
    
    if(trim($_POST['name']) != null) {
			//$_POST['name'] = "test";
      $insertsql = "INSERT INTO `".WPSC_TABLE_PRODUCT_CATEGORIES."` (`group_id`, `name` , `nice-name` , `description`, `image`, `fee` , `active`, `category_parent`, `order` ) VALUES ( '".(int)$_POST['categorisation_group']."', '".$wpdb->escape(stripslashes($_POST['name']))."', '".$url_name."', '".$wpdb->escape(stripslashes($_POST['description']))."', '$image', '0', '1' ,'$parent_category', '0')";
      
      $wp_rewrite->flush_rules(); 
      if($wpdb->query($insertsql)) {
				$category_id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` LIMIT 1");

			if($_POST['use_additonal_form_set'] != '') {
				wpsc_update_categorymeta($category_id, 'use_additonal_form_set', $_POST['use_additonal_form_set']);
			} else {
				wpsc_delete_categorymeta($category_id, 'use_additonal_form_set');
			}


			if((bool)(int)$_POST['uses_billing_address'] == true) {
				wpsc_update_categorymeta($category_id, 'uses_billing_address', 1);
				$uses_additional_forms = true;
			} else {
				wpsc_update_categorymeta($category_id, 'uses_billing_address', 0);
				$uses_additional_forms = false;
			}

			
// 			if($uses_additional_forms == true) {
// 				$checkout_form_sets = get_option('wpsc_checkout_form_sets');
// 				$checkout_form_sets[$url_name] = $wpdb->escape(stripslashes($_POST['name']));
// 				update_option('wpsc_checkout_form_sets', $checkout_form_sets);
// 			}



				
        echo "<div class='updated'><p align='center'>".__('The item has been added', 'wpsc')."</p></div>";
      } else {
        echo "<div class='updated'><p align='center'>".__('The item has not been added', 'wpsc')."</p></div>";
      }
      
			update_option('wpsc_category_url_cache', array());
      $wp_rewrite->flush_rules();
    } else {
      echo "<div class='updated'><p align='center'>".__('The item has not been added', 'wpsc')."</p></div>";
    }
    
   // Jeff 15-04-09 Used for category target market options
	   
    if(($_POST['countrylist2'] != null ) && ($category_id > 0)){
    	$AllSelected = false;
    	
			$countryList = $wpdb->get_col("SELECT `id` FROM  `".WPSC_TABLE_CURRENCY_LIST."`");
    	
    	if(in_array('all',$_POST['countrylist2'])) {
    		foreach($countryList as $country){
					$wpdb->query("INSERT INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('1','{$country}', '{$category_id}' )");
					//echo "REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('1','{$country}', '{$category_id}' )<br />";
    		}
				$AllSelected = true;
    	}
    	
    	
    	if(in_array('none', $_POST['countrylist2'])){
    		foreach($countryList as $country){
					$wpdb->query("REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('0','{$country}', '{$category_id}' )");
    		}
				$AllSelected = true;
    	}
    			
    			
			if($AllSelected != true){
				$unselectedCountries = array_diff($countryList, $_POST['countrylist2']);
				foreach($unselectedCountries as $unselected){
					$wpdb->query("REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('0','{$unselected}', '{$category_id}' )");
					//echo "REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('0','{$unselected}', '{$category_id}' )<br />";
				} 
		
				//find the countries that are selected
				$selectedCountries = array_intersect($countryList, $_POST['countrylist2']);
				foreach($selectedCountries as $selected){
					$wpdb->query("REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('1','{$selected}', '{$category_id}' )");
					//echo "REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('1','{$unselected}', '{$category_id}' )<br />";
				}
			}
		}
	}

  if(($_POST['submit_action'] == "edit") && is_numeric($_POST['prodid'])) {
    $category_id = absint($_POST['prodid']);
    if(($_FILES['image'] != null) && preg_match("/\.(gif|jp(e)*g|png){1}$/i",$_FILES['image']['name'])) {
      if(function_exists("getimagesize")) {
      		if(((int)$_POST['width'] >= 10 && (int)$_POST['width'] <= 512) && ((int)$_POST['height'] >= 10 && (int)$_POST['height'] <= 512) ) {
      		  $width = (int)$_POST['width'];
      		  $height = (int)$_POST['height'];
						image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']), $width, $height);
    		  } else {
						image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
    		  }  
					$image = $wpdb->escape($_FILES['image']['name']);
        } else {
					move_uploaded_file($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
					$image = $wpdb->escape($_FILES['image']['name']);
        }
      
      } else {
				$image = '';
      }
    
    if(is_numeric($_POST['height']) && is_numeric($_POST['width']) && ($image == null)) {
      $imagedata = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`=".(int)$_POST['prodid']." LIMIT 1");
      if($imagedata != null) {
        $height = $_POST['height'];
        $width = $_POST['width'];
        $imagepath = WPSC_CATEGORY_DIR . $imagedata;
        $image_output = WPSC_CATEGORY_DIR . $imagedata;
        image_processing($imagepath, $image_output, $width, $height);
			}
		}
   
    $category_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('".(int)$_POST['prodid']."')", ARRAY_A);
    
    if(($_POST['title'] != $category_data['name']) && (trim($_POST['title']) != null)) {
      $category_name = $_POST['title'];
      $category_sql_list[] = "`name` = '$category_name' ";
      
      /* creates and checks the tidy URL name */     
//       $tidied_name = trim($category_name);
//       $tidied_name = strtolower($tidied_name);
      $url_name = sanitize_title($category_name);
      if($url_name != $category_data['nice-name']) {
        $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` REGEXP '^($url_name){1}(0-9)*$' AND `id` NOT IN ('".(int)$category_data['id']."') ",ARRAY_A);
        //exit("<pre>".print_r($similar_names,true)."</pre>");
        $extension_number = '';
        if($similar_names['count'] > 0) {
          $extension_number = (int)$similar_names['max_number']+1;
				}
        $url_name .= $extension_number;   
			}
      /* checks again, just in case */
      if($url_name != $category_data['nice-name']) {
        $category_sql_list[] = "`nice-name` = '$url_name' ";
			}
			
			update_option('wpsc_category_url_cache', array());
      $wp_rewrite->flush_rules(); 
		}   
	   // Jeff 15-04-09 Used for category target market options
	   
    if($_POST['countrylist2'] != null){
			$countryList = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_CURRENCY_LIST."`");
    	$AllSelected = false;
    	if(in_array('all',$_POST['countrylist2'])){
    		foreach($countryList as $country){
					$wpdb->query("REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('1','{$country}', '{$category_id}' )");
    		}
				$AllSelected = true;
    	}
    	if(in_array('none', $_POST['countrylist2'])){
				$wpdb->query("UPDATE `".WPSC_TABLE_CATEGORY_TM."` SET `visible` = '0' WHERE `categoryid`='{$category_id}'");
			$AllSelected = true;
    	}
			if($AllSelected != true){
				$unselectedCountries = array_diff($countryList, $_POST['countrylist2']);
				foreach($unselectedCountries as $unselected){
    				$wpdb->query("REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."` (`visible`, `countryid`, `categoryid`) VALUES (0,'{$unselected}', '{$category_id}' )");
				} 
		
				//find the countries that are selected
				$selectedCountries = array_intersect($countryList, $_POST['countrylist2']);
				foreach($selectedCountries as $selected){
					$wpdb->query("REPLACE INTO `".WPSC_TABLE_CATEGORY_TM."`(`visible`, `countryid`, `categoryid`) VALUES ('1','{$selected}', '{$category_id}' )");
				}
			}
		}
    
    if($_POST['description'] != $category_data['description']) {
      $description = $_POST['description'];
      $category_sql_list[] = "`description` = '$description' ";
		}
      
    if(is_numeric($_POST['category_parent']) and ($_POST['category_parent'] != $category_data['category_parent'])) {
      $parent_category = (int)$_POST['category_parent'];
      $category_sql_list[] = "`category_parent` = '$parent_category' ";
		}      
    
    if($_POST['deleteimage'] == 1) {
      $category_sql_list[] = "`image` = ''";
		} else {
      if($image != null) {
        $category_sql_list[] = "`image` = '$image'";
			}
		}

		if($_POST['display_type'] != $category_data['display_type']) {
			switch($_POST['display_type']) {
				case "grid":
					$display_type = 'grid';
				break;
				
				case "list":
					$display_type = 'list';
				break;
				
				case "default":
					$display_type = 'default';
				break;
				
				default:
					$display_type = '';
				break;
			}
      $category_sql_list[] = "`display_type` = '$display_type' ";
		}

		

		  //echo "<pre>".print_r($category_sql_list,true)."</pre>";

    if($_POST['product_height'] > 0) {
      $product_height = (int)$_POST['product_height'];
    } else {
      $product_height = '';
    }
		$category_sql_list[] = "`image_height` = '$product_height' ";
    
    if($_POST['product_width'] > 0) {
      $product_width = (int)$_POST['product_width'];
    } else {
      $product_width = '';
    }
		$category_sql_list[] = "`image_width` = '$product_width' ";

    if(count($category_sql_list) > 0) {
      $category_sql = implode(", ",$category_sql_list);
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET $category_sql WHERE `id`='".(int)$_POST['prodid']."' LIMIT 1");
      $category_id = absint($_POST['prodid']);
      
			update_option('wpsc_category_url_cache', array());

			if($_POST['use_additonal_form_set'] != '') {
				wpsc_update_categorymeta($category_id, 'use_additonal_form_set', $_POST['use_additonal_form_set']);
			} else {
				wpsc_delete_categorymeta($category_id, 'use_additonal_form_set');
			}
			

			
			if((bool)(int)$_POST['uses_billing_address'] == true) {
				wpsc_update_categorymeta($category_id, 'uses_billing_address', 1);
				$uses_additional_forms = true;
			} else {
				wpsc_update_categorymeta($category_id, 'uses_billing_address', 0);
				$uses_additional_forms = false;
			}
			
// 			if($uses_additional_forms == true) {
// 				$category_name = $wpdb->escape(stripslashes($_POST['title']));
// 				$url_name = sanitize_title($category_name);
// 				$checkout_form_sets = get_option('wpsc_checkout_form_sets');
// 				$checkout_form_sets[$url_name] = $category_name;
// 				//print_r($checkout_form_sets);
// 				//exit();
// 				update_option('wpsc_checkout_form_sets', $checkout_form_sets);
// 			}

			
      $wp_rewrite->flush_rules(); 
		}
    echo "<div class='updated'><p align='center'>".__('The product group has been edited.', 'wpsc')."</p></div>";
	}
  
if($_POST['submit_action'] == "add_categorisation") {  
  $wpdb->query("INSERT INTO `".WPSC_TABLE_CATEGORISATION_GROUPS."` ( `name`, `description`, `active`, `default`) VALUES ( '".$wpdb->escape(stripslashes($_POST['name']))."', '".$wpdb->escape(stripslashes($_POST['description']))."', '1', '0')");
	echo "<div class='updated'><p align='center'>".__('The group has been added.', 'wpsc')."</p></div>";  

}


  
if($_POST['submit_action'] == "edit_categorisation") {  
  $edit_group_id = $_POST['group_id'];
  
  $wpdb->query("UPDATE `".WPSC_TABLE_CATEGORISATION_GROUPS."` SET `name` = '".$wpdb->escape(stripslashes($_POST['name']))."', `description` = '".$wpdb->escape(stripslashes($_POST['description']))."' WHERE `id` IN('$edit_group_id') LIMIT 1 ");
	
	
	echo "<div class='updated'><p align='center'>".__('The group has been edited.', 'wpsc')."</p></div>";  
	
		
	if(!is_numeric($_GET['category_group']) || ((int)$_GET['category_group'] == null)) {
		$current_categorisation =  $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1') AND `default` IN ('1') LIMIT 1 ",ARRAY_A);
	} else {
		$current_categorisation =  $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1') AND `id` IN ('".(int)$_GET['category_group']."') LIMIT 1 ",ARRAY_A);
	}
}


if(is_numeric($_GET['category_delete_id'])) {
  $delete_id = (int)$_GET['category_delete_id'];
  $deletesql = "UPDATE `".WPSC_TABLE_CATEGORISATION_GROUPS."` SET `active` = '0' WHERE `id`='{$delete_id}' AND `default` IN ('0') LIMIT 1";
  $wpdb->query($deletesql);
  $delete_subcat_sql = "UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `active` = '0', `nice-name` = '' WHERE `group_id`='{$delete_id}'";
  $wpdb->query($delete_subcat_sql);

  
	update_option('wpsc_category_url_cache', array());
	$wp_rewrite->flush_rules(); 
}


if(is_numeric($_GET['deleteid'])) {
  $delete_id = absint($_GET['deleteid']);
  $deletesql = "UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `active` = '0', `nice-name` = '' WHERE `id`='{$delete_id}' LIMIT 1";
  if($wpdb->query($deletesql)) {
		$delete_subcat_sql = "UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `active` = '0', `nice-name` = '' WHERE `category_parent`='{$delete_id}'";
		$wpdb->query($delete_subcat_sql);
		// if this is the default category, we need to find a new default category
		if($delete_id == get_option('wpsc_default_category')) {
			// select the category that is not deleted with the greatest number of products in it
			$new_default = $wpdb->get_var("SELECT `cat`.`id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` AS `cat`
				LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `assoc` ON `cat`.`id` = `assoc`.`category_id`
				WHERE `cat`.`active` IN ( '1' )
				GROUP BY `cat`.`id`
				ORDER BY COUNT( `assoc`.`id` ) DESC
				LIMIT 1");
			if($new_default > 0) {
				update_option('wpsc_default_category', $new_default);
			}
		}
		
		update_option('wpsc_category_url_cache', array());
		$wp_rewrite->flush_rules(); 
	}
}

unset($GLOBALS['wpsc_category_url_cache']);
update_option('wpsc_category_url_cache', array());
	?>
	
	
	<script language='javascript' type='text/javascript'>
	function conf() {
		var check = confirm("<?php echo __('Are you sure you want to delete this category? If the category has any subcategories, they will be deleted too.', 'wpsc');?>");
		if(check) {
			return true;
		} else {
			return false;
		}
	}
	function categorisation_conf() {
		var check = confirm("<?php echo __('Are you sure you want to delete this product group? All categories it contains will be deleted too.', 'wpsc');?>");
		if(check) {
			return true;
		} else {
			return false;
		}
	}
	
	<?php
		if(is_numeric($_POST['prodid'])) {
			echo "fillcategoryform(".$_POST['prodid'].");";
		}
	?>
	</script>
	<div class="wrap">
		<h2><?php echo __('Categories', 'wpsc');?></h2>
			<?php
		
		
		if(function_exists('add_object_page')) {
			echo "<div id='dashboard-widgets' class='metabox-holder'>";
		}
	?>
		<span><?php echo __('Categorizing your products into groups help your customers find them. For instance if you sell hats and trousers you  might want to setup a Group called clothes and add hats and trousers to that group.', 'wpsc');?></span>
	<?php
		if (function_exists('add_object_page')) {
			echo "<div class='wpsc_products_nav27'>";
		} else {
			echo "<div class='tablenav wpsc_groups_nav' >";
		}
	?>
	
		<div class="alignleft product_group" style='width: 500px;'>
			<form action='' method='GET' id='submit_categorisation_form' >
			<input type='hidden' value='<?php echo $_GET['page']; ?>' name='page'  />
			<?php
			$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')", ARRAY_A);
			//echo "<ul class='categorisation_links'>\n\r";
			echo "<label for='select_categorisation_group' class='select_categorisation_group'>".__('Select a Group to Manage', 'wpsc').":&nbsp;&nbsp;</label>";
			echo "<select name='category_group' id='select_categorisation_group' onchange='submit_status_form(\"submit_categorisation_form\")'>"; 
			foreach((array)$categorisation_groups as $categorisation_group) {
				$selected = '';
				if($current_categorisation['id'] == $categorisation_group['id']) {
					//$selected = "class='selected'";
					$selected = "selected='selected'";
				}
				echo "<option value='{$categorisation_group['id']}' $selected >{$categorisation_group['name']}</option>";
				//echo "  <li $selected >\n\r";
				//echo "    <a href='?page={$_GET['page']}&amp;category_group={$categorisation_group['id']}'>{$categorisation_group['name']}</a> ";
				//echo "  </li>\n\r";
			}
			echo "</select>"; 
			//echo "<li>- <a href='' onclick='return showadd_categorisation_form()'><span>".__('Add New Group', 'wpsc')."</span></a></li>";
			//echo "</ul>\n\r";
			?>
			
			 <span><?php _e('or');?></span>
			 
			<?php echo "<a class='button add_categorisation_group' href='#' onclick='return showadd_categorisation_form()'><span>".__('Add New Group', 'wpsc')."</span></a>"; ?>
			</form>
		</div>
		
			
		<!--
	<div class="alignright">
			<a target="_blank" href='http://www.instinct.co.nz/e-commerce/product-groups/' class='about_this_page'><span><?php //echo __('About This Page', 'wpsc');?></span>&nbsp;</a>
		</div>
	-->
		<br class="clear"/>
	</div>
	
	
	
	
	<div id='add_categorisation'>
		<strong><?php echo __('Add New Group', 'wpsc');?></strong>
		<form method='POST' enctype='multipart/form-data'>
		
			<fieldset>
			<label for='add_categorisation_name'>Name</label>
			<input type='text' name='name' value='' id='add_categorisation_name' />
			</fieldset>
			
			<fieldset>
			<label for='add_categorisation_description'>Description</label>
			<input type='text' name='description' value='' id='add_categorisation_description' />
			</fieldset>
			
			<fieldset>
			<label>&nbsp;</label>
			
			<input type='hidden' name='submit_action' value='add_categorisation' />
			<input type='submit' name='submit_form' value='<?php echo __('Submit', 'wpsc'); ?>' />
			</fieldset>
		</form>
		<br/>
	</div>
	
	<div id='edit_categorisation'>
		<strong><?php echo __('Edit Group', 'wpsc');?></strong>
		
		<form method='POST' enctype='multipart/form-data'>
		
			<fieldset>
				<label for='add_categorisation_name'>Name</label>
				<input type='text' name='name' value='<?php echo $current_categorisation['name']; ?>' id='add_categorisation_name' />
			</fieldset>
			
			<fieldset>
				<label for='add_categorisation_description'>Description</label>
				<input type='text' name='description' value='<?php echo $current_categorisation['description']; ?>' id='add_categorisation_description' />
			</fieldset>
			
			<fieldset>
				<label>&nbsp;</label>		
				<input type='hidden' name='group_id' value='<?php echo $current_categorisation['id']; ?>' />
				<input type='hidden' name='submit_action' value='edit_categorisation' />
				<input type='submit' name='submit_form' value='<?php echo __('Submit', 'wpsc'); ?>' />
				<?php if($current_categorisation['default'] != 1) { ?>
				<a href='<?php echo "?page={$_GET['page']}&amp;category_delete_id={$current_categorisation['id']}"  ?>' onclick='return categorisation_conf()' > <?php echo __('Delete', 'wpsc'); ?></a>
				<?php 	} ?>
			</fieldset>
		</form>
		<br/>
	</div>
	
	<?php
	
	$num = 0;
	
	echo "  <table id='productpage' style='margin-top: 1ex;'>\n\r";
	echo "    <tr><td class='firstcol' style='width: 297px;'>\n\r";
	if (function_exists('add_object_page')){
		echo "<div class='postbox' style='margin-right: 15px; min-width:255px;'>";
		echo "<h3 class='hndle'>".str_replace("[categorisation]", $current_categorisation['name'], __('&quot;[categorisation]&quot; Group', 'wpsc'))."</h3>";
		echo "<div class='inside'>";
	}
	//echo "<div class='categorisation_title'><a href='' onclick='return showaddform()' class='add_category_link'><span>". __('+ Add new category to the &quot;[categorisation]&quot; Group', 'wpsc')."</span></a><strong class='form_group'>".str_replace("[categorisation]", $current_categorisation['name'], __('Manage &quot;[categorisation]&quot;', 'wpsc'))." <a href='#' onclick='return showedit_categorisation_form()'>[".__('Edit', 'wpsc')."]</a> </strong></div>";
	echo "      <table id='itemlist'>\n\r";
	if (function_exists('add_object_page')) {
		echo "<tr></tr>";
	} else {
		echo "        <tr class='firstrow categorisation_title'>\n\r";
		echo "          <td>\n\r";
		echo __('Image', 'wpsc');
		echo "          </td>\n\r";
		
		echo "          <td>\n\r";
		echo __('Name', 'wpsc');
		echo "          </td>\n\r";
		
		echo "          <td>\n\r";
		//echo __('Description', 'wpsc');
		echo "          </td>\n\r";
		
		echo "          <td>\n\r";
		echo __('Edit', 'wpsc');
		echo "          </td>\n\r";
		
		echo "        </tr>\n\r";
	}
	
	
	
	echo "     <tr>\n\r";
	echo "       <td colspan='4' class='colspan'>\n\r";
	echo "<div class='editing_this_group'><p>";
	echo str_replace("[categorisation]", $current_categorisation['name'], __('You are editing the &quot;[categorisation]&quot; Group', 'wpsc'));
	
	echo "  <a href='#' onclick='return showedit_categorisation_form()'>".__('Edit', 'wpsc')."</a>";
	
	echo "</p></div>";
	echo "       </td>\n\r";
	echo "     <tr>\n\r";
	
	display_categories($current_categorisation['id']);
	if (function_exists('add_object_page')){
		echo "</table>";
		echo "</div>"; //class inside ends
		echo "</div>"; //class postbox ends
	} else {
		echo "</table>\n\r";
	}
	echo "      </td><td id='poststuff' class='secondcol product_groups_page'>\n\r";
	echo "        <div id='productform' class='postbox'>";
	echo "<form method='POST'  enctype='multipart/form-data' name='editproduct$num'>\n\r";
	
	if (function_exists('add_object_page')) {
		echo "<h3 class='hndle'>".str_replace("[categorisation]", $current_categorisation['name'], __('You are editing an item in the &quot;[categorisation]&quot; Group', 'wpsc'))."</h3>";
		echo "<div class='inside'>";
	} else {
		echo "<div class='categorisation_title'><strong class='form_group'>".__('Edit Details', 'wpsc')." </strong></div>\n\r";
	echo "<div class='editing_this_group'><p>".str_replace("[categorisation]", $current_categorisation['name'], __('You are editing an item in the &quot;[categorisation]&quot; Group', 'wpsc')) ."</p></div>";
	}
	
	
	echo "        <div id='formcontent'>\n\r";
	echo "        </div>\n\r";
	if (function_exists('add_object_page')) {
		echo "</div>";
	}
	echo "</form>\n\r";
	echo "        </div>\n\r";
	?>

	
<div id="blank_item">
	<h3 class="form_heading"><?php echo str_replace("[categorisation]", $current_categorisation['name'], __('Add Category', 'wpsc')); ?></h3>
	<div class="inside">
	  <a href='' onclick='return showaddform()' class='add_category_link'><span><?php echo str_replace("[categorisation]", $current_categorisation['name'], __('+ Add new category to the &quot;[categorisation]&quot; Group', 'wpsc')); ?> </span></a>
	  <span class="setting-description"><?php echo __('Adding a new category here will make it available when you add or edit a product.', 'wpsc');?></span>
	</div>
</div>

	
	<div id='additem' class='postbox'>
		<h3 class='hndle'><?php echo str_replace("[categorisation]", $current_categorisation['name'], __('You are adding a new item to the &quot;[categorisation]&quot; Group', 'wpsc')); ?></h3>
		<div class='inside'>
			<form method='post' enctype='multipart/form-data' class='additem'>
				<div class='editing_this_group'><p> <?php echo "".str_replace("[categorisation]", $current_categorisation['name'], __('You are adding a new item to the &quot;[categorisation]&quot; Group', 'wpsc')) .""; ?></p></div>
	  			  
				<table class='category_forms form_table'>
					<tr>
						<td>
							<?php echo __('Name', 'wpsc');?>:
						</td>
						<td>
							<input type='text' class="text" name='name' value=''  />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo __('Description', 'wpsc');?>:
						</td>
						<td>
							<textarea name='description' rows='8'></textarea>
						</td>
					</tr>

						<tr>
						<td>
							<?php echo __('Group Parent', 'wpsc');?>:
						</td>
						<td>
							<?php echo wpsc_parent_category_list($current_categorisation['id'], 0,0); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo __('Group&nbsp;Image', 'wpsc');?>:
						</td>
						<td>
							<input type='file' name='image' value='' />
						</td>
					</tr>
			<?php
			if(function_exists("getimagesize")) {
					?>
					<tr>
						<td>
						</td>
						<td>
							<?php echo __('Height', 'wpsc');?>:<input type='text' size='6' name='height' value='<?php echo get_option('category_image_height'); ?>' /> <?php echo __('Width', 'wpsc');?>:<input type='text' size='6' name='width' value='<?php echo get_option('category_image_width'); ?>' /> <br />
							<span class='wpscsmall description'><?php echo __('You can upload thumbnail images for each group. To display Group details in your shop you must configure these settings under <a href="admin.php?page=wpsc-settings&tab=presentation">Presentation Settings</a>.', 'wpsc'); ?></span>
						</td>
					</tr>
					<?php
			}
			?>
				<?php  wpsc_category_tm(); //category target market checkbox ?>
					<tr>
						<td colspan='2' class='category_presentation_settings'>
							<h4><?php echo __('Presentation Settings', 'wpsc');?></h4>
							<span class='small'><?php echo __('To over-ride the presentation settings for this group you can enter in your prefered settings here', 'wpsc'); ?></span>
						</td>
					</tr>



					<tr>
						<td>
							<?php echo __('Catalog View', 'wpsc');?>:
						</td>
						<td>
								<select name='product_view'>
									<option value='default' <?php echo $product_view1; ?>><?php echo __('Default View', 'wpsc');?></option>
									<?php
									if(function_exists('product_display_list')) {
										?>
										<option value='list' <?php echo $product_view2; ?>><?php echo __('List View', 'wpsc');?></option>
										<?php
									}  else {
										?>
										<option value='list' disabled='disabled' <?php echo $product_view2; ?>><?php echo __('List View', 'wpsc');?></option>
										<?php
									}

									if(function_exists('product_display_grid')) {
										?>
									<option value='grid' <?php echo $product_view3; ?>><?php echo __('Grid View', 'wpsc');?></option>
										<?php
									} else {
										?>
									<option value='grid' disabled='disabled' <?php echo $product_view3; ?>><?php echo __('Grid View', 'wpsc');?></option>
										<?php
									}
									?>
								</select>
						</td>
					</tr>

					<tr>
						<td colspan='2'>
						<?php echo __('Thumbnail&nbsp;Size', 'wpsc'); ?>:

							<?php echo __('Height', 'wpsc'); ?>: <input type='text' value='' name='product_height' size='6'/>
							<?php echo __('Width', 'wpsc'); ?>: <input type='text' value='' name='product_width' size='6'/> <br/>
						</td>
					</tr>

	          <tr>
	          	<td colspan='2' class='category_presentation_settings'>
	          		<h4><?php _e('Checkout Settings', 'wpsc'); ?></h4>
	          		<?php /* <span class='small'><?php _e('To over-ride the presentation settings for this group you can enter in your prefered settings here', 'wpsc'); ?></span> */ ?>
	          	</td>
	          </tr>

					<tr>
            <td><?php _e("This category requires additional checkout form fields",'wpsc'); ?>:</td>
            <td>
							<select name='use_additonal_form_set'>
								<option value=''>None</option>
								<?php
									$checkout_sets = get_option('wpsc_checkout_form_sets');
									unset($checkout_sets[0]);
									foreach((array)$checkout_sets as $key => $value) {
										$selected_state = "";
										if($_GET['checkout-set'] == $key) {
											$selected_state = "selected='selected'";
										}
										echo "<option {$selected_state} value='{$key}'>".stripslashes($value)."</option>";
									}
								?>
							</select>
							<?php
							/*  <label><input type="radio" name="uses_additonal_forms" value="1"/><?php _e("Yes",'wpsc'); ?></label> */
							/*  <label><input type="radio" checked="checked" name="uses_additonal_forms" value="0"/><?php _e("No",'wpsc'); ?></label>*/
							?>
            </td>
          </tr>
          
					<tr>
						<td colspan='2'>						</td>
          </tr>

					<tr>
            <td><?php _e("Products in this category use the billing address to calculate shipping",'wpsc'); ?>:</td>
            <td>
							<label><input type="radio" name="uses_billing_address" value="1"/><?php _e("Yes",'wpsc'); ?></label>
							<label><input type="radio" checked="checked" name="uses_billing_address" value="0"/><?php _e("No",'wpsc'); ?></label>
            </td>
          </tr>

					<tr>
						<td>
						</td>
						<td class='last_row'>

							<input type='hidden' name='categorisation_group' value='<?php echo $current_categorisation['id']; ?>' />
							<input type='hidden' name='submit_action' value='add' />
							<input class='button-primary' type='submit' name='submit' value='<?php echo __('Add Category', 'wpsc');?>' />
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>

	
	</td></tr>
	</table>
	</div>
	<?php
  }

?>
