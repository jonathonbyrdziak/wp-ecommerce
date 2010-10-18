<?php
/**
 * WPSC Product form generation functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

//$closed_postboxes = (array)get_usermeta( $current_user->ID, 'editproduct');
$variations_processor = new nzshpcrt_variations;


$wpsc_product_defaults =array (
  'id' => '0',
  'name' => '',
  'description' => '',
  'additional_description' => '',
  'price' => '0.00',
  'weight' => '0',
  'weight_unit' => 'pound',
  'pnp' => '0.00',
  'international_pnp' => '0.00',
  'file' => '0',
  'image' => '',
  'category' => '0',
  'brand' => '0',
  'quantity_limited' => '0',
  'quantity' => '0',
  'special' => '0',
  'special_price' => '',
  'display_frontpage' => '0',
  'notax' => '0',
  'publish' => '1',
  'active' => '1',
  'donation' => '0',
  'no_shipping' => '0',
  'thumbnail_image' => '',
  'thumbnail_state' => '1',
  'meta' => 
  array (
    'external_link' => NULL,
    'merchant_notes' => NULL,
    'sku' => NULL,
    'engrave' => '0',
    'can_have_uploaded_image' => '0',
    'table_rate_price' => 
    array (
      'quantity' => 
      array (
        0 => '',
      ),
      'table_price' => 
      array (
        0 => '',
      ),
    ),
  ),
);



function wpsc_display_product_form ($product_id = 0) {
  global $wpdb, $wpsc_product_defaults;
  $product_id = absint($product_id);
	//$variations_processor = new nzshpcrt_variations;
  if($product_id > 0) {

		$product_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
	
		$product_data['meta']['dimensions'] = get_product_meta($product_id, 'dimensions',true);

		$product_data['meta']['external_link'] = get_product_meta($product_id,'external_link',true);
		$product_data['meta']['merchant_notes'] = get_product_meta($product_id,'merchant_notes',true);
		$product_data['meta']['sku'] = get_product_meta($product_id,'sku',true);
		
		$product_data['meta']['engrave'] = get_product_meta($product_id,'engraved',true);
		$product_data['meta']['can_have_uploaded_image'] = get_product_meta($product_id,'can_have_uploaded_image',true);
		
		$product_data['meta']['table_rate_price'] = get_product_meta($product_id,'table_rate_price',true);
		$sql ="SELECT `meta_key`, `meta_value` FROM ".WPSC_TABLE_PRODUCTMETA." WHERE `meta_key` LIKE 'currency%' AND `product_id`=".$product_id;
		$product_data['newCurr']= $wpdb->get_results($sql, ARRAY_A);


		
		//echo "<pre>".print_r($product_data,true)."</pre>";
		if(function_exists('wp_insert_term')) {
			$term_relationships = $wpdb->get_results("SELECT * FROM `{$wpdb->term_relationships}` WHERE object_id = '{$product_id}'", ARRAY_A);
		
			foreach ((array)$term_relationships as $term_relationship) {
				$tt_ids[] = $term_relationship['term_taxonomy_id'];
			}
			foreach ((array)$tt_ids as $tt_id) {
				$term_ids[] = $wpdb->get_var("SELECT `term_id` FROM `{$wpdb->term_taxonomy}` WHERE `term_taxonomy_id` = '{$tt_id}' AND `taxonomy` = 'product_tag' LIMIT 1");
			}
			foreach ((array)$term_ids as $term_id ) {
				if ($term_id != NULL){
					$tags[] = $wpdb->get_var("SELECT `name` FROM `{$wpdb->terms}` WHERE `term_id`='{$term_id}' LIMIT 1");
				}
			}
			if ($tags != NULL){ 
				$imtags = implode(',', $tags);
			}
		}
		//exit('got called<pre>'.print_r($imtags,true).'</pre>');	
	
		$check_variation_value_count = $wpdb->get_var("SELECT COUNT(*) as `count` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = '{$product_id}'");
		
  } else {
    if(isset($_SESSION['wpsc_failed_product_post_data']) && (count($_SESSION['wpsc_failed_product_post_data']) > 0 )) {
			$product_data = array_merge($wpsc_product_defaults, $_SESSION['wpsc_failed_product_post_data']);
			$_SESSION['wpsc_failed_product_post_data'] = null;
    } else {
			$product_data = $wpsc_product_defaults;
		}
  }
  
	$product_data = apply_filters('wpsc_display_product_form_get', $product_data);
  
	$current_user = wp_get_current_user();
  
  // we put the closed postboxes array into the product data to propagate it to each form without having it global.
  $product_data['closed_postboxes'] = (array)get_usermeta( $current_user->ID, 'closedpostboxes_store_page_wpsc-edit-products');
  $product_data['hidden_postboxes'] = (array)get_usermeta( $current_user->ID, 'metaboxhidden_store_page_wpsc-edit-products');
  
  if(count($product_data) > 0) {
		wpsc_product_basic_details_form($product_data);
  }
}
 
function wpsc_product_basic_details_form(&$product_data) {
  global $wpdb,$nzshpcrt_imagesize_info;
	/*<h3 class='hndle'><?php echo  __('Product Details', 'wpsc'); ?> <?php echo __('(enter in your product details here)', 'wpsc'); ?></h3>*/
  ?>
  <h3 class='form_heading'>
 <?php
  if($product_data['id'] > 0) {
		echo __('Edit Product', 'wpsc')." <span>(<a href='".add_query_arg('page','wpsc-edit-products', remove_query_arg('product_id', 'admin.php'))."'>".__('Add Product', 'wpsc')."</a>)</span>";
	} else {
		echo __('Add Product', 'wpsc');
	} 
	?>
	</h3>
	<div>
		<table class='product_editform' >
			<tr>
				<td colspan='2' class='itemfirstcol'>  
				<div style='width:470px'>
					<label for="wpsc_product_name"><?php echo __('Product Name', 'wpsc')?></label>
					<div class='admin_product_name'>
						<input id='wpsc_product_name' class='wpsc_product_name text' size='15' type='text' name='title' value='<?php echo htmlentities(stripslashes($product_data['name']), ENT_QUOTES, 'UTF-8'); ?>' />
						<a href='#' class='shorttag_toggle'></a>
					</div>
					<div class='admin_product_shorttags'>
						<h4>Shortcodes</h4>
	
						<dl>
							<dt><?php echo __('Display Product Shortcode', 'wpsc'); ?>: </dt><dd>[wpsc_products product_id='<?php echo $product_data['id'];?>']</dd>
							<dt><?php echo __('Buy Now Shortcode', 'wpsc'); ?>: </dt><dd>[buy_now_button=<?php echo $product_data['id'];?>]</dd>
							<dt><?php echo __('Add to Cart Shortcode', 'wpsc'); ?>: </dt><dd>[add_to_cart=<?php echo $product_data['id'];?>]</dd>
						</dl>

						<h4>Template Tags</h4>

						<dl>
							<dt><?php echo __('Display Product Template Tag', 'wpsc'); ?>: </dt><dd> &lt;?php echo wpsc_display_products('product_id=<?php echo $product_data['id'];?>'); ?&gt;</dd>
							<dt><?php echo __('Buy Now PHP', 'wpsc'); ?>: </dt><dd>&lt;?php echo wpsc_buy_now_button(<?php echo $product_data['id'];?>); ?&gt;</dd>
							<dt><?php echo __('Add to Cart PHP', 'wpsc'); ?>: </dt><dd>&lt;?php echo wpsc_add_to_cart_button(<?php echo $product_data['id'];?>); ?&gt;</dd>
							<dt><?php echo __('Display Product SKU', 'wpsc'); ?>: </dt><dd>&lt;?php echo wpsc_product_sku(<?php echo $product_data['id'];?>); ?&gt;</dd>
						</dl>
						
						<?php if ( $product_data['id'] > 0 ) { ?>
							<p><a href="<?php echo wpsc_product_url( $product_data['id'] ); ?>" target="_blank" class="button">View product</a></p>
						<?php } ?>
						
					</div>
					</div>
					<div style='clear:both; height: 0px; margin-bottom: 15px;'></div>	
				</td>
			</tr>
		
		
			<tr>
				<td colspan='3' class='skuandprice'>
					<div class='wpsc_floatleft'>
					<?php echo __('Stock Keeping Unit', 'wpsc'); ?> :<br />
					<input size='17' type='text' class='text'  name='productmeta_values[sku]' value='<?php echo htmlentities(stripslashes($product_data['meta']['sku']), ENT_QUOTES, 'UTF-8'); ?>' />
					</div>
					<div class='wpsc_floatleft'>
					<?php echo __('Price', 'wpsc'); ?> :<br />
					<input type='text' class='text' size='17' name='price' value='<?php echo $product_data['price']; ?>' />
					</div>
					<div class='wpsc_floatleft'>
    			   <label for='add_form_special'><?php echo __('Sale Price :', 'wpsc'); ?></label>
			       <div style='display:<?php if(($product_data['special'] == 1) ? 'block' : 'none'); ?>' id='add_special'>
								<?php
								if(is_numeric($product_data['special_price'])) {
									$special_price = number_format(($product_data['price'] - $product_data['special_price']), 2);
								}
								/*
if(0 == $product_data['special_price']){
									$special_price = number_format(( $product_data['special_price']), 2);

								}
*/
								?>
        			  <input type='text' size='17' value='<?php echo $special_price; ?>' name='special_price'/>
			       </div>
			       </div>

      			</td>
    
	
			</tr>
		
			<tr>
				<td ><a href='' class='wpsc_add_new_currency'>+ <?php echo __('New Currency', 'wpsc');?></a></td>
			</tr>
			<tr class='new_layer'>
					<td>
						<label for='newCurrency[]'><?php echo __('Currency type', 'wpsc');?>:</label><br />
						<select name='newCurrency[]' class='newCurrency'>
						<?php
						$currency_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
						foreach((array)$currency_data as $currency) {
							if($isocode == $currency['isocode']) {
								$selected = "selected='selected'";
							} else {
								$selected = "";
							} ?>
							<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> ><?php echo htmlspecialchars($currency['country']); ?> (<?php echo $currency['currency']; ?>)</option>
				<?php	}  
						$currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A) ;
						if($currency_data['symbol'] != '') {
							$currency_sign = $currency_data['symbol_html'];
						} else {
							$currency_sign = $currency_data['code'];
						}
				?>
						</select>
						</td>
						<td>
						<?php echo __('Price', 'wpsc');?> :<br />
						<input type='text' class='text' size='15' name='newCurrPrice[]' value='0.00' />
						<a href='' class='deletelayer' rel='<?php echo $isocode; ?>'><?php echo __('Delete Currency', 'wpsc');?></a>
						</td>

			</tr>
			<?php if(count($product_data['newCurr']) > 0) :
				$i = 0;
				foreach($product_data['newCurr'] as $newCurr){  
				$i++;
				$isocode = str_replace("currency[", "", $newCurr['meta_key']);
				$isocode = str_replace("]", "", $isocode);
			//	exit('ere<pre>'.print_r($isocode, true).'</pre>'); 
				
				?>
					<tr>
						<td>
						<label for='newCurrency[]'><?php echo __('Currency type', 'wpsc');?>:</label><br />
						<select name='newCurrency[]' class='newCurrency'>
						<?php
						$currency_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
						foreach($currency_data as $currency) {
							if($isocode == $currency['isocode']) {
								$selected = "selected='selected'";
							} else {
								$selected = "";
							} ?>
							<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> ><?php echo htmlspecialchars($currency['country']); ?> (<?php echo $currency['currency']; ?>)</option>
				<?php	}  
						$currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A) ;
						if($currency_data['symbol'] != '') {
							$currency_sign = $currency_data['symbol_html'];
						} else {
							$currency_sign = $currency_data['code'];
						}
				?>
						</select>
						</td>
						<td>
						Price<?php //echo __('Price', 'wpsc'); ?> :<br />
						<input type='text' class='text' size='15' name='newCurrPrice[]' value='<?php echo $newCurr['meta_value']; ?>' />
						<a href='' class='wpsc_delete_currency_layer' rel='<?php echo $isocode; ?>'><?php echo __('Delete Currency', 'wpsc');?></a>
						</td>
					</tr>
			<?php } ?>
			<?php endif; ?>
			<tr>
				<td colspan='2'>
					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea" >
				 <?php
						wpsc_the_editor($product_data['description'], 'content', false, false);
				 ?>
				 </div>
				</td>
			</tr>
		
			<tr>
				<td class='itemfirstcol' colspan='2'>
					
					<strong ><?php echo __('Additional Description', 'wpsc'); ?> :</strong><br />			
					
					<textarea name='additional_description' id='additional_description' cols='40' rows='5' ><?php echo stripslashes($product_data['additional_description']); ?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class='meta-box-sortables'>
		<?php
		
		
		$default_order=array(
		  "wpsc_product_category_and_tag_forms",
		  "wpsc_product_price_and_stock_forms",
		  "wpsc_product_shipping_forms",
		  "wpsc_product_variation_forms",
		  "wpsc_product_advanced_forms",
		  "wpsc_product_image_forms",
		  "wpsc_product_download_forms"
		  );
		
	 	$order = apply_filters( 'wpsc_products_page_forms', get_option('wpsc_product_page_order'));
	  
	 	//echo "<pre>".print_r($order,true)."</pre>";
	 	if (($order == '') || (count($order ) < 6)){
				$order = $default_order;
	 	}
	 	$check_missing_items = array_diff($default_order, $order);
	 	
	 	if(count($check_missing_items) > 0) {
	 	  $order = array_merge($check_missing_items, $order);
	 	}
		
		update_option('wpsc_product_page_order', $order);
		foreach((array)$order as $key => $box_function_name) {
			if(function_exists($box_function_name)) {
				echo call_user_func(apply_filters('wpsc_product_page_order_form_name', $box_function_name),$product_data);
			}
		}
		?>
	</div>

	<input type='hidden' name='product_id' id='product_id' value='<?php echo $product_data['id']; ?>' />
	<input type='hidden' name='wpsc_admin_action' value='edit_product' />
	<?php wp_nonce_field('edit-product', 'wpsc-edit-product'); ?>
	<input type='hidden' name='submit_action' value='edit' />
	<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
	 
	
	<input class='button-primary' style='float:left;'  type='submit' name='submit' value='<?php if($product_data['id'] > 0) { 	_e('Update Product', 'wpsc'); } else {	_e('Add New Product', 'wpsc');	} ?>' />&nbsp;
	<a class='submitdelete delete_button' title='<?php echo attribute_escape(__('Delete this product')); ?>' href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=delete_product&amp;product={$product_data['id']}", 'delete_product_' . $product_data['id']); ?>' onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this product '%s'\n 'Cancel' to stop, 'OK' to delete."), $product_data['name'] )) ?>') ) { return true;}return false;"><?php _e('Delete') ?></a>
	<?php
  }





