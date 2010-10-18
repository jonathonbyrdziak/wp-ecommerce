<?php
function wpsc_options_admin(){
global $wpdb;
?>
<form name='cart_options' id='cart_options' method='post' action=''>
	<div id="options_admin">
	  <h2><?php echo __('Admin Settings', 'wpsc'); ?></h2>
  		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
			<table class='wpsc_options form-table'>            
				<tr>
					<th scope="row"><?php echo __('Max downloads per file', 'wpsc');?>:	</th>
					<td>
						<input type='text' size='10' value='<?php echo get_option('max_downloads'); ?>' name='wpsc_options[max_downloads]' />
					</td>
				</tr>				
				<?php
				$wpsc_ip_lock_downloads1 = "";
				$wpsc_ip_lock_downloads2 = "";
				switch(get_option('wpsc_ip_lock_downloads')) {    
					case 1:
					$wpsc_ip_lock_downloads1 = "checked ='checked'";
					break;
							
					case 0:
					default:
					$wpsc_ip_lock_downloads2 = "checked ='checked'";
					break;
				}
	
				?>
				<tr>
					<th scope="row">
					<?php echo __('Lock downloads to IP address', 'wpsc');?>:
					</th>
					<td>
						<input type='radio' value='1' name='wpsc_options[wpsc_ip_lock_downloads]' id='wpsc_ip_lock_downloads2' <?php echo $wpsc_ip_lock_downloads1; ?> /> <label for='wpsc_ip_lock_downloads2'><?php echo __('Yes', 'wpsc');?></label>&nbsp;
						<input type='radio' value='0' name='wpsc_options[wpsc_ip_lock_downloads]' id='wpsc_ip_lock_downloads1' <?php echo $wpsc_ip_lock_downloads2; ?> /> <label for='wpsc_ip_lock_downloads1'><?php echo __('No', 'wpsc');?></label><br />
					</td>
				</tr>

				
				<?php
				$wpsc_check_mime_types1 = "";
				$wpsc_check_mime_types2 = "";
				switch(get_option('wpsc_check_mime_types')) {
					case 1:
					$wpsc_check_mime_types2 = "checked ='checked'";
					break;
							
					case 0:
					default:
					$wpsc_check_mime_types1 = "checked ='checked'";
					break;
				}
	
				?>
				<tr>
					<th scope="row">
					<?php echo __('Check MIME types on file uploads', 'wpsc');?>:
					</th>
					<td>
						<input type='radio' value='0' name='wpsc_options[wpsc_check_mime_types]' id='wpsc_check_mime_types2' <?php echo $wpsc_check_mime_types1; ?> /> <label for='wpsc_check_mime_types2'><?php echo __('Yes', 'wpsc');?></label>&nbsp;
						<input type='radio' value='1' name='wpsc_options[wpsc_check_mime_types]' id='wpsc_check_mime_types1' <?php echo $wpsc_check_mime_types2; ?> /> <label for='wpsc_check_mime_types1'><?php echo __('No', 'wpsc');?></label><br />

						<span class="wpscsmall description">
							<?php echo __('Warning: Disabling this exposes your site to greater possibility of malicious files being uploaded, we reccomend installing the Fileinfo extention for PHP rather than disabling this.', 'wpsc'); ?>
						</span>
					</td>
				</tr> 
				
				
				<tr>
					<th scope="row">
					<?php echo __('Purchase Log Email', 'wpsc');?>:
					</th>
					<td>
					<input class='text' name='wpsc_options[purch_log_email]' type='text' size='40' value='<?php echo get_option('purch_log_email'); ?>' /><br />
					<?php _e('i.e. this is the address where your purchase reports are sent.','wpsc'); ?>
					
					</td>
				</tr>
				<tr>
					<th scope="row">
					<?php _e('Purchase Receipt - Reply Address');?>:
					</th>
					<td>
					<input class='text' name='wpsc_options[return_email]' type='text' size='40' value='<?php echo get_option('return_email'); ?>'  />
					<?php _e('i.e. this is the reply address contained in the purchase receipt sent to the buyer.','wpsc'); ?>
					</td>
				</tr>

				<tr>
					<th scope="row">
					<?php  _e('Purchase Receipt - Reply Name');?>:
					</th>
					<td>
					<input class='text' name='wpsc_options[return_name]' type='text' size='40' value='<?php echo get_option('return_name'); ?>'  />
					<?php _e('i.e. this is the "from name" buyers will see in their purchase receipt. ','wpsc'); ?>
					
					</td>
				</tr>
				
				<tr>
					<th scope="row">
					<?php echo __('Terms and Conditions', 'wpsc');?>:
					</th>
					<td>
					<textarea name='wpsc_options[terms_and_conditions]' cols='' rows='' style='width: 300px; height: 200px;'><?php echo stripslashes(get_option('terms_and_conditions')); ?></textarea>
					</td>
				</tr>
	
			</table> 
			<h3 class="form_group"><?php echo __('Custom Messages', 'wpsc');?>:</h3>
			<table class='wpsc_options form-table'>
				<tr>
					<th colspan="2"><?php echo __('Tags can be used', 'wpsc');?>: %shop_name%,<!-- %order_status%,--> %product_list%, %total_price%, %total_shipping%, %find_us%</th>
				</tr>
				<tr>
					<td class='wpsc_td_note' colspan='2'>
						<span class="wpscsmall description">
						Note: The purchase receipt is the message e-mailed to users after purchasing products from your shop.
						</span>
					</td>
				</tr>
				<tr>
					<th><strong><?php echo __('Purchase Receipt', 'wpsc');?></strong></th>
					<td><textarea name="wpsc_options[wpsc_email_receipt]" cols='' rows=''   style='width: 300px; height: 200px;'><?php echo stripslashes(get_option('wpsc_email_receipt'));?></textarea></td>
				</tr>
				<tr>
					<td class='wpsc_td_note' colspan='2'>
						<span class="wpscsmall description">
						Note: The Admin Report is the email sent to the e-mail address set above as soon as someone successfully buys a product.
						</span>
					</td>
				</tr>
				<tr>
					<th><strong><?php echo __('Admin Report', 'wpsc');?></strong></th>
					<td><textarea name="wpsc_options[wpsc_email_admin]" cols='' rows='' style='width: 300px; height: 200px;'><?php echo stripslashes(get_option('wpsc_email_admin'));?></textarea></td>
				</tr>
			</table>
			
			<h3 class="form_group"><?php echo __("Track and Trace settings", 'wpsc');?>:</h3>
			<table class='wpsc_options form-table'>
				<tr>
					<td class='wpsc_td_note' colspan='2'>
						<span class="wpscsmall description">
						Note: The Tracking Subject, is the subject for The Tracking Message email. The Tracking Message is the message e-mailed to users when you click 'Email buyer' on the sales log. This option is only available for purchases with the status of 'Job Dispatched'. Tags you can use in the email message section are %trackid% and %shop_name%
						</span>
					</td>
				</tr>
				<tr>
					<th><strong><?php echo __('Tracking Email Subject', 'wpsc');?></strong></th>
					<td><input name="wpsc_options[wpsc_trackingid_subject]" type='text' value='<?php echo stripslashes(get_option('wpsc_trackingid_subject'));?>' /></td>
				</tr>
				<tr>
					<th><strong><?php echo __('Tracking Email Message', 'wpsc');?></strong></th>
					<td><textarea name="wpsc_options[wpsc_trackingid_message]" cols='' rows=''   style='width: 300px; height: 200px;'><?php echo stripslashes(get_option('wpsc_trackingid_message'));?></textarea></td>
				</tr>
			</table>
			<h3 class="form_group"><?php echo __('URL Settings', 'wpsc');?>:</h3>
			<table class='wpsc_options form-table'>
			
				<tr class='merged'>
					<th scope="row">
					<?php echo __('The location of the product list', 'wpsc');?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('product_list_url'); ?>' name='wpsc_options[product_list_url]' />
					</td>
				</tr>
				<tr class='merged'>
					<th scope="row">
					<?php echo __('The location of the shopping cart', 'wpsc');?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('shopping_cart_url'); ?>' name='wpsc_options[shopping_cart_url]' />
					</td>
				</tr>
				<?php /*
				<tr class='merged'>
					<th scope="row">
					<?php echo __('The location of the checkout page', 'wpsc');?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('checkout_url'); ?>' name='checkout_url' />
					</td>
				</tr>*/
				?>
				<tr class='merged'>
					<th scope="row">
					<?php echo __('Transaction Details URL', 'wpsc');?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('transact_url'); ?>' name='wpsc_options[transact_url]' />
					</td>
				</tr>
			<?php
			if(function_exists("nzshpcrt_user_log")) {
			?>
				<tr class='merged'>
					<th scope="row">
					<?php echo __('User Account URL', 'wpsc');?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('user_account_url'); ?>' name='wpsc_options[user_account_url]' />
					</td>
				</tr>
			<?php
			do_action('wpsc_admin_settings_page');
			}
			$sendback = wp_get_referer();
			?>
				<tr class='merged'>
					<td colspan="2"><a href='<?php echo wp_nonce_url($sendback."&wpsc_admin_action=update_page_urls"); ?>' ><?php echo __('Update Page URLs', 'wpsc'); ?></a> &nbsp; | &nbsp;
					<a href='<?php echo wp_nonce_url($sendback."&wpsc_admin_action=clean_categories"); ?>'><?php echo __('Fix Product Group Permalinks', 'wpsc'); ?></a>
					</td>
				</tr>
			</table>					  
		<?php
		/* here end the admin options */						  
	  ?>
		<div class="submit">
			<input type='hidden' name='wpsc_admin_action' value='submit_options' />
			
			<?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
			<input type="submit" value="<?php echo __('Update &raquo;', 'wpsc');?>" name="updateoption"/>
       </div>
   </div>
</form>
						
<?php						
}					
						
						


?>