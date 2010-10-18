<?php  
function nzshpcrt_country_list($selected_country = null) {
  global $wpdb;
  $output = "<option value=''></option>";
  if($selected_country == null) {
    $output = "<option value=''>".__('Please select', 'wpsc')."</option>";
	}
  $country_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
  foreach ($country_data as $country) {
    $selected ='';
    if($selected_country == $country['isocode']) {
      $selected = "selected='true'";
		}
    $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>";
	}
  return $output;
}

function nzshpcrt_region_list($selected_country = null, $selected_region = null) {
  global $wpdb;
  if($selected_region == null) {
    $selected_region = get_option('base_region');
	}
  $output = "";
  $region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$selected_country."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
  if($region_list != null) {
    $output .= "<select name='base_region'>\n\r";
    $output .= "<option value=''>None</option>";
    foreach($region_list as $region) {
      if($selected_region == $region['id']) {
        $selected = "selected='true'";
			} else {
				$selected = "";
			}
      $output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
		}
    $output .= "</select>\n\r";    
	} else {
		$output .= "<select name='base_region' disabled='true'><option value=''>None</option></select>\n\r";
	}
  return $output;
}
  
function nzshpcrt_form_field_list($selected_field = null)
  {
  global $wpdb;
  $output = "";
  $output .= "<option value=''>Please choose</option>";
  $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1';";
  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
  foreach ((array)$form_data as $form) {
    $selected ='';
    if($selected_field == $form['id']) {
      $selected = "selected='true'";
		}
    $output .= "<option value='".$form['id']."' $selected>".$form['name']."</option>";
	}
  return $output;
}
  
  
function wpsc_parent_category_list($group_id, $category_id, $category_parent_id) {
  global $wpdb,$category_data;
  $options = "";
  $options .= "<option value='0'>".__('Select Parent', 'wpsc')."</option>\r\n";
  $options .= wpsc_category_options((int)$group_id, (int)$category_id, null, 0, (int)$category_parent_id);   
  $concat .= "<select name='category_parent'>".$options."</select>\r\n";    
  return $concat;
}

function wpsc_category_options($group_id, $this_category = null, $category_id = null, $iteration = 0, $selected_id = null) {
  /*
   * Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
   */
  global $wpdb;
  $siteurl = get_option('siteurl');
  if(is_numeric($category_id)) {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` = '$group_id' AND `active`='1' AND `id` != '$this_category' AND `category_parent` = '$category_id'  ORDER BY `id` ASC",ARRAY_A);
	} else {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` = '$group_id' AND `active`='1' AND `id` != '$this_category' AND `category_parent` = '0'  ORDER BY `id` ASC",ARRAY_A);
	}
  foreach((array)$values as $option) {
    if($selected_id == $option['id']) {
      $selected = "selected='selected'";
		}
    
    $output .= "<option $selected value='".$option['id']."'>".str_repeat("-", $iteration).stripslashes($option['name'])."</option>\r\n";
    $output .= wpsc_category_options($group_id, $this_category, $option['id'], $iteration+1, $selected_id);
    $selected = "";
	}
  return $output;
}
  

function wpsc_uploaded_files() {
  global $wpdb, $wpsc_uploaded_file_cache;
  
  $dir = @opendir(WPSC_FILE_DIR);
  $num = 0;
  if(count($wpsc_uploaded_file_cache) > 0) {
    $dirlist = $wpsc_uploaded_file_cache;
  } else {
		while(($file = @readdir($dir)) !== false) {
			//filter out the dots, macintosh hidden files and any backup files
			if(($file != "..") && ($file != ".") && ($file != "product_files")  && ($file != "preview_clips") && !stristr($file, "~") && !( strpos($file, ".") === 0 ) && !strpos($file, ".old")) {
				$file_data = $wpdb->get_row("SELECT `id`,`filename` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `idhash` LIKE '".$wpdb->escape($file)."' LIMIT 1",ARRAY_A);
				if($file_data != null) {
					$dirlist[$num]['display_filename'] = $file_data['filename'];
					$dirlist[$num]['file_id'] = $file_data['id'];
				} else {
					$dirlist[$num]['display_filename'] = $file;
					$dirlist[$num]['file_id'] = null;
				}        
				$dirlist[$num]['real_filename'] = $file;
				$num++;
			}
		}
		if(count($dirlist) > 0) {
			$wpsc_uploaded_file_cache = $dirlist;
		}
	}
  return $dirlist;
  }
  
  