function wpsc_product_category_and_tag_forms($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
	
	$output = '';
	if ($product_data == 'empty') {
		$display = "style='visibility:hidden;'";
	}
	$output .= "<div id='wpsc_product_category_and_tag_forms' class=' postbox ".((array_search('wpsc_product_category_and_tag_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_category_and_tag_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')." >";

    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	    <a class='togbox'>+</a>";
    }
    $output .= __('Categories and Tags', 'wpsc');
    if ($product_data != '') {
			if(function_exists('wp_insert_term')) {
				$term_relationships = $wpdb->get_results("SELECT * FROM `{$wpdb->term_relationships}` WHERE object_id = '{$product_data['id']}'", ARRAY_A);
				//exit('HERE><pre>'.print_r($term_relationships,true).'</pre>');
				foreach ((array)$term_relationships as $term_relationship) {
					$tt_ids[] = $term_relationship['term_taxonomy_id'];
				}
				foreach ((array)$tt_ids as $tt_id) {
					$term_ids[] = $wpdb->get_var("SELECT `term_id` FROM `{$wpdb->term_taxonomy}` WHERE `term_taxonomy_id` = '{$tt_id}' AND `taxonomy` = 'product_tag' LIMIT 1");
				}
				foreach ((array)$term_ids as $term_id ) {
					if ($term_id != NULL){
						$tags[] = $wpdb->get_var("SELECT `name` FROM `{$wpdb->terms}` WHERE `term_id`='{$term_id}' LIMIT 1");
					}
				}
				$imtags = '';
				if ($tags != NULL){ 
					$imtags = implode(',', $tags);
				}
				//exit('HERE<pre>'.print_r($imtags,true).'</pre>');
			}
  	}
    $output .= "
	</h3>
    <div class='inside'>
    <table>";
    $output .= "
      <tr>
      <td class='itemfirstcol'>
				<span class='howto'>".__('Categories', 'wpsc')." </span>
				<div id='categorydiv' >";
					$search_sql = apply_filters('wpsc_product_category_and_tag_forms_group_search_sql', '');
					$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')".$search_sql, ARRAY_A);
					//exit('<pre>'.print_r($categorisation_groups, true).'</pre>');
						foreach((array)$categorisation_groups as $categorisation_group){
							$category_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('{$categorisation_group['id']}')");
							if($category_count > 0) {
								$output .= "<p>";
								$category_group_name = str_replace("[categorisation]", $categorisation_group['name'], __('Select &quot;[categorisation]&quot;', 'wpsc'));
								$output .= "".$category_group_name.":<br />";
								$output .= "</p>";
								if ($product_data == '')
									$output .= wpsc_category_list($categorisation_group['id'], false, 'add_', null, 0, 'name' );
								else 
									$output .= wpsc_category_list($categorisation_group['id'], $product_data['id'], 'edit_', null, 0, 'name' );
								
							}
						}

     $output .= "
			</div>
     </td>
     <td class='itemfirstcol product_tags'>
				<span class='howto'> ".__('Product Tags', 'wpsc')."</span><br />
				<p id='jaxtag'>
					<label for='tags-input' class='hidden'>".__('Product Tags', 'wpsc')."</label>
					<input type='text' value='".$imtags."' tabindex='3' size='20' id='tags-input' class='tags-input' name='product_tags'/>
				<span class='howto'>".__('Separate tags with commas')."</span>
				</p>
				<div id='tagchecklist' onload='tag_update_quickclicks();'></div>

      </td>
      
    </tr>";
    
//				<p class='hide-if-no-js' id='tagcloud-link'><a href='#'>Choose from the most popular tags</a></p>
//       
//         <input type='text' class='text wpsc_tag greytext' value='Add new tag' name='product_tags' id='product_tag'>   
//         <input class='button' type='submit' value='Add' name='wpsc_add_new_tag' />
//         <br /><span class='small_italic greytext'>".__("Separate tags with commas")."</span><br />
//      
//         <p>".$imtags."</p>
//       	<input type='hidden' value='".$imtags."' name='wpsc_existing_tags' />
$output .= "
  </table>
 </div>
</div>";
$output = apply_filters('wpsc_product_category_and_tag_forms_output', $output);

return $output;

}
function wpsc_product_price_and_stock_forms($product_data=''){
		global $closed_postboxes, $wpdb, $variations_processor;
		$table_rate_price = get_product_meta($product_data['id'], 'table_rate_price');
		$custom_tax = get_product_meta($product_data['id'], 'custom_tax');
		
		if ($product_data == 'empty') {
			$display = "style='visibility:hidden;'";
		}
		echo "<div id='wpsc_product_price_and_stock_forms' class='wpsc_product_price_and_stock_forms postbox ".((array_search('wpsc_product_price_and_stock_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_price_and_stock_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')." >";

		echo "<h3 class='hndle'>";

    echo __('Price and Stock Control', 'wpsc');
    echo "
	</h3>
    <div class='inside'>
    <table>
    ";
    
    echo "
    <tr>
       <td>
          <input id='add_form_tax' type='checkbox' name='notax' value='yes' ".(($product_data['notax'] == 1) ? 'checked="checked"' : '')."/>&nbsp;<label for='add_form_tax'>".sprintf(__('Do not include tax (tax is set in <a href="%s"/wp-admin/admin.php?page=wpsc-settings">shop config</a>)', 'wpsc'), get_option("siteurl"))."</label>
       </td>
    </tr>";
    echo "
    <tr>

       <td>
          <input id='add_form_donation' type='checkbox' name='donation' value='yes' ".(($product_data['donation'] == 1) ? 'checked="checked"' : '')." />&nbsp;<label for='add_form_donation'>".__('This is a donation, checking this box populates the donations widget.', 'wpsc')."</label>
       </td>
    </tr>";
    ?>
     <tr>
      <td>
        <input type='checkbox' value='1' name='table_rate_price' id='table_rate_price'  <?php echo ((count($table_rate_price['quantity']) > 0) ? 'checked=\'checked\'' : ''); ?> <?php echo ((wpsc_product_has_variations($product_data['id'])) ? 'disabled=\'disabled\'' : ''); ?> />
        <label for='table_rate_price'><?php echo __('Table Rate Price', 'wpsc'); ?></label>
        <div style='display:<?php echo ((($table_rate_price != '') &&  !wpsc_product_has_variations($product_data['id'])) ? 'block' : 'none'); ?>;' id='table_rate'>
          <a class='add_level' style='cursor:pointer;'>+ Add level</a><br />
          <table>
						<tr>
							<td><?php echo __('Quantity In Cart', 'wpsc'); ?></td>
							<td><?php echo __('Discounted Price', 'wpsc'); ?></td>
						</tr>
						<?php
						if(count($table_rate_price) > 0 ) {
							foreach((array)$table_rate_price['quantity'] as $key => $qty) {
								if($qty != '') {
									?>
									<tr>
										<td>
											<input type="text" size="10" value="<?php echo $qty; ?>" name="productmeta_values[table_rate_price][quantity][]"/> and above
										</td>
										<td>
											<input type="text" size="10" value="<?php echo $table_rate_price['table_price'][$key]; ?>" name="productmeta_values[table_rate_price][table_price][]" />
										</td>
										<td><img src="<?php echo WPSC_URL; ?>/images/cross.png" class="remove_line" /></td>
									</tr>
									<?php
								}
							}
						}
						?>						
						<tr>
							<td><input type='text' size='10' value='' name='productmeta_values[table_rate_price][quantity][]'/> and above</td>
							<td><input type='text' size='10' value='' name='productmeta_values[table_rate_price][table_price][]'/></td>
						</tr>
          </table>
        </div>
      </td>
    </tr>


    
     <tr>
      <td>
        <input type='checkbox' value='1' name='custom_tax' id='custom_tax_checkbox'  <?php echo ((is_numeric($custom_tax) > 0) ? 'checked=\'checked\'' : ''); ?>  />
        <label for='custom_tax_checkbox'><?php echo _e("Custom Tax Rate",'wpsc'); ?></label>
        <div style='display:<?php echo ((is_numeric($custom_tax)) ? 'block' : 'none'); ?>;' id='custom_tax'>
					<input type='text' size='10' value='<?php echo $custom_tax; ?>' name='productmeta_values[custom_tax]'/>
        </div>
      </td>
    </tr>


    
    <?php
    echo "
    <tr>
      <td style='width:430px;'>
      <input class='limited_stock_checkbox' id='add_form_quantity_limited' type='checkbox' value='yes' ".(($product_data['quantity_limited'] == 1) ? 'checked="checked"' : '')." name='quantity_limited'/>"; //onclick='hideelement(\"add_stock\")'
		echo "&nbsp;<label for='add_form_quantity_limited' class='small'>".__('I have a limited number of this item in stock. If the stock runs out, this product will not be available on the shop unless you untick this box or add more stock.', 'wpsc')."</label>";
	
		if ($product_data['id'] > 0){
				$variations_output = $variations_processor->variations_grid_view($product_data['id']); 
				
				if(wpsc_product_has_variations($product_data['id'])) {
						switch($product_data['quantity_limited']) {
							case 1:
							echo "            <div class='edit_stock' style='display: block;'>\n\r";
							break;
							
							default:
							echo "            <div class='edit_stock' style='display: none;'>\n\r";
							break;
						}						
						
						echo "<input class='stock_limit_quantity' name='quantity' style='display:none;' value='".$product_data['quantity']."' />";
						echo "<div style='font-size:9px; padding:5px;'><input type='checkbox' " . $unpublish_oos . " class='inform_when_oos' name='inform_when_oos' /> " . __('If this product runs out of stock set status to Unpublished & email site owner', 'wpsc') . "</div>";
						echo "</div>\n\r";
					} else {
						switch($product_data['quantity_limited']) {
							case 1:
							echo "            <div class='edit_stock' style='display: block;'>\n\r";
							break;
							
							default:
							echo "            <div class='edit_stock' style='display: none;'>\n\r";
							break;
						}
						echo "<p><strong class='wpsc_error'>" .__('Note: If you are using variations please ensure you populate the stock under "Variation Control"', 'wpsc')."</strong></p>";
						echo __('Stock Qty', 'wpsc') . " <input type='text' class='stock_limit_quantity' name='quantity' size='10' value='".$product_data['quantity']."' />";
						echo "<div style='font-size:9px; padding:5px;'><input type='checkbox' " . $unpublish_oos . " class='inform_when_oos' name='inform_when_oos' /> " . __('If this product runs out of stock set status to Unpublished & email site owner', 'wpsc') . "</div>";
						echo "              </div>\n\r";
					}
		} else {
						echo "
					<div style='display: none;' class='edit_stock'>
						"; 
						echo "<p><strong class='wpsc_error'>" .__('Note: If you are using variations please ensure you populate the stock under "Variation Control"', 'wpsc')."</strong></p>";
						echo __('Stock Qty', 'wpsc') . " <input type='text' name='quantity' value='0' size='10' />";
						echo "<div style='font-size:9px; padding:5px;'><input type='checkbox' class='inform_when_oos' name='inform_when_oos' /> " . __('If this product runs out of stock set status to Unpublished & email site owner', 'wpsc') . "</div>";
					echo "</div>";  
			}
	echo "
				
				</td>
			</tr>";
	echo "
		</table>
	</div>
</div>";

//return $output;

}

function wpsc_product_variation_forms($product_data=''){
	global $closed_postboxes, $variations_processor;
	$siteurl = get_option('siteurl');
	$output='';
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	?>
	
	<div id='wpsc_product_variation_forms' class='postbox <?php echo ((array_search('wpsc_product_variation_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '');	?>' <?php echo ((array_search('wpsc_product_variation_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : ''); ?>>
		<h3 class='hndle'><?php echo __('Variation Control', 'wpsc'); ?></h3>
		
		<div class='inside'>
			<strong><?php echo __('Add Variation Set', 'wpsc'); ?></strong>
			<h4 class='product_action_link'><a target='_blank' href='admin.php?page=wpsc-edit-variations'><?php echo __('+ Add New Variations', 'wpsc'); ?></a></h4>
			<br />
			
			<?php 
			if ($product_data['id'] > 0) { ?>
				<div id='edit_product_variations'>
					<?php echo $variations_processor->list_variations($product_data['id']); ?>
				</div>
				<div id='edit_variations_container'>
					<?php echo $variations_processor->variations_grid_view($product_data['id']); ?>
				</div>
			<?php } else { ?>
					<div id='add_product_variations'>
						<?php echo $variations_processor->list_variations($product_data['id']); ?>
					</div>
					<div id='edit_variations_container'>
					</div>
			<?php
			} ?>
		</div>
	</div>
	<?php 
}

