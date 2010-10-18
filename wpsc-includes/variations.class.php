<?php
/**
 * wp- e-Commerce Variations class
 *
 * This is the code that handles adding, editing and displaying variations on Products
 */

class nzshpcrt_variations {  
  function nzshpcrt_variations() { 
    global $wpdb;
	}
	
	
  /**
   * This method creates a list of checkboxes to associate variations with products
   *  if a product ID is supplied, it displays variations associated with that product.
   *  This is used when adding and editing products
	*/
  function list_variations($product_id = null) {
  	global $wpdb;
    $options = "";
    $variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` ORDER BY `id` ASC",ARRAY_A);

    $options .= "<div class='variation_checkboxes'>\n\r";
    foreach((array)$variations as $variation) {
      $variation_values = array();    
      $checked = "";
      
			$values_box_state = "style='display: none;' ";
      if($product_id > 0) {
        // if the product ID is greater than 0, check to see if the variation is associated.
        $check_variation = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}') AND `variation_id` IN ('{$variation['id']}') LIMIT 1");
        if($check_variation > 0) {
          $checked = "checked='checked'";
					$values_box_state = "";
        }
      }
    
      $options .= "  <div class='variation_box'>\n\r";
      $options .= "    <label class='variation_checkbox{$product_id}'><input type='checkbox' $checked onchange='{$if_adding}variation_value_list(\"{$product_id}\", jQuery(this).parents(\"div.variation_box\"));' value='1' name='variations[{$variation['id']}]' class='variation_checkbox' />{$variation['name']}</label>\n\r";
      /**
      *  get the list of variation values
			*  need different input names for editing and adding due to using different keys
			*/
      if(($product_id > 0) && ($check_variation > 0)) {
				$variation_values_sql = "SELECT `values`.`id` , `values`.`name` , (\n";
				$variation_values_sql .= "	SELECT `visible`\n";
				$variation_values_sql .= "	FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."`\n";
				$variation_values_sql .= "	WHERE `value_id` = `values`.`id`\n";
				$variation_values_sql .= "	AND `product_id`\n";
				$variation_values_sql .= "	IN (\n";
				$variation_values_sql .= "	'{$product_id}'\n";
				$variation_values_sql .= "	)\n";
				$variation_values_sql .= ") AS `visible`\n";
				$variation_values_sql .= "FROM `".WPSC_TABLE_VARIATION_VALUES."` AS `values`\n";
				$variation_values_sql .= "WHERE `values`.`variation_id` IN ( '{$variation['id']}' )\n";
				$variation_values_sql .= "ORDER BY `values`.`id`\n";


				$variation_values = $wpdb->get_results($variation_values_sql,ARRAY_A);
				//echo $variation_values_sql;
				//echo "SELECT `a`.`visible`, `b`.*  FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` AS `a` JOIN `".WPSC_TABLE_VARIATION_VALUES."` AS `b` ON `a`.`value_id` = `b`.`id`  WHERE `a`.`product_id` IN('{$product_id}') AND `a`.`variation_id` IN('{$variation['id']}')";
			}
			if(count($variation_values) < 1) {
				$variation_values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` IN('{$variation['id']}')",ARRAY_A);
			}
			
				
      
				// display the list of variation values
				$options .= "    <div class='variation_values_box' $values_box_state>\n\r";
				foreach($variation_values as $variation_value) {
					$checked = '';
					$variation_value['name'] = stripslashes($variation_value['name']);
					if($variation_value['visible'] > 0) {
						$checked = "checked='checked'";
					}
					$options .= "     <label class='variation_checkbox{$product_id}'><input type='checkbox' $checked value='1' onchange='{$if_adding}variation_value_list(\"{$product_id}\", jQuery(this).parents(\"div.variation_box\"));' name='edit_var_val[{$variation['id']}][{$variation_value['id']}]' />{$variation_value['name']}</label>\n\r";
				}
				$options .= "    </div>\n\r";
				$options .= "  </div>\n\r";
      }
    $options .= "</div>\n\r";
    return $options;
  }
    
	
  /**
   * This appears to be obsolete, but cannot be removed yet as it may still be referenced in the code
   */
  function display_variation_values($prefix,$variation_id) {
    global $wpdb;
    if(is_numeric($variation_id)) {
      //$variation_values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` = '{$variation_id}' ORDER BY `id` ASC",ARRAY_A);
      if($variation_values != null) {
			}
		}
    return $output;
	}
    
  
  /**
   * This method imitates the array sent by POST one would expect when adding a variation, it is used for the ajax code.
   */
  function falsepost_variation_values($variation_id) {
    global $wpdb;
    if(is_numeric($variation_id)) {
      $variation_values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` = '{$variation_id}' ORDER BY `id` ASC",ARRAY_A);
      if($variation_values != null) {
        foreach($variation_values as $variation_value) {
          if(isset($_POST['edit_var_val'])) {
            if($_POST['edit_var_val'][$variation_id][$variation_value['id']] == 1) {
              $output_variation_values[$variation_id][$variation_value['id']]['active'] = 1;
            } else {
							$output_variation_values[$variation_id][$variation_value['id']]['active'] = 0;
            }
          } else {
						$output_variation_values[$variation_id][$variation_value['id']]['active'] = 1;
				  }
          $output_variation_values[$variation_id][$variation_value['id']]['blank'] = 'null';
				}
			}
		}
    return $output_variation_values;
	}
    
    
  /**
   * This method adds variations to an already existing product, it must be passed a product ID and a list of variations to add.
   */
  function add_to_existing_product($product_id,$variation_list) {
     global $wpdb;
    if(is_numeric($product_id)) {
      foreach($variation_list as $variation_id => $variation_values) {
        if(is_numeric($variation_id)) {
          $num = 0;
          $variation_assoc_sql = "INSERT INTO `".WPSC_TABLE_VARIATION_ASSOC."` ( `type` , `name` , `associated_id` , `variation_id` ) VALUES ( 'product', '', '{$product_id}', '{$variation_id}');";

          $product_assoc_sql = "INSERT INTO `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` ( `product_id` , `value_id` , `visible` , `variation_id` ) VALUES";
          foreach($variation_values as $variation_value_id => $variation_value_properties) {
            if(is_numeric($variation_value_id)) {
              switch($num) {
                case 0:
                $comma = '';
                break;
                
                default:
                $comma = ', ';
                break;
							}
                
              if($variation_value_properties['active'] == 1) {
                $active = 1;
							} else {
								$active = 0;
							}
              $product_assoc_sql .= "$comma ( '$product_id', '$variation_value_id', '$active', '$variation_id')";
              $num++;
						}
					}
          $product_assoc_sql .= ";";
          $wpdb->query($product_assoc_sql);
          $wpdb->query($variation_assoc_sql);
				}
			}
		}
    return $output;
	}
  
  
  /**
   * This is one part of the code that displays the variation combination forms in the add and edit product pages.
   * If this fails to find any data about the variation combinations, it runs "variations_add_grid_view" instead
   */
  function variations_grid_view($product_id, $variation_ids = null,  $variation_values = null, $selected_price = null, $limited_stock = null, $current_stock=null) {
    global $wpdb;
    if($product_id > 0) { // if we have a product ID
			$product_id = (int)$product_id;
			$product_data = $wpdb->get_row("SELECT `price`, `quantity`, `quantity_limited`, `weight`, `weight_unit` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN ('{$product_id}') LIMIT 1", ARRAY_A);
			$stock_column_state = '';
			if($product_data['quantity_limited'] == 0) {
				$stock_column_state = " style='display: none;'";
			}
			$limited_stock = null;
			if(isset($_POST['limited_stock'])) {
				if($_POST['limited_stock'] == 'true') {
					$limited_stock = true;
				}
			}
			$associated_variations = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` = '{$product_id}' ORDER BY `id` ASC");
		} else {
			$associated_variations = $variation_ids;
			
			$product_data['price'] = $selected_price;
			$product_data['quantity'] = $current_stock;
		
			$stock_column_state = '';
			if($limited_stock != true) {
				$stock_column_state = " style='display: none;'";
			}
			$limited_stock = null;
			if(isset($_POST['limited_stock'])) {
				if($_POST['limited_stock'] == 'true') {
					$limited_stock = true;
				}
			}

		}
		
		$variation_count = count($associated_variations);
		asort($associated_variations);
			
    if($variation_count > 0) {
      $output .= "<table class='product_variation_grid'>\n\r";   
      $output .= "  <tr>\n\r";
      $output .= "    <th class='variations titles'>".__('Variation', 'wpsc')."</th>\n\r";
      $output .= "    <th class='titles stock' $stock_column_state >".__('Stock', 'wpsc')."</th>\n\r";
      $output .= "    <th class='titles price'>".__('Price', 'wpsc')."</th>\n\r";
      //$output .= "    <th class='titles weight'>".__('Weight', 'wpsc')."</th>\n\r";
      $output .= "    <th class='titles'>".__('More', 'wpsc')."&nbsp;</th>\n\r";
      $output .= "  </tr>\n\r";
      

		// generate all the various bits of SQL to bind the tables together
		$join_selected_cols = array();
		$join_subselect_cols = array();
		$join_tables = array();
		$join_conditions = array();
		foreach((array)$associated_variations as $variation) {
     	 $variation = (int)$variation;
      
			$excluded_value_sql = '';
			if($product_id > 0 ) {
			  $included_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` IN('{$product_id}') AND `variation_id` IN ('{$variation}') AND `visible` IN ('1')");
				$included_values_sql = "AND `a{$variation}`.`id` IN('".implode("','", $included_values)."')";
			} else if(count($variation_values) > 0) {
				$included_values_sql = "AND `a{$variation}`.`id` IN('".implode("','", $variation_values)."')";
			}
							
	      $join_selected_cols[] = "`a{$variation}`.`id` AS `id_{$variation}`, `a{$variation}`.`name` AS `name_{$variation}`";
	      $join_subselect_cols[] = "`a{$variation}`.`id`";
	      $join_tables[] = "`".WPSC_TABLE_VARIATION_VALUES."` AS `a{$variation}`";
	      $join_conditions[] = "`a{$variation}`.`variation_id` = '{$variation}' $included_values_sql";
    	}

		$variation_count = count($associated_variations);
		$all_variation_ids = implode(",", $associated_variations);
  		$join_subselect_cols = implode(", ", $join_subselect_cols);
    
		$join_selected_cols[] = "(
			SELECT `priceandstock_id`
			FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."`
			WHERE `product_id` = '{$product_id}'
			AND `value_id`
			IN ( {$join_subselect_cols} )
			AND `all_variation_ids`
			IN ( '{$all_variation_ids}'	)
			GROUP BY `priceandstock_id`
			HAVING COUNT( `priceandstock_id` ) = '{$variation_count}'
			LIMIT 1
		) AS `priceandstock_id`";
        // implode the SQL statment segments into bigger segments
    $join_selected_cols = implode(", ", $join_selected_cols);
    $join_tables = implode("\nJOIN ", $join_tables);
    $join_conditions = implode("\nAND ", $join_conditions);
    
    // Assemble and execute the SQL query
    $associated_variation_values = $wpdb->get_results(
    "SELECT {$join_selected_cols}
    FROM {$join_tables}
    WHERE {$join_conditions}
    ORDER BY {$join_subselect_cols}"
    , ARRAY_A);
/*
	exit("SELECT {$join_selected_cols}
    FROM {$join_tables}
    WHERE {$join_conditions}
    ORDER BY {$join_subselect_cols}");
*/
    // extract the price and stock IDs and make them into a comma seperated string.
		$priceandstock_ids = array();
		foreach((array)$associated_variation_values as $variation_set) {
		  if($variation_set['priceandstock_id'] > 0) {
				$priceandstock_ids[] = $variation_set['priceandstock_id'];
		  }
		}
    $priceandstock_id_string = implode(", ", $priceandstock_ids);
		if(count($priceandstock_ids)) {
			$unindexed_variation_properties_data = $wpdb->get_results("SELECT `id`, `stock`, `price`, `weight`,`weight_unit`, `file` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` IN({$priceandstock_id_string})", ARRAY_A);
		}
	
		// Index the variation properties data according to the price and stock ID.
		$variation_properties_data = array();
		foreach((array)$unindexed_variation_properties_data as $properties_data) {
		  $variation_properties_data[$properties_data['id']] = $properties_data;
		}
		// wipe the unindexed data to save memory
		unset($unindexed_variation_properties_data); 
		
		
    // add variation code ends
			foreach((array)$associated_variation_values as $key => $variation_set) {
				$variation_id_array = array();
				$variation_names = array();
				foreach($associated_variations as $variation_id) {
					$variation_id_array[] = $variation_set["id_{$variation_id}"];
					$variation_names[] = $variation_set["name_{$variation_id}"];
				}
				
				$variation_names = implode(", ", $variation_names);
				$variation_ids = implode(",", $variation_id_array);
				$variation_settings_uniqueid = $product_id."_".str_replace(",","_",$variation_ids);
				
				$group_defining_class = '';
				$next_id_set = array_keys((array)$variation_sets[$key+1]);
				//echo "<pre>".print_r($variation_set,true)."</pre>";
				if($variation_id_array[0] != $next_id_set[0]) {
					$group_defining_class = "group_boundary";
				}
				if(isset($variation_properties_data[$variation_set['priceandstock_id']])) {
					$product_price = $variation_properties_data[$variation_set['priceandstock_id']]['price'];
					$product_stock = $variation_properties_data[$variation_set['priceandstock_id']]['stock'];
					$product_weight = $variation_properties_data[$variation_set['priceandstock_id']]['weight'];
					$product_weight_unit = $variation_properties_data[$variation_set['priceandstock_id']]['weight_unit'];
					$product_file_id = $variation_properties_data[$variation_set['priceandstock_id']]['file'];
				} else {
					$product_price = $product_data['price'];
					$product_stock = $product_data['quantity'];
					$product_weight = $product_data['weight'];
					$product_weight_unit = $product_data['weight_unit'];
					$product_file_id = $product_data['file'];
				}
				
				
				$output .= "  <tr class='variation_row'>\n\r";
				$output .= "    <td class='variations'>".str_replace(" ", "&nbsp;", (stripslashes( $variation_names )))."</td>\n\r";
				$output .= "    <td class='stock' $stock_column_state><input type='text' name='variation_priceandstock[{$variation_ids}][stock]' value='$product_stock' size='3' /></td>\n\r";
				$output .= "    <td class='price'><input type='text' name='variation_priceandstock[{$variation_ids}][price]' value='$product_price' size='6' /></td>\n\r";
	//       $output .= "    <td class='weight'><input type='text' name='variation_priceandstock[{$variation_ids}][weight]' value='' size='3' /></td>\n\r";
				
				$output .= "    <td>\n\r";
				$output .= "      <a href='#' class='variation_edit_button' onclick='return open_variation_settings(\"variation_settings_$variation_settings_uniqueid\")' ><img src='".WPSC_URL."/images/gear__plus.png' alt='".__('Edit', 'wpsc')."' title='".__('Edit', 'wpsc')."'></a>\n\r";
				$output .= "    </td>\n\r";
				
				$output .= "  </tr>\n\r";
				$output .= "  <tr class='settings_row {$group_defining_class}' id='variation_settings_$variation_settings_uniqueid'>\n\r";
				$output .= "    <td colspan='5'>\n\r";
				$output .= "      <div class='variation_settings'>\n\r";
				
				$output .= "        <div class='variation_weight'>\n\r";
				$output .= "          <strong>".__('Weight Settings', 'wpsc')."</strong><br />";
				$output .= "          <input type='text' name='variation_priceandstock[{$variation_ids}][weight]' value='{$product_weight}' size='3' />";

				$output .= "          <select name='variation_priceandstock[{$variation_ids}][weight_unit]'>\n\r";
				$output .= "            <option value='pound' ". (($product_weight_unit == 'pound') ? 'selected' : '') .">Pounds</option>\n\r";
				$output .= "            <option value='ounce' ". ((preg_match("/o(u)?nce/",$product_weight_unit)) ? 'selected' : '') .">Ounces</option>\n\r";
				$output .= "            <option value='gram' ". (($product_weight_unit == 'gram') ? 'selected' : '') .">Grams</option>\n\r";
				$output .= "            <option value='kilogram' ". (($product_weight_unit == 'kilogram') ? 'selected' : '') .">Kilograms</option>\n\r";
				$output .= "          </select>\n\r";

				$output .= "        </div>\n\r";
				$output .= wpsc_select_variation_file($product_file_id, $variation_ids);
				$output .= "      </div>\n\r";
				$output .= "    </td>\n\r";
				$output .= "  </tr>\n\r";
			}
      $output .= "</table>\n\r";    
    }
    return $output;
	}
   
  /**
   * This is the other part of the code that displays the variation combination forms in the add and edit product pages.
   * This is used if "variations_grid_view" fails to find any data about the variation combinations
   */
	function variations_add_grid_view($variations, $variation_values = null, $default_price = null, $limited_stock = true, $product_id = 0) {
	}
	
	
  /**
  * no longer used, calls to this need to be removed before this can. Look at function 'variations_grid_view' instead.
  */
  function display_attached_variations($product_id) {
    global $wpdb;
    return $output;
  }
    
    
   
  /**
  * this function edits the variation values associated with a product, 
   * and creates the values for them in the wp_wpsc_variation_combinations and wp_variation_priceandstock tables
  */
  function edit_product_values($product_id,$variation_value_list, $price = 0) {
		global $wpdb;
		// 		if(function_exists('xdebug_start_trace')) {
		// 			xdebug_start_trace('/var/www/xdebug/traces/edit_product_values');
		// 		}
		
		$variation_id_list = array();
		$modified_values = array();
		$modified_value_variations = array();
		$all_variation_values = array();
		// Edit or update the variation values association table
    foreach((array)$variation_value_list as $variation_id => $submitted_variation_values) {
      $variation_id = absint($variation_id);
			if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}') AND `variation_id` IN ('{$variation_id}')") < 1) {
			  $wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_ASSOC."` ( `type` , `name` , `associated_id` , `variation_id` ) VALUES ( 'product', '', '{$product_id}', '{$variation_id}');");
			}
			$existing_variation_values = $wpdb->get_col("SELECT `value_id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE  `product_id` IN ('{$product_id}') AND `variation_id` IN ('{$variation_id}') AND `visible` IN('1')");
		
			$submitted_variation_values = array_keys($submitted_variation_values);
			
			// compare the submitted and existing arrays in various ways to find the added, removed and unchanged values 
			$unchanged_values = array_intersect($submitted_variation_values, $existing_variation_values);
			
			$removed_values = array_diff($existing_variation_values, $submitted_variation_values);
			$added_values = array_diff($submitted_variation_values, $existing_variation_values);
			
			
			
			$variation_values = array();
			// because we have to preserve PHP 4 compatibility, I cannot use array_fill_keys here.
			foreach($removed_values as $value_id) {
				$variation_values[$value_id] = 0;
			}
			foreach($added_values as $value_id) {
				$variation_values[$value_id] = 1;
			}
			
			
			foreach($unchanged_values as $value_id) {
				$all_variation_values[$value_id] = 1;
			}
			
			$all_variation_values +=  $variation_values;
			foreach($variation_values as $variation_value_id => $variation_state) {
				$variation_value_id = absint($variation_value_id); 
				$visible_state = (int)(bool)$variation_state;
				
				if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = '{$product_id}' AND `value_id` = '{$variation_value_id}'") > 0) {
					// if is present, update it
					$rows_changed = $wpdb->query("UPDATE `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` SET `visible` = '{$visible_state}' WHERE `product_id` = '{$product_id}' AND `value_id` = '".(int)$variation_value_id."' LIMIT 1 ;");
				} else {
					// otherwise, add it
					$wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` ( `product_id` , `value_id` , `visible` , `variation_id` ) VALUES ( '{$product_id}', '{$variation_value_id}', '{$visible_state}', '{$variation_id}')");
				}
				// we probably still need these to generate the combinations
				$modified_values[] = $variation_value_id;
				$modified_value_variations[$variation_value_id] = $variation_id;
			}
			// and this, too
			$variation_id_list[] = $variation_id;
			
		}
		return $all_variation_values; 
		exit();
	}
    
	/**
	* this does nothing now, but is left to avoid fatal errors
	*/
	function edit_add_product_values($product_id,$variation_value_list) {
    global $wpdb;
    return $output;
	}
  
    /**
    * this displays the variation dropdowns or radio buttons on the front end on the sites for products
    * It must be passed a product ID,
    *
    * no_label = true | causes it to pass back the variations with no label text
    * no_div= true | causes it to not wrap the forms in a div tag
    * update_price = true | causes it to include the javascript that updates the price of the product
    */
  function display_product_variations($product_id,$no_label = false, $no_div = false, $update_price = false ) {
    global $wpdb;
    $product_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$product_id."' LIMIT 1",ARRAY_A);
    
    $variation_assoc_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('$product_id')",ARRAY_A);
    $saved_variation_price = 0;
    
    if($variation_assoc_data != null) {
      $first_entries = Array();
			foreach($variation_assoc_data as $variation_association) {
				$variation_ids[] = $variation_association['variation_id'];
			}
			$j=0;
      foreach((array)$variation_assoc_data as $variation_association) {
        $i = 0;
        if($no_div !== true) {			
				}
				if($j==0) {
					$default_topping='checked="checked"';
				} else {
					$default_topping='';
				}
				$j++;
        $variation_id = $variation_association['variation_id'];
        $value_assoc_data = $wpdb->get_results("SELECT `a`.*, `v`.`name` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` AS `a` JOIN `".WPSC_TABLE_VARIATION_VALUES."` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('$product_id') AND `a`.`variation_id` IN ('$variation_id') AND `a`.`visible` IN ('1')",ARRAY_A);
        
        
        $variation_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."`  WHERE `id` IN ('$variation_id') LIMIT 1",ARRAY_A);
        
        
        if($no_label !== true) {
          $output .= "<label for='variation_select_".$product_id."_".$variation_data['id']."'>". $variation_data['name'] . ":</label> ";
				}
        
        
        if(($update_price === true) && (count($variation_ids) >= 1)) {
          $special = 'false';
          if($no_label == true) {
            $special = 'true';
					}
          $on_change = "onchange='change_variation($product_id, Array(\"".implode("\",\"", $variation_ids)."\"), $special)'";
	      } else { $on_change = ''; }
				$special_prefix = '';
				if($no_label == true) {
					$special_prefix = 'special_';
				}
				
				
				if (get_option("checkbox_variations")=='1') {
					$output .= "<br>";
				} else {
					$output .= "<select id='".$special_prefix."variation_select_".$product_id."_".$variation_data['id']."' name='variation[".$variation_data['id']."]' $on_change >";
				}
				
				
				foreach((array)$value_assoc_data as $value_association) {
          if($i == 0) { 
						$first_entries[] = $value_association['value_id']; 
					}
          $check_stock = false;
          
          if(($product_data['quantity_limited'] == 1) && (count($variation_assoc_data) == 1)) {
            $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '$product_id' AND `value_id` IN ( '{$value_association['value_id']}' ) AND `all_variation_ids` IN('{$variation_data['id']}') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '1' LIMIT 1");
            
            $variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1",ARRAY_A);
            $check_stock = true;
            $stock = $variation_stock_data['stock'];
            $variation_price = $variation_stock_data['price'];
          }
		
					if (get_option('checkbox_variations')==1) {
						$output .= "<input type='checkbox' id='variation[".$value_association['id']."]' name='variation[".$variation_data['name']."][]'".$default_topping." value='".$value_association['id']."' onclick='manage_topping(".$product_id.",".$value_association['id'].",".$special.")'>".stripslashes($value_association['name'])."<br>";
					} else {
						if(($check_stock == true) && ($stock < 1)) {
							//$output .= "<option value='".$value_association['id']."' disabled='true'>".stripslashes($value_association['name'])." - ".__('No Stock', 'wpsc')."</option>";
						} else {
							$output .= "<option value='".$value_association['value_id']."'>".stripslashes($value_association['name'])."</option>";
							if($saved_variation_price == 0) {
							  $saved_variation_price = $variation_price;
							}
						}
					}
					$i++;
				}
				if (get_option("checkbox_variations")=='1') {
					$output .= "";
				} else {
					$output .= "</select><br>";
				}
				if($no_div !== true) {
		
				}
			}
		}
    
		if($update_price === true) {
			$first_entry_count = count($first_entries);
			if(($first_entry_count >= 1)) {
				if($saved_variation_price > 0) {
					$price = $saved_variation_price;
				
				} else {
					$price = calculate_product_price($product_id, $first_entries);
				}
			} else { $price = null; }
			return Array($output, $price);
		} else {
			return $output;
		}
	}
   
   function duplicate_variation_values($new_id, $old_id){
   	global $wpdb;
   		$sql = "SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = ".$old_id;
   		$results = $wpdb->get_results($sql, ARRAY_A);
   		foreach((array)$results as $result){
   			$sql = "INSERT INTO `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` (`product_id`,`value_id`,`visible`,`variation_id`) VALUES ('".$new_id."','".$result['value_id']."','".$result['visible']."','".$result['variation_id']."')";
   			$wpdb->query($sql);
   		}
   		$variation_assocs = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_ASSOC." WHERE associated_id = ".$old_id, ARRAY_A);
   		foreach((array)$variation_assocs as $result){
   			$sql ="INSERT INTO `".WPSC_TABLE_VARIATION_ASSOC."` (`type`,`name`,`associated_id`,`variation_id`) VALUES ('".$result['type']."','".$result['name']."','".$new_id."','".$result['variation_id']."')";
   			$wpdb->query($sql);   		
   		}
   		$sql = "SELECT * FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = ".$old_id;
   		$results = $wpdb->get_results($sql, ARRAY_A);
   		foreach((array)$results as $result){
   			$sql ="INSERT INTO `".WPSC_TABLE_VARIATION_COMBINATIONS."` (`product_id`,`priceandstock_id`,`value_id`,`variation_id`, `all_variation_ids`) VALUES ('".$new_id."','".$result['priceandstock_id']."','".$result['value_id']."','".$result['variation_id']."','".$result['all_variation_ids']."')";
   			$wpdb->query($sql);  
   		}
   		$sql = "SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `product_id` = ".$old_id;
   		$results = $wpdb->get_results($sql, ARRAY_A);
   		foreach((array)$results as $result){
   			$sql ="INSERT INTO `".WPSC_TABLE_VARIATION_PROPERTIES."` (`product_id`,`stock`,`price`, `weight`,`weight_unit`,`visibility`,`file`) VALUES ('".$new_id."','".$result['stock']."','".$result['price']."','".$result['weight']."','".$result['weight_unit']."','".$result['visibility']."','".$result['file']."')";
   			$wpdb->query($sql);  
   		}
   }
	  /**
  * this function updates the wp_variation_priceandstock table based on what is passed to it
  * this is used on the add and edit products page, This must be passed a product ID and variation values array
  */
  function update_variation_values($product_id, $variation_values) {
    global $wpdb;
    $product_id = (int)$product_id;
    // declare function for sanitising the keys
    
    //echo("<pre>".print_r($variation_values,true)."</pre>");
    //exit("<pre>".print_r($_POST,true)."</pre>");
		foreach($variation_values as $form_key => $variation_data) {
		
			// split input
			$keys = explode(",",$form_key);
			// sanitise input
			array_walk($keys, 'wpsc_sanitise_keys');
			
			
      $variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$keys)."')");
      asort($variation_ids);
      $all_variation_ids = implode(",", $variation_ids);
			
			
      $variation_price = (float)str_replace(",","",$variation_data['price']);
      $variation_stock =(int)$variation_data['stock']; // having 1.2 stock makes no sense unless dealing with kilograms or some other such measurement
      $variation_weight = (float)$variation_data['weight'];		
      $variation_weight_unit = (string)$variation_data['weight_unit'];		
      $variation_file =(int)$variation_data['file'];		
      $variation_visibility =(int)(bool)$variation_data['visibility'];
      
      
      if((is_numeric($variation_data['stock']) || is_numeric($variation_price))) {
        $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '$product_id' AND `value_id` IN ( '".implode("', '",$keys )."' ) AND `all_variation_ids` IN('$all_variation_ids') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($keys)."' LIMIT 1");
        
        $variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
        
        // if the associated row has already been created
        if(is_numeric($variation_stock_data['id'])) {
          foreach($keys as $key) {
            // if create the records to associate it with a product if they are not present
            if($wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `priceandstock_id` = '{$variation_stock_data['id']}' AND `value_id` = '$key'") < 1) {
              $variation_id = $wpdb->get_var("SELECT `".WPSC_TABLE_VARIATION_VALUES."`.`variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '{$key}'");
              $wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_COMBINATIONS."` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id`, `all_variation_ids` ) VALUES ( '$product_id', '{$variation_stock_data['id']}', '$key', '$variation_id', '$all_variation_ids' );");
            }
          }
          // and start building the SQL query to edit the item
          $variation_sql = null; // return the sql array to null for each trip round the loop
          if(($variation_stock_data['stock'] != $variation_stock)) {
            $variation_sql[] = "`stock` = '{$variation_stock}'";
          }
          
          if(($variation_stock_data['price'] != $variation_price)) {
            $variation_sql[] = "`price` = '{$variation_price}'";
          }
          
          if(($variation_stock_data['weight'] != $variation_weight)) {
            $variation_sql[] = "`weight` = '{$variation_weight}'";
          }
                    
          if(($variation_stock_data['weight_unit'] != $variation_weight_unit)) {
            $variation_sql[] = "weight_unit = '{$variation_weight_unit}'";
          }
          
          if(($variation_stock_data['file'] != $variation_file)) {
            $variation_sql[] = "`file` = '{$variation_file}'";
          }
          // if there is any SQL to execute, make it so
          if($variation_sql != null) { 
            $wpdb->query("UPDATE `".WPSC_TABLE_VARIATION_PROPERTIES."` SET ".implode(",",$variation_sql)."WHERE `id` = '{$variation_stock_data['id']}' LIMIT 1 ;");
          }
        }	else {
          // otherwise, the price and stock row does not exist, make it
          $wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_PROPERTIES."` ( `product_id` , `stock`, `price`, `weight`, `weight_unit`, `file` ) VALUES ('{$product_id}', '{$variation_stock}', '{$variation_price}', '{$variation_weight}', '{$variation_weight_unit}', '{$variation_file}');");
          $variation_priceandstock_id = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` LIMIT 1");
          foreach($keys as $key) {
            // then make sure it is associated with the product and variations.
            if($wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `priceandstock_id` = '{$variation_priceandstock_id}' AND `value_id` = '$key'") < 1) {
              $variation_id = $wpdb->get_var("SELECT `".WPSC_TABLE_VARIATION_VALUES."`.`variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '{$key}'");
              $wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_COMBINATIONS."` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id`, `all_variation_ids` ) VALUES ( '$product_id', '{$variation_priceandstock_id}', '$key', '$variation_id', '$all_variation_ids' );");
            }
          }
        }
      }
    
		} //foreach ends here
		//exit();
  } //function ends here   
  
  
}

?>