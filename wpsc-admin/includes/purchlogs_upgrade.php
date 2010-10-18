<?php 
	global $wpdb;
	$numChanged = 0;
	$numQueries = 0;
	$purchlog =  "SELECT DISTINCT id FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIMIT 1";
	$id = $wpdb->get_var($purchlog);
	//exit($id);
	$usersql = "SELECT DISTINCT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value, `".WPSC_TABLE_CHECKOUT_FORMS."`.* FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE `".WPSC_TABLE_SUBMITED_FORM_DATA."`.log_id=".$id." ORDER BY `".WPSC_TABLE_CHECKOUT_FORMS."`.`order`" ;
	//exit($usersql);
	$formfields = $wpdb->get_results($usersql);
	
	
	
	if(count($formfields) < 1){
		$usersql = "SELECT DISTINCT  `".WPSC_TABLE_CHECKOUT_FORMS."`.* FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` != 'heading'";
		$formfields = $wpdb->get_results($usersql);
	
	}	
	//exit('<pre>'.print_r($formfields,true).'</pre>');
	//echo '<pre>'.print_r($formfields, true).'</pre>';
if(isset($_POST)){
	//echo '<pre>'.print_r($_POST, true).'</pre>';
	foreach($_POST as $key=>$value){
		if($value != '-1'){
			$sql = "UPDATE  `".WPSC_TABLE_CHECKOUT_FORMS."` SET `unique_name`='".$value."' WHERE id=".$key;
			$complete = $wpdb->query($sql);
	//	exit(' ' .$sql);
		}
		$numChaged++;
		$numQueries ++;
	}
	
	$sql = "UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `unique_name`='delivertoafriend' WHERE `name` = '2. Shipping details'";
	$wpdb->query($sql);
	
	add_option('wpsc_purchaselogs_fixed',true);
}
function wpsc_select_options_purchlogs_fix($id){
	?>
	<select name='<?php echo $id; ?>'>
		<option value='-1'>Select an Option</option>
		<option value='billingfirstname'>Billing First Name</option>
		<option value='billinglastname'>Billing Last Name</option>
		<option value='billingaddress'>Billing Address</option>
		<option value='billingcity'>Billing City</option>
		<option value='billingcountry'>Billing Country</option>
		<option value='billingemail'>Billing Email</option>
		<option value='billingphone'>Billing Phone</option>
		<option value='billingpostcode'>Billing Post Code</option>
		<option value='shippingfirstname'>Shipping First Name</option>
		<option value='shippinglastname'>Shipping Last Name</option>		
		<option value='shippingaddress'>Shipping Address</option>
		<option value='shippingcity'>Shipping City</option>
		<option value='shippingstate'>Shipping State</option>
		<option value='shippingcountry'>Shipping Country</option>
		<option value='shippingpostcode'>Shipping Post Code</option>

	</select> 
	<?php
}
?>

<div class='wrap'>
	
			<?php if ( $numChanged != 0 && $numQueries != 0 ) {
				echo '<div id="message" class="updated fade"><p>';
				printf( __ngettext( 'Check Out Form Fields updated.', 'Check Out Form Fields updated.', $numChanged, $numQueries ), $numChanged , $numQueries);
				echo '</p></div>';
			}
	
			?>
			
<h2><?php echo wp_specialchars( __('Sales', 'wpsc').' Upgrade Fix' ); ?> </h2>
<p><?php _e('Upgrading to WP e-Commerce 3.7 and later requires you to run this fix once.The following Boxes corresponds to the form fields in your current checkout page.  All you have to do is select from the drop-down menu box what each of the following fields represent. Sorry for any inconvenience caused, but we\'re sure you\'ll agree that the new purchase logs are worth this minor hassle. '); ?> </p>

<div class="metabox-holder" style="width:700px">
<form action='' method='post'>

	<?php
	
	$duplicate = array();
	foreach($formfields as $fields){
		if(!in_array($fields->name,$duplicate) && $fields->name != 'State'){
		echo '<div class="postbox" style="width:70%">';
		echo '<h3 class="handle">Billing '.$fields->name.'</h3>';
		echo '<div class="inside" style="padding:20px;">';
		echo '<label style="width:120px;float:left;" for="'.$fields->id.'">'.$fields->value.'</label>';
		echo wpsc_select_options_purchlogs_fix($fields->id);
		echo '</div>';
		echo '</div>';
		$duplicate[] = $fields->name;
		}else{
		echo '<div class="postbox" style="width:70%">';
		echo '<h3 class="handle">Shipping '.$fields->name.'</h3>';
		echo '<div class="inside" style="padding:20px;">';
		echo '<label style="width:120px;float:left;" for="'.$fields->id.'">'.$fields->value.'</label>';
		echo wpsc_select_options_purchlogs_fix($fields->id);
		echo '</div>';
		echo '</div>';
		
		}
		
	}
	//exit('<pre>'.print_r($duplicate, true).'</pre>');
	?>
	<input type='submit' value='Apply' class='button-secondary action' />
</form>
</div>
</div>