function wpsc_product_shipping_forms($product_data=''){
	global $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div class='postbox ".((array_search('wpsc_product_shipping_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_shipping_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')." id='wpsc_product_shipping_forms'>";

    	if (IS_WP27) {
    		$output .= "<h3 class='hndle'>";
    	} else {
    		$output .= "<h3>
			<a class='togbox'>+</a>";
    	}
		$output .= __('Shipping Details', 'wpsc');
		$output .= "
		</h3>
      <div class='inside'>
  <table>

  	  <!--USPS shipping changes-->
	<tr>
		<td>
			".__('Weight', 'wpsc')."
		</td>
		<td>
			<input type='text' size='5' name='weight' value='".$product_data['weight']."' />
			<select name='weight_unit'>
				<option value='pound' ". (($product_data['weight_unit'] == 'pound') ? 'selected="selected"' : '') .">Pounds</option>
				<option value='ounce' ". ((preg_match("/o(u)?nce/",$product_data['weight_unit'])) ? 'selected="selected"' : '') .">Ounces</option>
				<option value='gram' ". (($product_data['weight_unit'] == 'gram') ? 'selected="selected"' : '') .">Grams</option>
				<option value='kilogram' ". (($product_data['weight_unit'] == 'kilogram') ? 'selected="selected"' : '') .">Kilograms</option>
			</select>
		</td>
    </tr>
      <!--dimension-->
    <tr>
		<td>
			".__('Height', 'wpsc')."
		</td>
		<td>
			<input type='text' size='5' name='productmeta_values[dimensions][height]' value='".$product_data['meta']['dimensions']['height']."'>
			<select name='productmeta_values[dimensions][height_unit]'>
				<option value='in' ". (($product_data['meta']['dimensions']['height_unit'] == 'in') ? 'selected' : '') .">inches</option>
				<option value='cm' ". (($product_data['meta']['dimensions']['height_unit'] == 'cm') ? 'selected' : '') .">cm</option>
				<option value='meter' ". (($product_data['meta']['dimensions']['height_unit'] == 'meter') ? 'selected' : '') .">meter</option>			</select>
			</td>
			</tr>
			<tr>
		<td>
			".__('Width', 'wpsc')."
		</td>
		<td>
		<input type='text' size='5' name='productmeta_values[dimensions][width]' value='".$product_data['meta']['dimensions']['width']."'>
		<select name='productmeta_values[dimensions][width_unit]'>
				<option value='in' ". (($product_data['meta']['dimensions']['width_unit'] == 'in') ? 'selected' : '') .">inches</option>
				<option value='cm' ". (($product_data['meta']['dimensions']['width_unit'] == 'cm') ? 'selected' : '') .">cm</option>
				<option value='meter' ". (($product_data['meta']['dimensions']['width_unit'] == 'meter') ? 'selected' : '') .">meter</option>
			</select>
			</td>
			</tr>
			<tr>
		<td>
			".__('Length', 'wpsc')."
		</td>
		<td>
			<input type='text' size='5' name='productmeta_values[dimensions][length]' value='".$product_data['meta']['dimensions']['length']."'>
			<select name='productmeta_values[dimensions][length_unit]'>
				<option value='in' ". (($product_data['meta']['dimensions']['length_unit'] == 'in') ? 'selected' : '') .">inches</option>
				<option value='cm' ". (($product_data['meta']['dimensions']['length_unit'] == 'cm') ? 'selected' : '') .">cm</option>
				<option value='meter' ". (($product_data['meta']['dimensions']['length_unit'] == 'meter') ? 'selected' : '') .">meter</option>

			</select>
			</td>
			</tr>

    <!--//dimension-->


    <!--USPS shipping changes ends-->


    <!--USPS shipping changes ends-->

 
    <tr>
      <td colspan='2'>
      <strong>".__('Flat Rate Settings', 'wpsc')."</strong> 
      </td>
    </tr>
    <tr>
      <td>
      ".__('Local Shipping Fee', 'wpsc')." 
      </td>
      <td>
        <input type='text' size='10' name='pnp' value='".$product_data['pnp']."' />
      </td>
    </tr>
  
    <tr>
      <td>
      ".__('International Shipping Fee', 'wpsc')."
      </td>
      <td>
        <input type='text' size='10' name='international_pnp' value='".$product_data['international_pnp']."' />
      </td>
    </tr>
    <tr>
   		<td>
   		<br />
          <input id='add_form_no_shipping' type='checkbox' name='no_shipping' value='yes' ".(($product_data['no_shipping'] == 1) ? 'checked="checked"' : '')."/>&nbsp;<label for='add_form_no_shipping'>".__('Disregard Shipping for this product', 'wpsc')."</label>
       </td>
    </tr>
    </table></div></div>";
    
    return $output;
}

