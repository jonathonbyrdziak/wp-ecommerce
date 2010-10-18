<?php
function wpsc_options_general(){
	global $wpdb;
?>
	<form name='cart_options' id='cart_options' method='post' action=''>
	<div id="options_general">
		<h2><?php _e('General Settings', 'wpsc'); ?></h2>
		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
		<table class='wpsc_options form-table'>
		<tr>
			<th scope="row"><?php echo __('Base Country/Region', 'wpsc'); ?>: </th>
			<td>
				<select name='wpsc_options[base_country]' onchange='submit_change_country();'>
				<?php echo country_list(get_option('base_country')); ?>
				</select>
				<span id='options_country'>
				<?php
				$region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".get_option('base_country')."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
				if($region_list != null) {
				?>
					<select name='wpsc_options[base_region]'>
						<?php
						foreach($region_list as $region) {
							if(get_option('base_region')  == $region['id']) {
								$selected = "selected='selected'";
							} else {
								$selected = "";
							}
						?>
						<option value='<?php echo $region['id']; ?>' <?php echo $selected; ?> ><?php echo $region['name']; ?></option>	<?php	
						} 
						?>
					</select>
				   
		<?php  	 }	?>
				</span>
				<br /><?php echo __('Select your primary business location.', 'wpsc');?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo __('Tax Settings', 'wpsc');?>:</th>
			<td>
				<span id='options_region'>
				<?php
				$country_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".get_option('base_country')."' LIMIT 1",ARRAY_A);
				echo $country_data['country'];
				$region_count = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".get_option('base_country')."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`") ;
				if($country_data['has_regions'] == 1) {
					?>&nbsp;&nbsp;&nbsp;&nbsp;<a href='<?php echo add_query_arg(array( 'page' => 'wpsc-settings', 'isocode' => get_option('base_country') )); ?>'><?php echo $region_count ?> Regions</a>
		<?php	} else { ?>
					<input type='hidden' name='country_id' value='<?php echo $country_data['id']; ?>' />
					&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='country_tax' class='tax_forms' maxlength='5' size='5' value='<?php echo $country_data['tax']; ?>' />%
		<?php	}	?>
				</span>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Tax Included in prices','wpsc') ?>:</th>		
			<td>
				<?php
					$tax_inprice0= '';
					$tax_inprice1= '';
				if(wpsc_tax_isincluded()){
					$tax_inprice1= 'checked="checked"';
				}else{
					$tax_inprice0= 'checked="checked"';
				}
				?>
				<input <?php echo $tax_inprice1; ?> type='radio' name='wpsc_options[tax_inprice]' value='1' id='tax_inprice1' />
				<label for='tax_inprice1'><?php echo __('Yes', 'wpsc'); ?></label>
				<input <?php echo $tax_inprice0; ?> type='radio' name='wpsc_options[tax_inprice]' value='0' id='tax_inprice0' />
				<label for='tax_inprice1'><?php echo __('No', 'wpsc'); ?></label>
			</td>
		</tr>

		<?php	/* START OF TARGET MARKET SELECTION */					
		$countrylist = $wpdb->get_results("SELECT id,country,visible FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY country ASC ",ARRAY_A);
		?>
		<tr>
			<th scope="row">
			<?php echo __('Target Markets', 'wpsc'); ?>:
			</th>
			<td>
				<?php
				// check for the suhosin module
				if(@extension_loaded('suhosin') && (@ini_get('suhosin.post.max_vars') > 0) && (@ini_get('suhosin.post.max_vars') < 500)) {
					echo "<em>".__("The Target Markets feature has been disabled because you have the Suhosin PHP extension installed on this server. If you need to use the Target Markets feature then disable the suhosin extension, if you can not do this, you will need to contact your hosting provider.
			",'wpsc')."</em>";
   		
				} else {
					?>
					<span>Select: <a href='<?php echo add_query_arg(array('selected_all' => 'all'))?>' class='wpsc_select_all'>All</a>&nbsp; <a href='<?php echo add_query_arg(array( 'selected_all'=>'none'))?>' class='wpsc_select_none'>None</a></span><br />

					<div id='resizeable' class='ui-widget-content multiple-select'>
						<?php
							foreach((array)$countrylist as $country){
								$country['country'] = htmlspecialchars($country['country']);
								if($country['visible'] == 1){ ?>
									<input type='checkbox' name='countrylist2[]' value='<?php echo $country['id']; ?>'  checked='checked' /><?php echo $country['country']; ?><br />
						<?php	}else{ ?>
									<input type='checkbox' name='countrylist2[]' value='<?php echo $country['id']; ?>'  /><?php echo $country['country']; ?><br />
						<?php	}
									
							}	?>		
					</div><br />
					Select the markets you are selling products to.
				<?php
				}
			?>
			</td>
		</tr>
		</table> 
							
		<h3 class="form_group"><?php echo __('Currency Settings', 'wpsc');?>:</h3>
		<table class='wpsc_options form-table'>
		<tr>
			<th scope="row"><?php echo __('Currency type', 'wpsc');?>:</th>
			<td>
				<select name='wpsc_options[currency_type]' onchange='getcurrency(this.options[this.selectedIndex].value);'>
				<?php
				$currency_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
				foreach($currency_data as $currency) {
					if(get_option('currency_type') == $currency['id']) {
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
		</tr>
		<tr>
			<th scope="row"><?php echo __('Currency sign location', 'wpsc');?>:</th>
			<td>
				<?php
				$currency_sign_location = get_option('currency_sign_location');
				$csl1 = "";
				$csl2 = "";
				$csl3 = "";
				$csl4 = "";
				switch($currency_sign_location) {
					case 1:
					$csl1 = "checked ='checked'";
					break;
				
					case 2:
					$csl2 = "checked ='checked'";
					break;
				
					case 3:
					$csl3 = "checked ='checked'";
					break;
				
					case 4:
					$csl4 = "checked ='checked'";
					break;
				}
				?>
				<input type='radio' value='1' name='wpsc_options[currency_sign_location]' id='csl1' <?php echo $csl1; ?> /> 
				<label for='csl1'>100<span id='cslchar1'><?php echo $currency_sign; ?></span></label> &nbsp;
				<input type='radio' value='2' name='wpsc_options[currency_sign_location]' id='csl2' <?php echo $csl2; ?> /> 
				<label for='csl2'>100 <span id='cslchar2'><?php echo $currency_sign; ?></span></label> &nbsp;
				<input type='radio' value='3' name='wpsc_options[currency_sign_location]' id='csl3' <?php echo $csl3; ?> /> 
				<label for='csl3'><span id='cslchar3'><?php echo $currency_sign; ?></span>100</label> &nbsp;
				<input type='radio' value='4' name='wpsc_options[currency_sign_location]' id='csl4' <?php echo $csl4; ?> /> 
				<label for='csl4'><span id='cslchar4'><?php echo $currency_sign; ?></span> 100</label>
			</td>
		</tr>
		<tr>
			<?php 
			$decimals = get_option('wpsc_hide_decimals');
			switch($decimals){
				case '1':
				$decimal1 = 'checked="checked"';
				break;
			
				case '0':
				default:
				$decimal2 = 'checked="checked"';
				break;
			}
			
			?>
			<th scope="row"><?php _e('Hide Decimals on Products Pages'); ?></th>
			<td>
			<input type='radio' value='1' name='wpsc_options[wpsc_hide_decimals]' id='hide_decimals1' <?php echo $decimal1; ?> />
			<label for='hide_decimals1'><?php _e('Yes'); ?></label>

			<input type='radio' value='0' name='wpsc_options[wpsc_hide_decimals]' id='hide_decimals2' <?php echo $decimal2; ?> />
			<label for='hide_decimals2'><?php _e('No'); ?></label>
			</td>
		</tr>
		</table> 
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