<?php
function wpsc_display_coupons_page(){
global $wpdb;
if(isset($_POST) && is_array($_POST) && !empty($_POST)) {

	if(isset($_POST['add_coupon']) && ($_POST['add_coupon'] == 'true')&& (!($_POST['is_edit_coupon'] == 'true'))) {
		$coupon_code = $_POST['add_coupon_code'];
		$discount = (double)$_POST['add_discount'];
		// cast to boolean, then integer, prevents the value from being anything but 1 or 0
		$discount_type = (int)$_POST['add_discount_type'];
		$use_once = (int)(bool)$_POST['add_use-once'];
		$every_product = (int)(bool)$_POST['add_every_product'];
// 		$start_date = date("Y-m-d H:i:s", mktime(0, 0, 0, (int)$_POST['add_start']['month'], (int)$_POST['add_start']['day'], (int)$_POST['add_start']['year']));
// 		$end_date = date("Y-m-d H:i:s", mktime(0, 0, 0, (int)$_POST['add_end']['month'], (int)$_POST['add_end']['day'], (int)$_POST['add_end']['year']));
		$start_date = date('Y-m-d', strtotime($_POST['add_start'])) . " 00:00:00";
		$end_date = date('Y-m-d', strtotime($_POST['add_end'])) . " 00:00:00";
		$rules = $_POST['rules'];
		foreach ($rules as $key => $rule) {
			foreach ($rule as $k => $r) {
				$new_rule[$k][$key] = $r;
			}
		}
		foreach($new_rule as $key => $rule) {
			if ($rule['value'] == '') {
				unset($new_rule[$key]);
			}
		}
		if($wpdb->query("INSERT INTO `".WPSC_TABLE_COUPON_CODES."` ( `coupon_code` , `value` , `is-percentage` , `use-once` , `is-used` , `active` , `every_product` , `start` , `expiry`, `condition` ) VALUES ( '$coupon_code', '$discount', '$discount_type', '$use_once', '0', '1', '$every_product', '$start_date' , '$end_date' , '".serialize($new_rule)."' );")) {  
			echo "<div class='updated'><p align='center'>".__('Thanks, the coupon has been added.', 'wpsc')."</p></div>";
		}
	}
	if(isset($_POST['is_edit_coupon']) && ($_POST['is_edit_coupon'] == 'true') && !(isset($_POST['delete_condition'])) && !(isset($_POST['submit_condition']))) {
			//exit('<pre>'.print_r($_POST, true).'</pre>');
		foreach((array)$_POST['edit_coupon'] as $coupon_id => $coupon_data) {
			//echo('<pre>'.print_r($coupon_data,true)."</pre>");
			$coupon_id = (int)$coupon_id;
			// convert dates to a form that compares well and can be inserted into the database
// 			$coupon_data['start'] = date("Y-m-d H:i:s", mktime(0, 0, 0, (int)$coupon_data['start']['month'], (int)$coupon_data['start']['day'], (int)$coupon_data['start']['year']));
// 			$coupon_data['expiry'] = date("Y-m-d H:i:s", mktime(0, 0, 0, (int)$coupon_data['expiry']['month'], (int)$coupon_data['expiry']['day'], (int)$coupon_data['expiry']['year']));
			$coupon_data['start'] = $coupon_data['start']." 00:00:00";
			$coupon_data['expiry'] = $coupon_data['expiry']." 00:00:00";
			$check_values = $wpdb->get_row("SELECT `id`, `coupon_code`, `value`, `is-percentage`, `use-once`, `active`, `start`, `expiry` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '$coupon_id'", ARRAY_A);
			//sort both arrays to make sure that if they contain the same stuff, that they will compare to be the same, may not need to do this, but what the heck
		//	exit('<pre>'.print_r($coupon_data, true).'</pre>');
			ksort($check_values); ksort($coupon_data);
			
			if($check_values != $coupon_data) {
				$insert_array = array();
				foreach($coupon_data as $coupon_key => $coupon_value) {
				  if(($coupon_key == "submit_coupon") || ($coupon_key == "delete_coupon")) {
				    continue;
				  }
					if($coupon_value != $check_values[$coupon_key]) {
						$insert_array[] = "`$coupon_key` = '$coupon_value'";
					}
				}
				//if(in_array(mixed needle, array haystack [, bool strict]))	
				//exit("<pre>".print_r($conditions,true)."</pre>");
				if(count($insert_array) > 0) {
					$wpdb->query("UPDATE `".WPSC_TABLE_COUPON_CODES."` SET ".implode(", ", $insert_array)." WHERE `id` = '$coupon_id' LIMIT 1;");
				}
				unset($insert_array);
				$rules = $_POST['rules'];
				
				foreach ((array)$rules as $key => $rule) {
					foreach ($rule as $k => $r) {
						$new_rule[$k][$key] = $r;
					}
				}
				foreach((array)$new_rule as $key => $rule) {
					if ($rule['value'] == '') {
						unset($new_rule[$key]);
					}
				}

				/*
$sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($new_rule)."' WHERE `id` = '$coupon_id' LIMIT 1";
				$wpdb->query($sql);
				
*/
			$conditions = $wpdb->get_var("SELECT `condition` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1");
				  $conditions=unserialize($conditions);
				  $new_cond=array();
				  if($_POST['rules']['value'][0] != ''){
					  $new_cond['property']=$_POST['rules']['property'][0];
					  $new_cond['logic']=$_POST['rules']['logic'][0];
					  $new_cond['value']=$_POST['rules']['value'][0];
					  $conditions []= $new_cond;
				  }
				  $sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($conditions)."' WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1";
				  $wpdb->query($sql);

			}
				
			if($coupon_data['delete_coupon'] != '') {
				$wpdb->query("DELETE FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '$coupon_id' LIMIT 1;");
			}
		}
	}
  if(isset($_POST['delete_condition'])){

	  $conditions = $wpdb->get_var("SELECT `condition` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1");
	  $conditions=unserialize($conditions);
	    
	  unset($conditions[(int)$_POST['delete_condition']]);
	  	
	  //$conditions = array_values($conditions);
	 //  exit('<pre>'.print_r($_POST, true).'</pre><pre>'.print_r($conditions, true).'</pre>'.$sql);
	  $sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($conditions)."' WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1";
	
	  $wpdb->query($sql);
  }
  if(isset($_POST['submit_condition'])){
	$conditions = $wpdb->get_var("SELECT `condition` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1");
	  $conditions=unserialize($conditions);
	  $new_cond=array();
	  $new_cond['property']=$_POST['rules']['property'][0];
	  $new_cond['logic']=$_POST['rules']['logic'][0];
	  $new_cond['value']=$_POST['rules']['value'][0];
	  $conditions []= $new_cond;
	  $sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($conditions)."' WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1";
	  $wpdb->query($sql);
  }
  if($_POST['change-settings'] == 'true') {
    if($_POST['wpsc_also_bought'] == 1) {
      update_option('wpsc_also_bought', 1);
		} else {
      update_option('wpsc_also_bought', 0);
		}
	
    if($_POST['display_find_us'] == 'on') {
      update_option('display_find_us', 1);
		} else {
      update_option('display_find_us', 0);
		}
      
    if($_POST['wpsc_share_this'] == 1) {
      update_option('wpsc_share_this', 1);
		} else {
      update_option('wpsc_share_this', 0);
		}
	}
}

/*<strong><?php echo TXT_WPSC_ADD_COUPON; ?></strong>*/
?>
<script type='text/javascript'>
	jQuery(".pickdate").datepicker();
		/* jQuery datepicker selector */
	if (typeof jQuery('.pickdate').datepicker != "undefined") {
		jQuery('.pickdate').datepicker({ dateFormat: 'yy-mm-dd' });
	}
</script>
<div class="wrap">
  <h2><?php echo __('Coupons', 'wpsc');?></h2>
  <div style='margin:0px;' class="tablenav wpsc_admin_nav">
  <!-- <a target="_blank" href="http://www.instinct.co.nz/e-commerce/marketing/" class="about_this_page"><span>About This Page</span> </a> -->

 	<form action='' method='post'>
		<input id='add_coupon_box_link' type='submit' class=' add_item_link button' name='add_coupon_button' value='<?php echo __('Create Coupon', 'wpsc');?>' onclick='return show_status_box("add_coupon_box","add_coupon_box_link");return false;' />
	</form>
</div>
<!-- <form name='edit_coupon' method='post' action=''>   -->
<table style="width: 100%;">
  <tr>
    <td id="coupon_data">
    

<div id='add_coupon_box' class='modify_coupon' >
<form name='add_coupon' method='post' action=''>
<table class='add-coupon' >
 <tr>
   <th>
   <?php echo __('Coupon Code', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Discount', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Start', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Expiry', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Use Once', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Active', 'wpsc'); ?>
   </th>
  <!--
 <th>
   <?php echo __('Apply On All Products', 'wpsc'); ?>
   </th>
-->
 </tr>
 <tr>
   <td>
   <input type='text' value='' name='add_coupon_code' />
   </td>
   <td>
   <input type='text' value='' size='3' name='add_discount' />
   <select name='add_discount_type'>
     <option value='0' >$</option>
     <option value='1' >%</option>
     <option value='2' >Free shipping</option>
   </select>
   </td>
   <td>
   <input type='text' class='pickdate' size='11' name='add_start' />
   <!--<select name='add_start[day]'>
   <?php
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d")) { $selected = "selected='selected'"; }
     echo "<option $selected value='$i'>$i</option>";
     }
   ?>
   </select>
   <select name='add_start[month]'>
   <?php
   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m")) { $selected = "selected='selected'"; }
     echo "<option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
   ?>
   </select>
   <select name='add_start[year]'>
   <?php
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == date("Y")) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>".$i."</option>";
     }
   ?>
   </select>-->
   </td>
   <td>
   <input type='text' class='pickdate' size='11' name='add_end'>
   <!--<select name='add_end[day]'>
   <?php
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d")) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>$i</option>";
     }
   ?>
   </select>
   <select name='add_end[month]'>
   <?php
   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m")) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
   ?>
   </select>
   <select name='add_end[year]'>
   <?php
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == (date("Y")+1)) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>".$i."</option>";
     }
   ?>
   </select>-->
   </td>
   <td>
   <input type='hidden' value='0' name='add_use-once' />
   <input type='checkbox' value='1' name='add_use-once' />
   </td>
   <td>
   <input type='hidden' value='0' name='add_active' />
   <input type='checkbox' value='1' checked='checked' name='add_active' />
   </td>

   <td>
   
   <input type='hidden' value='true' name='add_coupon' />
   <input type='submit' value='Add Coupon' name='submit_coupon' class='button-primary' />
   </td>
 </tr>
 <tr><td colspan="2">
		   <input type='hidden' value='0' name='add_every_product' />
			<input type="checkbox" value="1" name='add_every_product'/>
		<?php _e('Apply On All Products', 'wpsc')?></td></tr>