function wpsc_product_advanced_forms($product_data='') {
	global $closed_postboxes,$wpdb;
	//exit('<pre>'.print_r($product_data, true).'</pre>');
	$merchant_note = $product_data['meta']['merchant_notes'];
	$engraved_text = $product_data['meta']['engrave'];
	$can_have_uploaded_image = $product_data['meta']['can_have_uploaded_image'];
	$external_link = $product_data['meta']['external_link'];
	$enable_comments = $product_data['meta']['enable_comments'];
	
	
	$output ='';
	
	$custom_fields =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` IN('{$product_data['id']}') AND `custom` IN('1') ",ARRAY_A);


	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div id='wpsc_product_advanced_forms' class='postbox ".((array_search('wpsc_product_advanced_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_advanced_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '').">";

		$output .= "<h3 class='hndle'>";
		$output .= __('Advanced Options', 'wpsc');
		$output .= "
	    </h3>
	    <div class='inside'>
	    <table>";
	$output .= "
	<tr>
		<td colspan='2' class='itemfirstcol'>
		  <strong>".__('Custom Meta', 'wpsc').":</strong><br />
			<a href='#' class='add_more_meta' onclick='return add_more_meta(this)'> + ".__('Add Custom Meta', 'wpsc')."</a><br /><br />
		";
		foreach((array)$custom_fields as $custom_field) {
			$i = $custom_field['id'];
			$custom_meta_value = stripslashes($custom_field['meta_value']);
			$output .= "
			<div class='product_custom_meta'  id='custom_meta_$i'>
				".__('Name', 'wpsc')."
				<input type='text' class='text' value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>
				
				".__('Value', 'wpsc')."
				<textarea class='text' name='custom_meta[$i][value]' id='custom_meta_value_$i'>$custom_meta_value</textarea>
				<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>".__('Delete')."</a>
				<br />
			</div>
			";
		}
		
		$output .= "<div class='product_custom_meta'>
		".__('Name', 'wpsc').": <br />
		<input type='text' name='new_custom_meta[name][]' value='' class='text'/><br />
		
		".__('Description', 'wpsc').": <br />
		<textarea name='new_custom_meta[value][]' cols='40' rows='10' class='text' ></textarea>
		<br /></div></td></tr>";
		
	    $output .= "<tr>
      <td class='itemfirstcol' colspan='2'><br /> <strong>". __('Merchant Notes', 'wpsc') .":</strong><br />
      
        <textarea cols='40' rows='3' name='productmeta_values[merchant_notes]' id='merchant_notes'>".stripslashes($merchant_note)."</textarea> 
      	<small>".__('These notes are only available here.', 'wpsc')."</small>
      </td>
    </tr>";
			/*   Insert Publish /No Publish Option on Product Edit (1bigidea)
			*/
		$output .= '
		<tr>
			<td colspan="2" class="itemfirstcol"><br />
						<strong>'.__("Publish").': </strong> <label for="publish_yes">'.__("Yes").'
				</label><input name="publish" id="publish_yes" type="radio" value="1" '.((!is_array($product_data) || $product_data['publish']) ? 'checked="checked" ' : '' ).' />
				<label for="publish_no">'.__("No").'
			</label><input name="publish" id="publish_no" type="radio" value="0" '.((is_array($product_data) && !$product_data['publish']) ? 'checked="checked" ' : '' ).' />
			</td>
		</tr>';
			/*   End Publish /No Publish Fields
			*/
		$output .="
		<tr>
      <td class='itemfirstcol' colspan='2'><br />
       <strong>". __('Personalisation Options', 'wpsc') .":</strong><br />
        <input type='hidden' name='productmeta_values[engraved]' value='0' />
        <input type='checkbox' name='productmeta_values[engraved]' ".(($engraved_text == 'on') ? 'checked="checked"' : '')." id='add_engrave_text' />
        <label for='add_engrave_text'> ".__('Users can personalize this product by leaving a message on single product page', 'wpsc')."</label>
        <br />
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='hidden' name='productmeta_values[can_have_uploaded_image]' value='0' />
        <input type='checkbox' name='productmeta_values[can_have_uploaded_image]' ".(($can_have_uploaded_image == 'on') ? 'checked="checked"' : '')." id='can_have_uploaded_image' />
        <label for='can_have_uploaded_image'> ".__('Users can upload images on single product page to purchase logs.', 'wpsc')."</label>
        <br />
      </td>
    </tr>";
	
    
    if(get_option('payment_gateway') == 'google') {
		$output .= "<tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='checkbox' name='productmeta_values[google_prohibited]' id='add_google_prohibited' /> <label for='add_google_prohibited'>
       ".__('Prohibited', 'wpsc')."
	 <a href='http://checkout.google.com/support/sell/bin/answer.py?answer=75724'>by Google?</a></label><br />
      </td>
    </tr>";
    }

  	ob_start();
  	do_action('wpsc_add_advanced_options', $product_data['id']);
  	$output .= ob_get_contents();
  	ob_end_clean();
	
	$output .= "
	<tr>
      <td class='itemfirstcol' colspan='2'><br />
       <strong>".__('Off Site Product Link', 'wpsc').":</strong><br />
       <small>".__('If this product is for sale on another website enter the link here. For instance if your product is an MP3 file for sale on itunes you could put the link here. This option over rides the buy now and add to cart links and takes you to the site linked here.', 'wpsc')."</small><br /><br />
		<label for='external_link'>".__('External Link', 'wpsc')."</label>:<br />
		  <input type='text' class='text' name='productmeta_values[external_link]' value='".$external_link."' id='external_link' size='40' /> 
      </td>
    </tr>";
	if (get_option('wpsc_enable_comments') == 1) {
		$output .= "
		<tr>
			<td class='itemfirstcol' colspan='2'><br />
				<strong>".__('Enable IntenseDebate Comments', 'wpsc').":</strong><br />
			<select name='productmeta_values[enable_comments]'>
				<option value='' ". (($enable_comments == '') ? 'selected' : '') .">Use Default</option>
				<option value='yes' ". (($enable_comments == 'yes') ? 'selected' : '') .">Yes</option>
				<option value='no' ". (($enable_comments == 'no') ? 'selected' : '') .">No</option>
			</select>
			<br/>".__('Allow users to comment on this product.', 'wpsc')."
			</td>
		</tr>";
	}
	$output .= "
    </table></div></div>";
	return $output;
}

function wpsc_product_image_forms($product_data='') {
	global $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}

	 	//echo "<pre>".print_r($product_data,true)."</pre>";

	//As in WordPress,  If Mac and mod_security, no Flash
	$flash = true;
	if ( (false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac')) && apache_mod_loaded('mod_security') ) {
		$flash = false;
	}
	
	$flash_action_url = admin_url('async-upload.php');
	$flash = apply_filters('flash_uploader', $flash);
	?>
	<div id='wpsc_product_image_forms' class='postbox <?php echo ((array_search('wpsc_product_image_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : ''); ?>' <?php echo ((array_search('wpsc_product_image_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : ''); ?> >
		<h3 class='hndle'> <?php echo	__('Product Images', 'wpsc'); ?></h3>
		<div class='inside'>
		
		<?php if ( $flash ) : ?>
			<script type="text/javascript" >
			/* <![CDATA[ */
			jQuery("span#spanButtonPlaceholder").livequery(function() {
				swfu = new SWFUpload({
					button_text: '<span class="button"><?php _e('Select Files'); ?></span>',
					button_text_style: '.button { text-align: center; font-weight: bold; font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana,sans-serif; }',
					button_height: "24",
					button_width: "132",
					button_image_url: '<?php echo includes_url('images/upload.png'); ?>',
					button_placeholder_id: "spanButtonPlaceholder",
					upload_url : "<?php echo attribute_escape( $flash_action_url ); ?>",
					flash_url : "<?php echo includes_url('js/swfupload/swfupload.swf'); ?>",
					file_post_name: "async-upload",
					file_types: "<?php echo apply_filters('upload_file_glob', '*.*'); ?>",
					post_params : {
						"product_id" : parseInt(jQuery('#product_id').val()),
						"auth_cookie" : "<?php if ( is_ssl() ) echo $_COOKIE[SECURE_AUTH_COOKIE]; else echo $_COOKIE[AUTH_COOKIE]; ?>",
						"_wpnonce" : "<?php echo wp_create_nonce('product-swfupload'); ?>",
						"wpsc_admin_action" : "wpsc_add_image"
					},
					file_size_limit : "<?php echo wp_max_upload_size(); ?>b",
					file_dialog_start_handler : wpsc_fileDialogStart,
					file_queued_handler : wpsc_fileQueued,
					upload_start_handler : wpsc_uploadStart,
					upload_progress_handler : wpsc_uploadProgress,
					upload_error_handler : wpsc_uploadError,
					upload_success_handler : wpsc_uploadSuccess,
					upload_complete_handler : wpsc_uploadComplete,
					file_queue_error_handler : wpsc_fileQueueError,
					file_dialog_complete_handler : wpsc_fileDialogComplete,
					swfupload_pre_load_handler: wpsc_swfuploadPreLoad,
					swfupload_load_failed_handler: wpsc_swfuploadLoadFailed,
					custom_settings : {
						degraded_element_id : "browser-image-uploader", // id of the element displayed when swfupload is unavailable
						swfupload_element_id : "flash-image-uploader" // id of the element displayed when swfupload is available
					},
					<?php
					if(defined('WPSC_ADD_DEBUG_PAGE') && (constant('WPSC_ADD_DEBUG_PAGE') == true)) {
						?>
						debug: true
						<?php
					} else {
						?>
						debug: false
						<?php
					}
					?>
				});
			});
		/* ]]> */
		</script>
		
		<?php endif; ?>
		
    <div class='flash-image-uploader'>
			<span id='spanButtonPlaceholder'></span><br />
				<div id='media-items'> </div>
				<p><?php echo wpsc_check_memory_limit(); ?></p>
				<p><?php echo __('You are using the Flash uploader.  Problems?  Try the <a class="wpsc_upload_switcher" onclick=\'wpsc_upload_switcher("browser")\'>Browser uploader</a> instead.', 'wpsc'); ?></p>
				<?php
				if(! function_exists('gold_shpcrt_display_gallery') ) {
					?>
					<p><?php _e('To upload multiple product thumbnails you must <a href="http://www.instinct.co.nz/shop/">install the premium upgrade</a>'); ?></p>
					<?php
				}
				?>
    </div>
    
    
		
		  <div class='browser-image-uploader'>
				<h4><?php _e("Select an image to upload:"); ?></h4>
				<ul>  
					<li>
						<input type="file" value="" name="image" />
						<input type="hidden" value="1" name="image_resize" />
					</li>
					<li>
						<?php echo wpsc_check_memory_limit(); ?>
					</li>
				</ul>
				<p><?php echo __('You are using the Browser uploader.  Problems?  Try the <a class="wpsc_upload_switcher" onclick=\'wpsc_upload_switcher("flash")\'>Flash uploader</a> instead.', 'wpsc'); ?></p>
				<br />
				
			</div>
			<p><strong <?php echo $display; ?>><?php echo __('Manage your thumbnails', 'wpsc');?></strong></p>
			<?php
			edit_multiple_image_gallery($product_data);
			?>

		</div>
		<div style='clear:both'></div>
	</div>
	<?php
  return $output;
}

function wpsc_product_download_forms($product_data='') {
	global $wpdb, $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}

	$output ='';
 	$upload_max = wpsc_get_max_upload_size();
 	$output .= "<div id='wpsc_product_download_forms' class='postbox ".((array_search('wpsc_product_download_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_download_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '').">";
 	
	$output .= "<h3 class='hndle'>".__('Product Download', 'wpsc')."</h3>";
	$output .= "<div class='inside'>";
	
	$output .= "<h4>".__('Upload File', 'wpsc').":</h4>";
	$output .= "<input type='file' name='file' value='' /><br />".__('Max Upload Size', 'wpsc')." : <span>".$upload_max."</span><br /><br />";
	$output .= wpsc_select_product_file($product_data['id'])."<br />";
    
	if($product_data['file'] > 0) {
    	$output .= __('Preview File', 'wpsc').": ";
    	
    	$output .= "<a class='admin_download' href='index.php?admin_preview=true&product_id=".$product_data['id']."' ><img align='absmiddle' src='".WPSC_URL."/images/download.gif' alt='' title='' /><span>".__('Click to download', 'wpsc')."</span></a>";
		
    	$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data['file']."' LIMIT 1",ARRAY_A);
    	if(($file_data != null) && (function_exists('listen_button'))) {
    	  $output .= "".listen_button($file_data['idhash'], $file_data['id'])."<br style='clear: both;' /><br />";
    	}
    }
	if(function_exists("make_mp3_preview") || function_exists("wpsc_media_player")) {    
    $output .="<h4>".__("Select an MP3 file to upload as a preview")."</h4>";
	
		$output .= "<input type='file' name='preview_file' value='' /><br />";
		$output .= "<br />";
	}
	$output .="</div></div>";
	return $output;
}

function wpsc_product_label_forms() {
	global $closed_postboxes;
	?>
	<div id='wpsc_product_label_forms' class='postbox <?php echo ((array_search('wpsc_product_label_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : ''); ?>'>
		<?php
    	if (function_exists('add_object_page')) {
    		echo "<h3 class='hndle'>";
    	} else {
    		echo "<h3>
		<a class='togbox'>+</a>";
    	}
    ?> 
		<?php echo __('Label Control', 'wpsc'); ?>
	</h3>
	<div class='inside'>
    <table>
    <tr>
      <td colspan='2'>
        <?php echo __('Add Label', 'wpsc'); ?> :
      	<a id='add_label'><?php echo __('Add Label', 'wpsc'); ?></a>
      </td>
    </tr> 
    <tr>
      <td colspan='2'>
      <div id="labels">
        <table>
        	<tr>
        		<td><?=__('Label', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[labels][]"></td>
        	</tr>
        	<tr>
        		<td><?=__('Label Description', 'wpsc')?> :</td>
        		<td><textarea name="productmeta_values[labels_desc][]"></textarea></td>
        	</tr>
        	<tr>
        		<td><?=__('Life Number', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[life_number][]"></td>
        	</tr>
        	<tr>
        		<td><?=__('Item Number', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[item_number][]"></td>
        	</tr>
        	<tr>
        		<td><?=__('Product Code', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[product_code][]"></td>
        	</tr>
        	<tr>
        		<td><?=__('PDF', 'wpsc')?> :</td>
        		<td><input type="file" name="pdf[]"></td>
        	</tr>
        </table>
        </div>
      </td>
    </tr> 
	</table></div></div>
	<?php
}


function edit_multiple_image_gallery($product_data) {
	global $wpdb;
	$siteurl = get_option('siteurl');
	if($product_data['id'] > 0) {
		$main_image = $wpdb->get_row("SELECT `images`.* FROM `".WPSC_TABLE_PRODUCT_IMAGES."` AS `images` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `product` ON `product`.`image` = `images`.`id`  WHERE `product`.`id` = '{$product_data['id']}' LIMIT 1", ARRAY_A);
	}
	$timestamp = time();
	?>
	<ul id="gallery_list" class="ui-sortable" style="position: relative;">
		<li class='first gallery_image' id='product_image_<?php echo $main_image['id']; ?>'>
			<input type='hidden' name='gallery_product_id[]' class='image-id' value='<?php echo $main_image['id']; ?>' />
			<div class='previewimage' id='gallery_image_<?php echo $main_image['id']; ?>'>
				<?php if ($main_image['image'] != '') { ?>
					<?php
					$image_data = getimagesize(WPSC_THUMBNAIL_DIR.$main_image['image']);
					?>
					<a id='extra_preview_link_0' href=''  title='' rel='product_extra_image_0'  >
					  <img class='previewimage' onclick='return false;' src='<?php echo WPSC_THUMBNAIL_URL.$main_image['image']; ?>' alt='<?php echo __('Preview', 'wpsc'); ?>' title='<?php echo __('Preview', 'wpsc'); ?>' />
					</a>
				<?php } ?>
				<?php
				echo wpsc_main_product_image_menu($product_data['id']);
				?>
			</div>
		</li>
	<?php
	$num = 0;
	if(function_exists('gold_shpcrt_display_gallery') && ($product_data['id'] > 0)) {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '{$product_data['id']}' AND `id` NOT IN ('{$main_image['id']}') ORDER BY image_order ASC",ARRAY_A);
    
    //echo "<pre>".print_r($values,true)."</pre>";
    
    if($values != null) {
      foreach($values as $image) {
        if(function_exists("getimagesize")) {
          if($image['image'] != '') {
            $num++;
            $imagepath = WPSC_IMAGE_DIR . $image['image'];
						$image_data = @getimagesize(WPSC_IMAGE_DIR.$image['image']);
            ?>
            <li id="product_image_<?php echo $image['id']; ?>" class='gallery_image'>
							<input type='hidden' class='image-id'  name='gallery_image_id[]' value='<?php echo $image['id']; ?>' />
							<div class='previewimage' id='gallery_image_<?php echo $image['id']; ?>'>
							  <a id='extra_preview_link_<?php echo $image['id']; ?>' onclick='return false;' href='' rel='product_extra_image_<?php echo $image['id']; ?>' >
							    <img class='previewimage' src='<?php echo WPSC_IMAGE_URL.$image['image']; ?>' alt='<?php echo __('Preview', 'wpsc'); ?>' title='<?php echo __('Preview', 'wpsc'); ?>' />
							  </a>
								<img alt='-' class='deleteButton' src='<?php echo WPSC_URL; ?>/images/cross.png' />
							</div>
            </li>
            
					<?php
          }
        }
      }
    }
  }
  ?>
	</ul>
	<?php
  //return $output;
}


function wpsc_main_product_image_menu($product_id) {
  global $wpdb;
  $thumbnail_state = 0;
	if($product_id > 0) {
		$main_image = $wpdb->get_row("SELECT `images`.*,  `product`.`thumbnail_state` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` AS `images` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `product` ON `product`.`image` = `images`.`id`  WHERE `product`.`id` = '{$product_id}' LIMIT 1", ARRAY_A);
		$thumbnail_state = $main_image['thumbnail_state'];
	} else {
		$thumbnail_state = 1;
	}
	$sendback = wp_get_referer();
	$presentation_link = add_query_arg('page','wpsc-settings', $sendback);
	$presentation_link = add_query_arg('tab','presentation#thumb_settings', $presentation_link);
	$thumbnail_image_height = get_product_meta($product_id, 'thumbnail_height');
	$thumbnail_image_width = get_product_meta($product_id, 'thumbnail_width');


	
