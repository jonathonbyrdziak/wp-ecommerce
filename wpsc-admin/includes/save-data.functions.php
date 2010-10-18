<?php

/**
 * WP eCommerce form and other data saving functions
 *
 * This is used for functions that save things like variation sets and product grpups that would be too large to have in the ajax.php file.
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 
/**
 * Saves the variation set data
 */
function wpsc_save_variation_set() {
  global $wpdb;

  check_admin_referer('edit-variation', 'wpsc-edit-variation');
  $imagedir = WPSC_FILE_PATH."/variation_images/";
  
 /*  delete variation_value */
  if($_GET['delete_value'] == 'true') {
   if(is_numeric($_GET['variation_id']) && is_numeric($_GET['value_id'])) {
			//exit("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `value_id` = '".$_GET['value_id']."'");
			$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `value_id` = '".$_GET['value_id']."'");
			$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '".$_GET['value_id']."' AND `variation_id` = '".$_GET['variation_id']."' LIMIT 1");
		}
	}
  
 /* add variation */
  if($_POST['submit_action'] == "add") {
    //exit("<pre>".print_r($_POST,true)."</pre>");
    $variation_sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_VARIATIONS."` (`name`, `variation_association`) VALUES ( '".$_POST['name']."', 0);";
    if($wpdb->query($variation_sql)) {
      $variation_id = $wpdb->get_results("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` LIMIT 1",ARRAY_A);
      $variation_id = $variation_id[0]['id'];
      $variation_values = $_POST['new_variation_values'];
      $num = 0;
      $variation_value_sql_items = array();
      foreach($variation_values as $variation_value) {
        $variation_value_sql_items[] = " ( '".$wpdb->escape(trim($variation_value))."', '".$variation_id."')";
        $num++;
			}
      $wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_VALUES."` ( `name` , `variation_id` )  VALUES ".implode(", ", $variation_value_sql_items));
      
      //echo "<div class='updated'><p align='center'>".__('The item has been added', 'wpsc')."</p></div>";
		} else {
			//echo "<div class='updated'><p align='center'>".__('The item has not been added', 'wpsc')."</p></div>";
		}
	}
    
    
    
  /* edit variation */
  if(($_POST['submit_action'] == "edit") && is_numeric($_POST['variation_id'])) {
    //exit("<pre>".print_r($_POST,true)."</pre>");
    $variation_id = absint($_POST['variation_id']);
    foreach($_POST['variation_values'] as $variation_value_id => $variation_value) {
      if(is_numeric($variation_value_id)) {
        $variation_value_state = $wpdb->get_results("SELECT `name` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '$variation_value_id' AND `variation_id` = '$variation_id' LIMIT 1",ARRAY_A);
        $variation_value_state = $variation_value_state[0]['name'];
			}
        
      if($variation_value_state != $variation_value) {
        $wpdb->query("UPDATE `".WPSC_TABLE_VARIATION_VALUES."` SET `name` = '".$wpdb->escape($variation_value)."' WHERE `id` = '$variation_value_id' AND `variation_id` = '".$variation_id."' LIMIT 1;");
			}
		}
    
    $num = 0; 
    if($_POST['new_variation_values'] != null) {
      $num = 0;
      $variation_value_sql_items = array();
      foreach($_POST['new_variation_values'] as $variation_value) {
        $variation_value_sql_items[] = "('".$wpdb->escape(trim($variation_value))."', '".$variation_id."')";
        $num++;
			}
      $wpdb->query("INSERT INTO `".WPSC_TABLE_VARIATION_VALUES."` ( `name` , `variation_id` )  VALUES ".implode(", ", $variation_value_sql_items));
		}
    
    $updatesql = "UPDATE `".WPSC_TABLE_PRODUCT_VARIATIONS."` SET `name` = '".$wpdb->escape($_POST['name'])."' WHERE `id`='".$variation_id."' LIMIT 1";
    $wpdb->query($updatesql);
  
    //echo "<div class='updated'><p align='center'>".__('The variation has been edited.', 'wpsc')."</p></div>";
	}
	
	if($product_id > 0) {
		$sendback = add_query_arg('product_id', $product_id);
	}
	
		$sendback = add_query_arg('message', 1);
  ///exit('<pre>'.print_r($_POST,true).'</pre>');
		wp_redirect($sendback);
}


?>