function wpsc_select_product_file($product_id = null) {
  global $wpdb;
  //return false;
  $product_id = absint($product_id);
  $file_list = wpsc_uploaded_files();
  $file_id = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '".$product_id."' LIMIT 1");
  
	//$product_files = $wpdb->get_row("SELECT `meta_value` FROM  `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` = '".$product_id."' AND `meta_key` = 'product_files'", ARRAY_A);
	
	$product_files = get_product_meta($product_id, 'product_files');
  $output = "<span class='admin_product_notes select_product_note '>".__('Choose a downloadable file for this product:', 'wpsc')."</span><br>";
  $output .= "<div class='ui-widget-content multiple-select  ".((is_numeric($product_id)) ? "edit_" : "")."select_product_file'>";
  $num = 0;
  foreach((array)$file_list as $file) {
    $num++;
	if(is_array($product_files)){
		if (in_array($file['file_id'], $product_files)){
			$checked_curr_file="checked='checked'";
		}
		else{$checked_curr_file="";}
	}
	else{
		if(is_numeric($file_id) && ($file_id == $file['file_id'])){
			$checked_curr_file="checked='checked'";
		}
		else{$checked_curr_file="";}
	}
		$deletion_url =  wp_nonce_url("admin.php?wpsc_admin_action=delete_file&amp;file_id=".$file['file_id'], 'delete_file_'.absint($file['file_id']));
    
    $output .= "<p ".((($num % 2) > 0) ? '' : "class='alt'").">\n";
    $output .= "  <input type='checkbox' name='select_product_file[]' value='".$file['real_filename']."' id='select_product_file_$num' ".$checked_curr_file." />\n";
    $output .= "  <label for='select_product_file_$num'>".$file['display_filename']."</label>\n";
    $output .= "  <a class='file_delete_button' href='{$deletion_url}'>\n";
    $output .= "    <img src='".WPSC_URL."/images/cross.png' />\n";
    $output .= "  </a>\n";
    $output .= "</p>\n";
  }
  $output .= "</div>";
  $output .= "<div class='".((is_numeric($product_id)) ? "edit_" : "")."select_product_handle'><div></div></div>";
  $output .= "<script type='text/javascript'>\n\r";
  $output .= "var select_min_height = ".(25*3).";\n\r";
  $output .= "var select_max_height = ".(25*($num+1)).";\n\r";  
  $output .= "</script>";  
  return $output;
}
  
  
function wpsc_select_variation_file($file_id, $variation_ids, $variation_combination_id = null) {
  global $wpdb;
  //return false;
  $file_list = wpsc_uploaded_files();
  $unique_id_component = ((int)$variation_combination_id)."_".str_replace(",","_",$variation_ids);
  
  $output = "<div class='variation_settings_contents'>\n\r";
  $output .= "<span class='admin_product_notes select_product_note '>".__('Choose a downloadable file for this variation', 'wpsc')."</span>\n\r";
  $output .= "<div class='select_variation_file'>\n\r";
  
  $num = 0;
  $output .= "  <p>\n\r";
  $output .= "    <input type='radio' name='variation_priceandstock[{$variation_ids}][file]' value='0' id='select_variation_file{$unique_id_component}_{$num}' ".((!is_numeric($file_id) || ($file_id < 1)) ? "checked='checked'" : "")." />\n\r";
  $output .= "    <label for='select_variation_file{$unique_id_component}_{$num}'>".__('No Product', 'wpsc')."</label>\n\r";
  $output .= "  </p>\n\r";
  foreach((array)$file_list as $file) {
    $num++;
    $output .= "  <p>\n\r";
    $output .= "    <input type='radio' name='variation_priceandstock[{$variation_ids}][file]' value='".$file['file_id']."' id='select_variation_file{$unique_id_component}_{$num}' ".((is_numeric($file_id) && ($file_id == $file['file_id'])) ? "checked='checked'" : "")." />\n\r";
    $output .= "    <label for='select_variation_file{$unique_id_component}_{$num}'>".$file['display_filename']."</label>\n\r";
    $output .= "  </p>\n\r";
  }
  $output .= "</div>\n\r";
  $output .= "</div>\n\r";
  return $output;
}
  
  
function wpsc_list_product_themes($theme_name = null) {
  global $wpdb, $wpsc_theme_path;
  $selected_theme = get_option('wpsc_selected_theme');
  if($selected_theme == '') {
    $selected_theme = 'default';
	}
    
  $theme_path = $wpsc_theme_path;
  $theme_list = wpsc_list_dir($theme_path);
  foreach($theme_list as $theme_file) {
    if(is_dir($theme_path.$theme_file) && is_file($theme_path.$theme_file."/".$theme_file.".css")) {
      $theme[$theme_file] = get_theme_data($theme_path.$theme_file."/".$theme_file.".css");
		}
	}  
  $output .= "<select name='wpsc_options[wpsc_selected_theme]'>\n\r";
  foreach((array)$theme as $theme_file=>$theme_data) {
    if(stristr($theme_file, $selected_theme)) {
      $selected = "selected='selected'";
		} else {
			$selected = "";
		}
    $output .= "<option value='$theme_file' $selected>".$theme_data['Name']."</option>\n\r";
	}
  $output .= "</select>\n\r";    
  return $output;
}

?>