// 	echo $thumbnail_image_height;
// 	echo "|";
// 	echo $thumbnail_image_width;
	ob_start();
	?>
	<div class='image_settings_box'>
		<div class='upper_settings_box'>
			<div class='upper_image'><img src='<?php echo WPSC_URL; ?>/images/pencil.png' alt='' /></div>
			<div class='upper_txt'><?php _e('Thumbnail Settings'); ?><a class='closeimagesettings'>X</a></div>
		</div>

		<div class='lower_settings_box'>
			<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='S' />
			<ul>		

				<li>
					<input type='radio' name='gallery_resize' value='1' id='gallery_resize1' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize1'><?php echo __('use default size', 'wpsc'); ?>&nbsp;(<a href='<?php echo $presentation_link; ?>' title='<?php echo __('This is set on the Settings Page', 'wpsc'); ?>'><?php echo get_option('product_image_height'); ?>&times;<?php echo get_option('product_image_width'); ?>px</a>)
					</label>

				</li>
				
				<li>
					<input type='radio' <?php echo (($thumbnail_state != 2) ? "checked='checked'" : "") ;?> name='gallery_resize' value='0' id='gallery_resize0' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize0'> <?php echo __('do not resize thumbnail image', 'wpsc'); ?></label><br />
				</li>
				
				<li>
					<input type='radio' <?php echo (($thumbnail_state == 2) ? "checked='checked'" : "") ;?>  name='gallery_resize' value='2' id='gallery_resize2' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize2'><?php echo __('use specific size', 'wpsc'); ?> </label>
					<div class='heightWidth image_resize_extra_forms' <?php echo (($thumbnail_state == 2) ? "style='display: block;'" : "") ;?>>
						<input id='gallery_image_width' type='text' size='4' name='gallery_width' value='<?php echo $thumbnail_image_width; ?>' /><label for='gallery_image_width'><?php echo __('px width', 'wpsc'); ?></label>
						<input id='gallery_image_height' type='text' size='4' name='gallery_height' value='<?php echo $thumbnail_image_height; ?>' /><label for='gallery_image_height'><?php echo __('px height', 'wpsc'); ?> </label>
					</div>
				</li>

				<li>
					<input type='radio'  name='gallery_resize' value='3' id='gallery_resize3' class='image_resize'  onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize3'> <?php echo __('use separate thumbnail', 'wpsc'); ?></label><br />
					<div class='browseThumb image_resize_extra_forms'>
						<input type='file' name='gallery_thumbnailImage' size='15' value='' />
					</div>
				</li>
				<li>
				<a href='<?php echo htmlentities("admin.php?wpsc_admin_action=crop_image&imagename=".$main_image['image']."&imgheight=".$image_data[1]."&imgwidth=".$image_data[0]."&width=630&height=500&product_id=".$product_id); ?>' title='Crop Image' class='thickbox'>Crop This Image Using jCrop</a>

				</li>
				
				<li>
					<input type='submit' class='button-primary closeimagesettings' onclick='return false;' value='finish' />
					<a href='#' class='delete_primary_image delete_button'>Delete</a>
				</li>
				

			</ul>
		</div>
	</div>
	<a class='editButton'>Edit   <img src='<?php echo WPSC_URL; ?>/images/pencil.png' alt='' /></a>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

  /**
	* Displays the category forms for adding and editing products
	* Recurses to generate the branched view for subcategories
	*/