<tr><td colspan='3'><span id='table_header'>Conditions</span></td></tr>
<tr><td colspan="8">
	<div class='coupon_condition' >
		<div class='first_condition'>
			<select class="ruleprops" name="rules[property][]">
				<option value="item_name" rel="order">Item name</option>
				<option value="item_quantity" rel="order">Item quantity</option>
				<option value="total_quantity" rel="order">Total quantity</option>
				<option value="subtotal_amount" rel="order">Subtotal amount</option>
				<?php echo apply_filters( 'wpsc_coupon_rule_property_options', '' ); ?>
			</select>
			<select name="rules[logic][]">
				<option value="equal">Is equal to</option>
				<option value="greater">Is greater than</option>
				<option value="less">Is less than</option>
				<option value="contains">Contains</option>
				<option value="not_contain">Does not contain</option>
				<option value="begins">Begins with</option>
				<option value="ends">Ends with</option>
                		<option value="category">In Category</option>
			</select>
			<span>
				<input type="text" name="rules[value][]"/>
			</span>
			<span>
            	<script>
				var coupon_number=1;
				function add_another_property(this_button){
					var new_property='<div class="coupon_condition">\n'+
						'<div><img height="16" width="16" class="delete" alt="Delete" src="<?php echo WPSC_URL; ?>/images/cross.png" onclick="jQuery(this).parent().remove();"/> \n'+
							'<select class="ruleprops" name="rules[property][]"> \n'+
								'<option value="item_name" rel="order">Item name</option> \n'+
								'<option value="item_quantity" rel="order">Item quantity</option>\n'+
								'<option value="total_quantity" rel="order">Total quantity</option>\n'+ 
								'<option value="subtotal_amount" rel="order">Subtotal amount</option>\n'+ 
								'<?php echo apply_filters( 'wpsc_coupon_rule_property_options', '' ); ?>'+
							'</select> \n'+
							'<select name="rules[logic][]"> \n'+
								'<option value="equal">Is equal to</option> \n'+
								'<option value="greater">Is greater than</option> \n'+
								'<option value="less">Is less than</option> \n'+
								'<option value="contains">Contains</option> \n'+
								'<option value="not_contain">Does not contain</option> \n'+
								'<option value="begins">Begins with</option> \n'+
								'<option value="ends">Ends with</option> \n'+
							'</select> \n'+
							'<span> \n'+
								'<input type="text" name="rules[value][]"/> \n'+
							'</span>  \n'+
						'</div> \n'+
					'</div> ';
		
					jQuery('.coupon_condition :first').after(new_property);
					coupon_number++;
				}
				</script>
			
			</span>
			
		</div>
	</div>
