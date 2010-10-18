<?php
function wpsc_options_checkout(){
global $wpdb;
$form_types = Array("Text" => "text",
	"Email Address" => "email",
	"Street Address" => "address",
	"City" => "city",
	"Country" => "country",
	"Delivery Address" => "delivery_address",
	"Delivery City" => "delivery_city",
	"Delivery Country" => "delivery_country",
	"Text Area" => "textarea",
	"Heading" => "heading",
	"Select" => "select",
	"Radio Button" => "radio",
	"Checkbox" => "checkbox"
);

$unique_names = Array('billingfirstname','billinglastname','billingaddress','billingcity','billingcountry','billingemail','billingphone','billingpostcode','delivertoafriend','shippingfirstname','shippinglastname','shippingaddress','shippingcity','shippingstate','shippingcountry','shippingpostcode');

update_option('wpsc_checkout_form_fields', $form_types);
if(get_option('wpsc_checkout_form_fields') == ''){
	update_option('wpsc_checkout_form_fields', $form_types);
}
do_action('wpsc_checkout_form_fields_page');
$columns = array(
	'drag' => 'Drag',
	'name' => 'Name',
	//'alt_name' => 'Alternate Name',
	'type' => 'Type',
	'unique_names' => 'Unique Names',
	'mandatory' => 'Mandatory',
	'trash' => 'Trash',
);
register_column_headers('display-checkout-list', $columns);	
$form_types = get_option('wpsc_checkout_form_fields');

?>

<form name='cart_options' id='cart_options' method='post' action=''>
	<div class="wrap">
  		<h2><?php echo __('Checkout Options', 'wpsc');?></h2>  
		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>

		<form method='post' action='' id='chekcout_options_tbl'>
		<div class='metabox-holder' style='width:95%;'>
			<div class='postbox'>
			<input type='hidden' name='checkout_submits' value='true' />
			<h3 class='hndle'>Misc Checkout Options</h3>
			<div class='inside'>
			<table>
			<tr>
				<td><?php echo __('Users must register before checking out', 'wpsc'); ?>:</td>
				<td>
					<?php
						$require_register = get_option('require_register');
						$require_register1 = "";
						$require_register2 = "";
						switch($require_register) {
							case 0:
							$require_register2 = "checked ='checked'";
							break;
    			
							case 1:
							$require_register1 = "checked ='checked'";
							break;
						}
		        ?>
						<input type='radio' value='1' name='wpsc_options[require_register]' id='require_register1' <?php echo $require_register1; ?> /> 					<label for='require_register1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
						<input type='radio' value='0' name='wpsc_options[require_register]' id='require_register2' <?php echo $require_register2; ?> /> 					<label for='require_register2'><?php echo __('No', 'wpsc');?></label>
					</td>
					<td>
						<a title='<?php echo __('If yes then you must also turn on the wordpress option "Any one can register"', 'wpsc');?>' class='flag_email' href='#' ><img src='<?php echo WPSC_URL; ?>/images/help.png' alt='' /> </a>
					</td>
     		</tr>
     				<tr>
			<?php
				$lock_tax = get_option('lock_tax');

				switch($lock_tax) {
					case 1:
					$lock_tax1 = "checked ='checked'";
					break;
					
					case 0:
					$lock_tax2 = "checked ='checked'";
					break;
				}
			?>
			<td scope="row"><?php echo __('Lock Tax to Billing Country', 'wpsc'); ?>:</td>
			<td>
				<input type='radio' value='1' name='wpsc_options[lock_tax]' id='lock_tax1' <?php echo $lock_tax1; ?> /> 
				<label for='lock_tax1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[lock_tax]' id='lock_tax2' <?php echo $lock_tax2; ?> /> 
				<label for='lock_tax2'><?php echo __('No', 'wpsc');?></label>
			</td>
			</tr>
		<tr>
			<!-- Disregard Billing State for Tax Calculations -->
			<?php
				$lock_tax_to_shipping = get_option('lock_tax_to_shipping');

				switch($lock_tax_to_shipping) {
					case 1:
					$lock_tax_to_shipping1 = "checked ='checked'";
					break;
					
					case 0:
					$lock_tax_to_shipping2 = "checked ='checked'";
					break;
				}
			?>
			<td scope="row"><?php echo __(' Disregard Billing State for Tax Calculations', 'wpsc'); ?>:</td>
			<td>
				<input type='radio' value='1' name='wpsc_options[lock_tax_to_shipping]' id='lock_tax1' <?php echo $lock_tax_to_shipping1; ?> /> 
				<label for='lock_tax_to_shipping1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[lock_tax_to_shipping]' id='lock_tax2' <?php echo $lock_tax_to_shipping2; ?> /> 
				<label for='lock_tax_to_shipping2'><?php echo __('No', 'wpsc');?></label>
			</td>


			
		</tr>
		<tr>
					<?php
				$shippingBilling = get_option('shippingsameasbilling');

				switch($shippingBilling) {
					case 1:
					$shippingBilling1 = "checked ='checked'";
					break;
					
					case 0:
					$shippingBilling2 = "checked ='checked'";
					break;
				}
			?>
			<td scope="row"><?php echo __('Enable Shipping Same as Billing Option: ', 'wpsc'); ?>:</td>
			<td>
			<input type='radio' value='1' name='wpsc_options[shippingsameasbilling]' id='shippingsameasbilling1' <?php echo $shippingBilling1; ?> /> 
			<label for='shippingsameasbilling1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
			<input type='radio' value='0' name='wpsc_options[shippingsameasbilling]' id='shippingsameasbilling2' <?php echo $shippingBilling2; ?> /> 
			<label for='shippingsameasbilling2'><?php echo __('No', 'wpsc');?></label>
			</td>
		</tr>
		
		<tr>
			<?php
				$billing_unless_shipping_setting = get_option('use_billing_unless_is_shipping');

				switch($billing_unless_shipping_setting) {
					case 1:
					$billing_unless_shipping['on'] = "checked ='checked'";
					break;
					
					case 0:
					$billing_unless_shipping['off'] = "checked ='checked'";
					break;
				}
			?>
			<td scope="row"><?php echo __('Use The Billing country for Shipping unless a shipping form is present: ', 'wpsc'); ?>:</td>
			<td>
			<input type='radio' value='1' name='wpsc_options[use_billing_unless_is_shipping]' id='use_billing_unless_is_shipping1' <?php echo $billing_unless_shipping['on']; ?> /> 
			<label for='use_billing_unless_is_shipping1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
			<input type='radio' value='0' name='wpsc_options[use_billing_unless_is_shipping]' id='use_billing_unless_is_shipping2' <?php echo $billing_unless_shipping['off']; ?> /> 
			<label for='use_billing_unless_is_shipping2'><?php echo __('No', 'wpsc');?></label>
			</td>
		</tr>
		
			</table>
		</div>
		</div>
		</div>
			<h3>Form Fields</h3>
  			<p><?php echo __('Here you can customise the forms to be displayed in your checkout page. The checkout page is where you collect important user information that will show up in your purchase logs i.e. the buyers address, and name...', 'wpsc');?></p>
  			
				<p>
					<label for='wpsc_form_set'>Select a Form Set:</label>
					<select id='wpsc_form_set' name='wpsc_form_set'>
					<?php
						$checkout_sets = get_option('wpsc_checkout_form_sets');
						foreach((array)$checkout_sets as $key => $value) {
							$selected_state = "";
							if($_GET['checkout-set'] == $key) {
								$selected_state = "selected='selected'";
							}
							echo "<option {$selected_state} value='{$key}'>".stripslashes($value)."</option>";
						}
					?>
					</select>
					<input type='submit' value='Filter' name='wpsc_checkout_set_filter' class='button-secondary' />
					<a href='#' class='add_new_form_set'><?php _e("+ Add New Form Set", 'wpsc'); ?></a>
				</p>
				
				<p class='add_new_form_set_forms'>
					<label><?php _e("Add new Form Set",'wpsc'); ?>: <input type="text" value="" name="new_form_set" /></label>
					<input type="submit" value="<?php _e('Add'); ?>" class="button-secondary" id="formset-add-sumbit"/>
				</p>
				
				<?php
				if(!isset($_GET['checkout-set'])) {
					$filter = 0;
					$form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `checkout_set` IN ('".$filter."') ORDER BY `order`;";
				} else {
					$filter = $wpdb->escape($_GET['checkout-set']);
					$form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `checkout_set` IN ('".$filter."') ORDER BY `order`;";
				}
				$email_form_field = $wpdb->get_row("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
			  
  		 		
			  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
				$selected_checkout_set = $_GET['checkout-set'];
  			echo "<input type='hidden' name='selected_form_set' value='".$selected_checkout_set."' />";
  			?>
			<table id="wpsc_checkout_list" class="widefat page fixed"  cellspacing="0">
			<thead>
				<tr>
					<?php print_column_headers('display-checkout-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
					<?php print_column_headers('display-checkout-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody id='wpsc_checkout_list_body'>
			<?php
				foreach((array)$form_data as $form_field) {
			    echo "<tr id='checkout_".$form_field['id']."' class='checkout_form_field'>\n\r";
			    echo '<td class="drag"><a href="" onclick="return false;" title="Click and Drag to Order Checkout Fields"><img src="'.WPSC_URL.'/images/roll-over-drag.jpg" alt="roll-over-drag" /></a></td>';
			    echo "<td class='namecol'><input type='text' name='form_name[".$form_field['id']."]' value='".htmlentities(stripslashes($form_field['name']), ENT_QUOTES, "UTF-8")."' /></td>";
			    echo "      <td class='typecol'>";
			    echo "<select class='wpsc_checkout_selectboxes' name='form_type[".$form_field['id']."]'>";
			    foreach($form_types as $form_type_name => $form_type) {
			      $selected = '';
			      if($form_type === $form_field['type']) {
			        $selected = "selected='selected'";
			      }
			       // define('__('Textarea', 'wpsc')', 'Textarea');
			      echo "<option value='".$form_type."' ".$selected.">".__($form_type_name, 'wpsc')."</option>";
			    }
			 
			    echo "</select>";
			   if(in_array($form_field['type'], array('select','radio','checkbox'))){
			    	   echo "<a class='wpsc_edit_checkout_options' rel='form_options[".$form_field['id']."]' href=''>more options</a>";			   
			    }
			    echo "</td>";
			    $checked = "";
			    echo "<td><select name='unique_names[".$form_field['id']."]'>";
			    echo "<option value='-1'>Select a Unique Name</option>";
			    foreach($unique_names as $unique_name){
			       $selected = "";
			      if($unique_name == $form_field['unique_name']) {
			        $selected = "selected='selected'";
			      }
			    	echo "<option ".$selected." value='".$unique_name."'>".$unique_name."</option>";
			    }
			    echo "</select></td>";
			    if($form_field['mandatory']) {
			      $checked = "checked='checked'";
			    }
			    echo "      <td class='mandatorycol'><input $checked type='checkbox' name='form_mandatory[".$form_field['id']."]' value='1' /></td>";
			   
			    
			    echo "      <td><a class='image_link' href='#' onclick='return remove_form_field(\"checkout_".$form_field['id']."\",".$form_field['id'].");'><img src='".WPSC_URL."/images/trash.gif' alt='".__('Delete', 'wpsc')."' title='".__('Delete', 'wpsc')."' /></a>";
		   
			    if($email_form_field['id'] == $form_field['id']) {
			      echo "<a title='".__('This will be the Email address that the Purchase Reciept is sent to.', 'wpsc')."' class='flag_email' href='#' ><img src='".WPSC_URL."/images/help.png' alt='' /> </a>";
			    }
			    echo "</td>";
			    
			    echo "
			    </tr>";
			 
			    }
			    ?>

			</tbody>
			</table>
		 <?php ?>
	<p>
        <input type='hidden' name='wpsc_admin_action' value='checkout_settings' />
        
				<?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
        <input class='button-primary' type='submit' name='submit' value='<?php echo __('Save Changes', 'wpsc');?>' />
        <a href='#' onclick='return add_form_field();'><?php echo __('Add New Form Field', 'wpsc');?></a>
  </p>
  </form>
</div>
</form>
		   <?php
  }
  ?>