function wpsc_category_list($group_id, $product_id = '', $unique_id = '', $category_id = null, $iteration = 0, $orderby = 'id' ) {
  global $wpdb;
  if(is_numeric($category_id)) {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('$group_id') AND  `active`='1' AND `category_parent` = '$category_id'  ORDER BY `$orderby` ASC",ARRAY_A);
  } else {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('$group_id') AND  `active`='1' AND `category_parent` = '0'  ORDER BY `$orderby` ASC",ARRAY_A);
	}
	
	if($category_id < 1) {
		$output .= "<ul class='list:category categorychecklist form-no-clear'>\n\r";
	} elseif((count($values) >0) ){
		$output .= "<ul class='children'>\n\r";
	}
		
		
  foreach((array)$values as $option) {
    if(is_numeric($product_id) && ($product_id > 0)) {
      $category_assoc = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` IN('".$product_id."') AND `category_id` IN('".$option['id']."')  LIMIT 1",ARRAY_A); 
      //echo "<pre>".print_r($category_assoc,true)."</pre>";
      if(is_numeric($category_assoc['id']) && ($category_assoc['id'] > 0)) {
        $selected = "checked='checked'";
			}
		}
		
		$output .= "  <li id='category-".$option['id']."'>\n\r";
    $output .= "    <label class='selectit'><input  id='".$unique_id."category_form_".$option['id']."' type='checkbox' $selected name='category[]' value='".$option['id']."' /></label><label for='".$unique_id."category_form_".$option['id']."' class='greytext' >".stripslashes($option['name'])."</label>";
    $output .= wpsc_category_list($group_id, $product_id, $unique_id, $option['id'], $iteration+1);
    
		$output .= "  </li>\n\r";
    
    $selected = "";
	}
	if((count($values) >0) ){
		$output .= "</ul>\n\r";
	}
  return $output;
}

/**
 * Slightly modified copy of the Wordpress the_editor function
 *
 *  We have to use a modified version because the wordpress one calls javascript that uses document.write
 *  When this javascript runs after being loaded through AJAX, it replaces the whole page.
 *
 * The amount of rows the text area will have for the content has to be between
 * 3 and 100 or will default at 12. There is only one option used for all users,
 * named 'default_post_edit_rows'.
 *
 * If the user can not use the rich editor (TinyMCE), then the switch button
 * will not be displayed.
 *
 * @since 3.7
 *
 * @param string $content Textarea content.
 * @param string $id HTML ID attribute value.
 * @param string $prev_id HTML ID name for switching back and forth between visual editors.
 * @param bool $media_buttons Optional, default is true. Whether to display media buttons.
 * @param int $tab_index Optional, default is 2. Tabindex for textarea element.
 */
function wpsc_the_editor($content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2) {
	$rows = get_option('default_post_edit_rows');
	if (($rows < 3) || ($rows > 100))
		$rows = 12;

	if ( !current_user_can( 'upload_files' ) )
		$media_buttons = false;

	$richedit =  user_can_richedit();
	$class = '';

	if ( $richedit || $media_buttons ) { ?>
	<div id="editor-toolbar">
<?php
	if ( $richedit ) {
		$wp_default_editor = wp_default_editor(); ?>
		<div class="zerosize"><input accesskey="e" type="button" onclick="switchEditors.go('<?php echo $id; ?>')" /></div>
<?php	if ( 'html' == $wp_default_editor ) {
			add_filter('the_editor_content', 'wp_htmledit_pre'); ?>
			<a id="edButtonHTML" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e('HTML'); ?></a>
			<a id="edButtonPreview" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e('Visual'); ?></a>
<?php	} else {
			$class = " class='theEditor'";
			add_filter('the_editor_content', 'wp_richedit_pre'); ?>
			<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e('HTML'); ?></a>
			<a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e('Visual'); ?></a>
<?php	}
	}

	if ( $media_buttons ) { ?>
		<div id="media-buttons" class="hide-if-no-js">
<?php	do_action( 'media_buttons' ); ?>
		</div>
<?php
	} ?>
	</div>
<?php
	}
?>
	<div id="quicktags"><?php
	wp_print_scripts( 'quicktags' ); ?>
	  <div id="ed_toolbar">
		</div>
		<script type="text/javascript">wpsc_edToolbar()</script>

	</div>

<?php
	$the_editor = apply_filters('the_editor', "<div id='editorcontainer'><textarea rows='$rows'$class cols='40' name='$id' tabindex='$tab_index' id='$id'>%s</textarea></div>\n");
	$the_editor_content = apply_filters('the_editor_content', $content);

	printf($the_editor, $the_editor_content);

?>
	<script type="text/javascript">
	edCanvas = document.getElementById('<?php echo $id; ?>');
	</script>
<?php
}

?>