</tr>
<tr><td>	<a class="wpsc_coupons_condition_add" onclick="add_another_property(jQuery(this));">
					<?php _e('Add New Condition','wpsc'); ?>
				</a></td></tr>
</table>
<br />
</form>  
</div>    

  <?php
  $num = 0;

echo "<table class='coupon-list'>\n\r";
echo "  <tr class='toprow'>\n\r";

echo "    <th>\n\r";
echo __('Coupon Code', 'wpsc');
echo "    </th>\n\r";

echo "    <th>\n\r";
echo __('Discount', 'wpsc');
echo "    </th>\n\r";

echo "    <th>\n\r";
echo __('Start', 'wpsc');
echo "    </th>\n\r";

echo "    <th>\n\r";
echo __('Expiry', 'wpsc');
echo "    </th>\n\r";

echo "    <th>\n\r";
echo __('Active', 'wpsc');
echo "    </th>\n\r";

echo "    <th>\n\r";
echo __('Apply On All Products', 'wpsc');
echo "    </th>\n\r";

echo "    <th>\n\r";
echo __('Edit', 'wpsc');
echo "    </th>\n\r";

$i=0;
$coupon_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` ",ARRAY_A);
//exit('Coupon Data<pre>'.print_r($coupon_data, true).'</pre>');
foreach((array)$coupon_data as $coupon) {
  $alternate = "";
  $i++;
  if(($i % 2) != 0) {
    $alternate = "class='alt'";
    }
  echo "<tr $alternate>\n\r";
  
  echo "    <td>\n\r";
  echo $coupon['coupon_code'];
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";
  if($coupon['is-percentage'] == 1){
    echo $coupon['value']."%";
    } else {    
    echo nzshpcrt_currency_display($coupon['value'], 1);
    }
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";
  echo date("d/m/Y",strtotime($coupon['start']));
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";
  echo date("d/m/Y",strtotime($coupon['expiry']));
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";  
  switch($coupon['active']) {
    case 1:
    echo "<img src='".WPSC_URL."/images/yes_stock.gif' alt='' title='' />";
    break;
    
    case 0: default:
    echo "<img src='".WPSC_URL."/images/no_stock.gif' alt='' title='' />";
    break;
    }
  echo "    </td>\n\r";
  
   echo "    <td>\n\r";
  switch($coupon['every_product']) {
	  case 1:
		  echo "<img src='".WPSC_URL."/images/yes_stock.gif' alt='' title='' />";
		  break;
    
	  case 0: default:
		  echo "<img src='".WPSC_URL."/images/no_stock.gif' alt='' title='' />";
		  break;
  }
  echo "    </td>\n\r";

  
  
  echo "    <td>\n\r";
  echo "<a title='".$coupon['coupon_code']."' href='#' rel='".$coupon['id']."' class='wpsc_edit_coupon'  >".__('Edit', 'wpsc')."</a>";
  echo "    </td>\n\r";
  
  echo "  </tr>\n\r";
  echo "  <tr>\n\r";
  echo "    <td colspan='7' style='padding-left:0px;'>\n\r";
//  $status_style = "style='display: block;'";
  echo "      <div id='coupon_box_".$coupon['id']."' class='displaynone modify_coupon' >\n\r";  
  coupon_edit_form($coupon);
  echo "      </div>\n\r";
  echo "    </td>\n\r";
  echo "  </tr>\n\r";
  }
echo "</table>\n\r";
  ?>
  <p style='margin: 0px 0px 5px 0px;'>
  	 <?php  _e('<strong>Note:</strong> Due to a current limitation of PayPal, if your user makes a purchase and uses a coupon, we can not send a list of items through to paypal for processing. Rather, we must send the total amount of the purchase, so that within PayPal the user who purchases a product will see your shop name and the total amount of their purchase.', 'wpsc');?>
  </p>
    </td>
  </tr>
</table>
<!-- <input type='hidden' value='true' name='is_edit_coupon' /> -->
<!-- </form> -->

<br />


      
<h2><?php echo __('Marketing Settings', 'wpsc');?></h2>

<form name='cart_options' method='POST' action=''>
<input type='hidden' value='true' name='change-settings' />
  <table>
    <tr>
      <td>
        <?php echo __('Display Cross Sales', 'wpsc');?>:
      </td>
      <td>
        <?php
        $wpsc_also_bought = get_option('wpsc_also_bought');
        $wpsc_also_bought1 = "";
        $wpsc_also_bought2 = "";
        switch($wpsc_also_bought) {
        case 0:
        $wpsc_also_bought2 = "checked ='true'";
        break;
        
        case 1:
        $wpsc_also_bought1 = "checked ='true'";
        break;
        }
        ?>
        <input type='radio' value='1' name='wpsc_also_bought' id='wpsc_also_bought1' <?php echo $wpsc_also_bought1; ?> /> <label for='wpsc_also_bought1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
        <input type='radio' value='0' name='wpsc_also_bought' id='wpsc_also_bought2' <?php echo $wpsc_also_bought2; ?> /> <label for='wpsc_also_bought2'><?php echo __('No', 'wpsc');?></label>
      </td>
    </tr>
    
    <tr>
      <td>
      <?php echo __('Show Share This (Social Bookmarks)', 'wpsc');?>:
      </td>
      <td>
        <?php
        $wpsc_share_this = get_option('wpsc_share_this');
        $wpsc_share_this1 = "";
        $wpsc_share_this2 = "";
        switch($wpsc_share_this) {
          case 0:
          $wpsc_share_this2 = "checked ='true'";
          break;
          
          case 1:
          $wpsc_share_this1 = "checked ='true'";
          break;
          }
        ?>
        <input type='radio' value='1' name='wpsc_share_this' id='wpsc_share_this1' <?php echo $wpsc_share_this1; ?> /> <label for='wpsc_share_this1'><?php echo __('Yes', 'wpsc');?></label> &nbsp;
        <input type='radio' value='0' name='wpsc_share_this' id='wpsc_share_this2' <?php echo $wpsc_share_this2; ?> /> <label for='wpsc_share_this2'><?php echo __('No', 'wpsc');?></label>
      </td>
    </tr>
	<tr>
        <td>
		<?php echo __('Display How Customer Found Us Survey', 'wpsc')?>
        </td>
	<?php
		$display_find_us = get_option('display_find_us');
		if ($display_find_us=='1') {
			$display_find_us1 = "checked ='checked'";
		}
	?>
        <td>
		<input <?php echo $display_find_us1; ?> type='checkbox' name='display_find_us'>
        </td>
      </tr>
      <tr>
        <td>
	
        </td>
        <td>
        <input  type='submit' value='<?php echo __('Submit', 'wpsc');?>' name='form_submit' />
        </td>
      </tr>
  </table>
</form>

<h2><?php echo __('RSS Address', 'wpsc');?></h2>
<table>
	<tr>
		<td colspan='2'>
			<?php echo __('<strong>Note:</strong> Not only can people use this RSS to keep update with your product list but you can also use this link to promote your products in your facebook profile. <br />Just add the <a href="http://apps.facebook.com/getshopped">getshopped! facebook application</a> to your facebook profile and follow the instructions.', 'wpsc');?>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
			RSS Feed Address:
		</td>
		<td>
			<?php echo get_bloginfo('url')."/index.php?rss=true&amp;action=product_list"; ?>
		</td>
	</tr>
</table>

<h2><?php echo __('Google Merchant Centre / Google Product Search', 'wpsc'); ?></h2>
<p>To import your products into <a href="http://www.google.com/merchants/" target="_blank">Google Merchant Centre</a> so that they appear within Google Product Search results, sign up for a Google Merchant Centre account and add a scheduled data feed with the following URL:</p>
<?php $google_feed_url = get_bloginfo('url')."/index.php?rss=true&action=product_list&xmlformat=google"; ?>
<a href="<?php echo htmlentities($google_feed_url); ?>"><?php echo htmlentities($google_feed_url); ?></a>
</div>
<?php
}
?>
