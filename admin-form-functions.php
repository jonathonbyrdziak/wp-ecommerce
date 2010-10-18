<?php
// ini_set('display_errors','1');

function nzshpcrt_getcategoryform($catid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`=$catid LIMIT 1",ARRAY_A);
  $output = '';
  $output .= "<div class='editing_this_group form_table'>";
	$output .= "<p>".str_replace("[categorisation]", htmlentities(stripslashes($product['name'])), __('You are editing the &quot;[categorisation]&quot; Category', 'wpsc'))."</p>\n\r";
	$output .= "<p><a href='' onclick='return showaddform()' class='add_category_link'><span>".str_replace("&quot;[categorisation]&quot;", "current", __('+ Add new category to the &quot;[categorisation]&quot; Group', 'wpsc'))."</span></a></p>";
	$output .="<dl>\n\r";
	$output .="		<dt>Display Category Shortcode: </dt>\n\r";
	$output .="		<dd> [wpsc_products category_url_name='{$product['nice-name']}']</dd>\n\r";
	$output .="		<dt>Display Category Template Tag: </dt>\n\r";
	$output .="		<dd> &lt;?php echo wpsc_display_products_page(array('category_url_name'=>'{$product['nice-name']}')); ?&gt;</dd>\n\r";
	$output .="</dl>\n\r";
	
	//$output .= "       [ <a href='#' onclick='return showedit_categorisation_form()'>".__('Edit This Group', 'wpsc')."</a> ]";
	
	$output .= "</div>";
  $output .= "        <table class='category_forms'>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Name', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' class='text' name='title' value='".htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Description', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Category Parent', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= wpsc_parent_category_list($product['group_id'], $product['id'], $product['category_parent']);
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";


	if ($product['display_type'] == 'grid') {
		$display_type1="selected='selected'";
	} else if ($product['display_type'] == 'default') {
		$display_type2="selected='selected'";
	}
	
	switch($product['display_type']) {
	  case "default":
			$product_view1 = "selected ='selected'";
		break;
		
		case "grid":
		if(function_exists('product_display_grid')) {
			$product_view3 = "selected ='selected'";
			break;
		}
		
		case "list":
		if(function_exists('product_display_list')) {
			$product_view2 = "selected ='selected'";
			break;
		}
		
		default:
			$product_view0 = "selected ='selected'";
		break;
	}	
	
	

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Category&nbsp;Image', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='file' name='image' value='' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  if(function_exists("getimagesize")) {
    if($product['image'] != '') {
      $imagepath = WPSC_CATEGORY_DIR . $product['image'];
      $imagetype = @getimagesize($imagepath); //previously exif_imagetype()
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= "            </td>\n\r";
      $output .= "            <td>\n\r";
      $output .= __('Height', 'wpsc').":<input type='text' size='6' name='height' value='".$imagetype[1]."' /> ".__('Width', 'wpsc').":<input type='text' size='6' name='width' value='".$imagetype[0]."' /><br /><span class='wpscsmall description'>$nzshpcrt_imagesize_info</span><br />\n\r";
			$output .= "<span class='wpscsmall description'>".__('You can upload thumbnail images for each group. To display Group details in your shop you must configure these settings under <a href="admin.php?page=wpsc-settings&tab=presentation">Presentation Settings</a>.', 'wpsc')."</span>\n\r";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
		} else {
			$output .= "          <tr>\n\r";
			$output .= "            <td>\n\r";
			$output .= "            </td>\n\r";
			$output .= "            <td>\n\r";
			$output .= __('Height', 'wpsc').":<input type='text' size='6' name='height' value='".get_option('product_image_height')."' /> ".__('Width', 'wpsc').":<input type='text' size='6' name='width' value='".get_option('product_image_width')."' /><br /><span class='wpscsmall description'>$nzshpcrt_imagesize_info</span><br />\n\r";
			$output .= "<span class='wpscsmall description'>".__('You can upload thumbnail images for each group. To display Group details in your shop you must configure these settings under <a href="admin.php?page=wpsc-settings&tab=presentation">Presentation Settings</a>.', 'wpsc')."</span>\n\r";
			$output .= "            </td>\n\r";
			$output .= "          </tr>\n\r";
		}
	}
	
	$output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Delete Image', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='checkbox' name='deleteimage' value='1' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";
	 /* START OF TARGET MARKET SELECTION */					
	$countrylist = $wpdb->get_results("SELECT id,country,visible FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY country ASC ",ARRAY_A);
	$selectedCountries = $wpdb->get_col("SELECT countryid FROM `".WPSC_TABLE_CATEGORY_TM."` WHERE categoryid=".$product['id']." AND visible= 1");
//	exit('<pre>'.print_r($countrylist,true).'</pre><br /><pre>'.print_r($selectedCountries,true).'</pre>');
	$output .= " <tr>\n\r";
	$output .= " 	<td colspan='2'><h4>Target Market Restrictions</h4></td></tr><tr><td>&nbsp;</td></tr><tr>\n\r";
	$output .= " 	<td>\n\r";
	$output .= __('Target Markets', 'wpsc').":\n\r";
	$output .= " 	</td>\n\r";
	$output .= " 	<td>\n\r";

	if(@extension_loaded('suhosin')) {
		$output .= "<em>".__("The Target Markets feature has been disabled because you have the Suhosin PHP extension installed on this server. If you need to use the Target Markets feature then disable the suhosin extension, if you can not do this, you will need to contact your hosting provider.
			",'wpsc')."</em>";

	} else {
		$output .= "<span>Select: <a href='' class='wpsc_select_all'>All</a>&nbsp; <a href='' class='wpsc_select_none'>None</a></span><br />";
		$output .= " 	<div id='resizeable' class='ui-widget-content multiple-select'>\n\r";
		foreach($countrylist as $country){
			if(in_array($country['id'], $selectedCountries))
			/* if($country['visible'] == 1) */{
			$output .= " <input type='checkbox' name='countrylist2[]' value='".$country['id']."'  checked='".$country['visible']."' />".$country['country']."<br />\n\r";
			}else{
			$output .= " <input type='checkbox' name='countrylist2[]' value='".$country['id']."'  />".$country['country']."<br />\n\r";
			}
				
		}
		$output .= " </div><br /><br />";
		$output .= " <span class='wpscsmall description'>Select the markets you are selling this category to.<span>\n\r";
	}

	$output .= "   </td>\n\r";
	
	$output .= " </tr>\n\r";
	////////

	$output .= "          <tr>\n\r";
	$output .= "          	<td colspan='2' class='category_presentation_settings'>\n\r";
	$output .= "          		<h4>".__('Presentation Settings', 'wpsc')."</h4>\n\r";
	$output .= "          		<span class='small'>".__('To over-ride the presentation settings for this group you can enter in your prefered settings here', 'wpsc')."</span>\n\r";
	$output .= "          	</td>\n\r";
	$output .= "          </tr>\n\r";
	
	$output .= "          <tr>\n\r";
	$output .= "          	<td>\n\r";
	$output .= "          	". __('Catalog View', 'wpsc').":\n\r";
	$output .= "          	</td>\n\r";
	$output .= "          	<td>\n\r";
	$output .= "          		<select name='display_type'>\n\r";	
	$output .= "          			<option value='' $product_view0 >".__('Please select', 'wpsc')."</option>\n\r";	
	$output .= "          			<option value='default' $product_view1 >".__('Default View', 'wpsc')."</option>\n\r";	
	if(function_exists('product_display_list')) {
		$output .= "          			<option value='list' ". $product_view2.">". __('List View', 'wpsc')."</option>\n\r"; 
	} else {
		$output .= "          			<option value='list' disabled='disabled' ". $product_view2.">". __('List View', 'wpsc')."</option>\n\r";
	}	
	if(function_exists('product_display_grid')) {
		$output .= "          			<option value='grid' ". $product_view3.">". __('Grid View', 'wpsc')."</option>\n\r";
	} else {
		$output .= "          			<option value='grid' disabled='disabled' ". $product_view3.">". __('Grid View', 'wpsc')."</option>\n\r";
	}	
	$output .= "          		</select>\n\r";	
	$output .= "          	</td>\n\r";
	$output .= "          </tr>\n\r";
	
	
  if(function_exists("getimagesize")) {
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= __('Thumbnail&nbsp;Size', 'wpsc').": ";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		$output .= __('Height', 'wpsc').": <input type='text' value='".$product['image_height']."' name='product_height' size='6'/> ";
		$output .= __('Width', 'wpsc').": <input type='text' value='".$product['image_width']."' name='product_width' size='6'/> <br/>";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
	}



	$output .= "          <tr>\n\r";
	$output .= "          	<td colspan='2' class='category_presentation_settings'>\n\r";
	$output .= "          		<h4>".__('Checkout Settings', 'wpsc')."</h4>\n\r";
	//$output .= "          		<span class='small'>".__('To over-ride the presentation settings for this group you can enter in your prefered settings here', 'wpsc')."</span>\n\r";
	$output .= "          	</td>\n\r";
	$output .= "          </tr>\n\r";

	
		$used_additonal_form_set = wpsc_get_categorymeta($product['id'], 'use_additonal_form_set');
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= __("This category requires additional checkout form fields",'wpsc').": ";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		
		$output .= "            <select name='use_additonal_form_set'>\n\r";
		$output .= "            	<option value=''>".__("None",'wpsc')."</option>\n\r";
		
		$checkout_sets = get_option('wpsc_checkout_form_sets');
		unset($checkout_sets[0]);
		foreach((array)$checkout_sets as $key => $value) {
			$selected_state = "";
			if($used_additonal_form_set == $key) {
				$selected_state = "selected='selected'";
			}
			$output .= "            <option {$selected_state} value='{$key}'>".stripslashes($value)."</option>\n\r";
		}
		$output .= "            </select>\n\r";
    //$output .= "            <label><input type='radio' value='1' name='uses_additonal_forms' ".(($uses_additional_forms == true) ? "checked='checked'" : "")." />".__("Yes",'wpsc')."</label>";
		//$output .= "            <label><input type='radio' value='0' name='uses_additonal_forms' ".(($uses_additional_forms != true) ? "checked='checked'" : "")." />".__("No",'wpsc')."</label>";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";


	$output .= "          <tr>";
	$output .= "          	<td colspan='2'>						</td>";
	$output .= "          </tr>";
		
		$uses_billing_address = (bool)wpsc_get_categorymeta($product['id'], 'uses_billing_address');
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= __("Products in this category use the billing address to calculate shipping",'wpsc').": ";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
    $output .= "            <label><input type='radio' value='1' name='uses_billing_address' ".(($uses_billing_address == true) ? "checked='checked'" : "")." />".__("Yes",'wpsc')."</label>";
		$output .= "            <label><input type='radio' value='0' name='uses_billing_address' ".(($uses_billing_address != true) ? "checked='checked'" : "")." />".__("No",'wpsc')."</label>";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td class='last_row'>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button-primary' style='float:left;' type='submit' name='submit' value='".__('Update Category', 'wpsc')."' />";
	$output .= "<a class='delete_button' href='".add_query_arg('deleteid', $product['id'], 'admin.php?page=wpsc-edit-groups')."' onclick=\"return conf();\" >".__('Delete', 'wpsc')."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r"; 
  return $output;
  }

function nzshpcrt_getvariationform($variation_id)
  {
  global $wpdb,$nzshpcrt_imagesize_info;

  $variation_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` WHERE `id`='$variation_id' LIMIT 1";
  $variation_data = $wpdb->get_results($variation_sql,ARRAY_A) ;
  $variation = $variation_data[0];
  $output .= "        <table class='category_forms' >\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Name', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text'  class='text' name='title' value='".htmlentities(stripslashes($variation['name']), ENT_QUOTES, 'UTF-8')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= __('Variation Values', 'wpsc').": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $variation_values_sql = "SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id`='$variation_id' ORDER BY `id` ASC";
  $variation_values = $wpdb->get_results($variation_values_sql,ARRAY_A);
  $variation_value_count = count($variation_values);
  $output .= "<div id='edit_variation_values'>";
  $num = 0;
  foreach($variation_values as $variation_value) {
    $output .= "<span class='variation_value'>";
    $output .= "<input type='text' class='text' name='variation_values[".$variation_value['id']."]' value='".htmlentities(stripslashes($variation_value['name']), ENT_QUOTES, 'UTF-8')."' />";
    if($variation_value_count > 1) {
      $output .= " <a  class='image_link' onclick='return remove_variation_value(this,".$variation_value['id'].")' href='#'><img src='".WPSC_URL."/images/trash.gif' alt='".__('Delete', 'wpsc')."' title='".__('Delete', 'wpsc')."' /></a>";
		}
    $output .= "<br />";
    $output .= "</span>";
    $num++;
	}
  $output .= "</div>";
  $output .= "<a href='#'  onclick='return add_variation_value(\"edit\")'>".__('Add', 'wpsc')."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$variation['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;'  type='submit' name='submit' value='".__('Edit', 'wpsc')."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=".WPSC_DIR_NAME."/display_variations.php&amp;deleteid=".$variation['id']."' onclick=\"return conf();\" >".__('Delete', 'wpsc')."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r";
  return $output;
  }

function coupon_edit_form($coupon) {

$conditions = unserialize($coupon['condition']);
	//exit('<pre>'.print_r($coupon, true).'</pre>');

  $start_timestamp = strtotime($coupon['start']);
  $end_timestamp = strtotime($coupon['expiry']);
  $id = $coupon['id'];
  $output = '';
  $output .= "<form name='edit_coupon' id='".$coupon['coupon_code']."' method='post' action=''>\n\r";
    $output .= "   <input type='hidden' value='true' name='is_edit_coupon' />\n\r";
  $output .= "<table class='add-coupon'>\n\r";
  $output .= " <tr>\n\r";
  $output .= "   <th>".__('Coupon Code', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Discount', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Start', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Expiry', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Use Once', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Active', 'wpsc')."</th>\n\r";
	$output .= "   <th>".__('Apply On All Products', 'wpsc')."</th>\n\r";
  $output .= "   <th></th>\n\r";
  $output .= " </tr>\n\r";
  $output .= " <tr>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' size='8' value='".$coupon['coupon_code']."' name='edit_coupon[".$id."][coupon_code]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' style='width:28px;' value='".$coupon['value']."'  name=edit_coupon[".$id."][value]' />";
  $output .= "   <select style='width:20px;' name='edit_coupon[".$id."][is-percentage]'>";
  $output .= "     <option value='0' ".(($coupon['is-percentage'] == 0) ? "selected='true'" : '')." >$</option>\n\r";//
  $output .= "     <option value='1' ".(($coupon['is-percentage'] == 1) ? "selected='true'" : '')." >%</option>\n\r";
  $output .= "     <option value='2' ".(($coupon['is-percentage'] == 2) ? "selected='true'" : '')." >Free shipping</option>\n\r";
  $output .= "   </select>\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_start = explode(" ",$coupon['start']);
  $output .= "<input type='text' class='pickdate' size='8' name='edit_coupon[".$id."][start]' value='{$coupon_start[0]}'>";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_expiry = explode(" ",$coupon['expiry']);
  $output .= "<input type='text' class='pickdate' size='8' name='edit_coupon[".$id."][expiry]' value='{$coupon_expiry[0]}'>";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['use-once'] == 1) ? "checked='checked'" : '')." name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['active'] == 1) ? "checked='checked'" : '')." name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['every_product'] == 1) ? "checked='checked'" : '')." name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='".$id."' name='edit_coupon[".$id."][id]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= " </tr>\n\r";

  if($conditions != null){
	  $output .= "<tr>";
	  $output .= "<th>";
	  $output .= "Conditions";
	  $output .= "</th>";
	  $output .= "</tr>";
	  $output .= "<th>";
	  $output .= "Delete";
	  $output .= "</th>";
	  $output .= "<th>";
	  $output .= "Property";
	  $output .= "</th>";
	  $output .= "<th>";
	  $output .= "Logic";
	  $output .= "</th>";
	  $output .= "<th>";
	  $output .= "Value";
	  $output .= "</th>";
	  $output .= " </tr>\n\r";
	  $i=0;
	  foreach ($conditions as $condition){
		  $output .= "<tr>";
		  $output .= "<td>";
		  $output .= "<input type='hidden' name='coupon_id' value='".$id."' />";
		  $output .= "<input type='submit' id='delete_condition".$i."' style='display:none;' value='".$i."' name='delete_condition' />";
		  $output .= "<span style='cursor:pointer;' class='delete_button' onclick='jQuery(\"#delete_condition".$i."\").click()'>Delete</span>";
		  $output .= "</td>";
		  $output .= "<td>";
		  $output .= $condition['property'];
		  $output .= "</td>";
		  $output .= "<td>";
		  $output .= $condition['logic'];
		  $output .= "</td>";
		  $output .= "<td>";
		  $output .= $condition['value'];
		  $output .= "</td>";
		  $output .= "</tr>";
		  $i++;
	  }
	  $output .=	wpsc_coupons_conditions( $id);
  }elseif($conditions == null){
  	$output .=	wpsc_coupons_conditions( $id);

  }
  $output .= "</table>\n\r";
  $output .= "</form>\n\r";
  echo $output;
  return $output;
  }
function wpsc_coupons_conditions($id){
?>

<?php

$output ='
<input type="hidden" name="coupon_id" value="'.$id.'" />
<tr><td colspan="3"><b>Add Conditions</b></td></tr>
<tr><td colspan="6">
	<div class="coupon_condition">
		<div>
			<select class="ruleprops" name="rules[property][]">
				<option value="item_name" rel="order">Item name</option>
				<option value="item_quantity" rel="order">Item quantity</option>
				<option value="total_quantity" rel="order">Total quantity</option>
				<option value="subtotal_amount" rel="order">Subtotal amount</option>
				' . apply_filters( 'wpsc_coupon_rule_property_options', '' ) . '
			</select>
			<select name="rules[logic][]">
				<option value="equal">Is equal to</option>
				<option value="greater">Is greater than</option>
				<option value="less">Is less than</option>
				<option value="contains">Contains</option>
				<option value="not_contain">Does not contain</option>
				<option value="begins">Begins with</option>
				<option value="ends">Ends with</option>
			</select>
			<span>
				<input type="text" name="rules[value][]"/>
			</span>
			

		</div>
	</div>
	</td>
	<td colspan="3">
	
		<input type="submit" value="'.__("Update Coupon", "wpsc").'" name="edit_coupon['.$id.'][submit_coupon]" />
 		<input type="submit" value="'.__("Delete Coupon", "wpsc").'" name="edit_coupon['.$id.'][delete_coupon]" />
	</td>
</tr>
';
return $output;

}  
function setting_button(){
	$next_url  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."?page=wpsc-edit-products";
	
// 	$output.="<div><img src='".get_option('siteurl')."/wp-content/plugins/".WPSC_DIR_NAME."/images/settings_button.jpg' onclick='display_settings_button()'>";
	$output.="<div style='float: right; margin-top: 0px; position: relative;'> | <a href='#' onclick='display_settings_button(); return false;' style='text-decoration: underline;'>".__('Settings', 'wpsc')." &raquo;</a>";
	$output.="<span id='settings_button' style='width:180px;background-color:#f1f1f1;position:absolute; right: 10px; border:1px solid black; display:none;'>";
	$output.="<ul class='settings_button'>";
	
	$output.="<li><a href='admin.php?page=wpsc-settings'>".__('Shop Settings', 'wpsc')."</a></li>";
	$output.="<li><a href='admin.php?page=wpsc-settings&amp;tab=gateway'>".__('Money and Payment', 'wpsc')."</a></li>";
	$output.="<li><a href='admin.php?page=wpsc-settings&amp;tab=checkout'>".__('Checkout Page Settings', 'wpsc')."</a></li>";
	//$output.="<li><a href='?page=".WPSC_DIR_NAME."/instructions.php'>Help/Upgrade</a></li>";
	$output.="</ul>";
//	$output.="<div>Checkout Settings</div>";
	$output.="</span>&emsp;&emsp;</div>";
	
	return $output;
}

function wpsc_right_now($hidden = '') {
  global $wpdb,$nzshpcrt_imagesize_info;
	$year = date("Y");
	$month = date("m");
	$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
	$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);

  $replace_values[":productcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1')");
  $product_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1')");
  $replace_values[":productcount:"] .= " ".(($replace_values[":productcount:"] == 1) ? __('product', 'wpsc') : __('products', 'wpsc'));
  $product_unit = (($replace_values[":productcount:"] == 1) ? __('product', 'wpsc') : __('products', 'wpsc'));
  
  $replace_values[":groupcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN ('1')");
  $group_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN ('1')");
  $replace_values[":groupcount:"] .= " ".(($replace_values[":groupcount:"] == 1) ? __('group', 'wpsc') : __('groups', 'wpsc'));
  $group_unit = (($replace_values[":groupcount:"] == 1) ? __('group', 'wpsc') : __('groups', 'wpsc'));
  
  $replace_values[":salecount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
  $sales_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
  $replace_values[":salecount:"] .= " ".(($replace_values[":salecount:"] == 1) ? __('sale', 'wpsc') : __('sales', 'wpsc'));
  $sales_unit = (($replace_values[":salecount:"] == 1) ? __('sale', 'wpsc') : __('sales', 'wpsc'));
		
  $replace_values[":monthtotal:"] = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
  $replace_values[":overaltotal:"] = nzshpcrt_currency_display(admin_display_total_price(),1);
  
  $variation_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."`");
  $variation_unit = (($variation_count == 1) ? __('variation', 'wpsc') : __('variations', 'wpsc'));
  
  $replace_values[":pendingcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('1')");
  $pending_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('1')");
  $replace_values[":pendingcount:"] .= " " . (($replace_values[":pendingcount:"] == 1) ? __('transaction', 'wpsc') : __('transactions', 'wpsc'));
  $pending_sales_unit = (($replace_values[":pendingcount:"] == 1) ? __('transaction', 'wpsc') : __('transactions', 'wpsc'));
  
  $accept_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('2' ,'3', '4')");
  $accept_sales_unit = (($accept_sales == 1) ? __('transaction', 'wpsc') : __('transactions', 'wpsc'));

  
  $replace_values[":theme:"] = get_option('wpsc_selected_theme');
  $replace_values[":versionnumber:"] = WPSC_PRESENTABLE_VERSION;
	if (function_exists('add_object_page')) {
		$output="";	
		$output.="<div id='dashboard_right_now' class='postbox ".((array_search('dashboard_right_now', $hidden) !== false) ? 'closed' : '')."'>";
		$output.="	<h3 class='hndle'>";
		$output.="		<span>".__('Current Month', 'wpsc')."</span>";
		$output.="		<br class='clear'/>";
		$output.="	</h3>";
		
		$output .= "<div class='inside'>";
		$output .= "<div class='table'>";
		$output .= "<p class='sub'>".__('At a Glance', 'wpsc')."</p>";
		//$output.="<p class='youhave'>".__('You have <a href='admin.php?page=wpsc-edit-products'>:productcount:</a>, contained within <a href='admin.php?page=wpsc-edit-groups'>:groupcount:</a>. This month you made :salecount: and generated a total of :monthtotal: and your total sales ever is :overaltotal:. You have :pendingcount: awaiting approval.', 'wpsc')."</p>";
		$output .= "<table style='border-top:1px solid #ececec;'>";
		$output .= "<tr class='first'>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=wpsc-edit-products'>".$product_count."</a>";
		$output .= "</td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($product_unit);
		$output .= "</td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=wpsc-sales-logs'>".$sales_count."</a>";
		$output .= "</td>";
		$output .= "<td class='last'>";
		$output .= ucfirst($sales_unit);
		$output .= "</td>";
		$output .= "</tr>";
		
		$output .= "<tr>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=wpsc-edit-groups'>".$group_count."</a>";
		$output .= "</td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($group_unit);
		$output .= "</td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=wpsc-sales-logs'>".$pending_sales."</a>";
		$output .= "</td>";
		$output .= "<td class='last t waiting'>".__('Pending', 'wpsc')." ";
		$output .= ucfirst($pending_sales_unit);
		$output .= "</td>";
		$output .= "</tr>";
		
		$output .= "<tr>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=wpsc-edit-variations'>".$variation_count."</a>";
		$output .= "</td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($variation_unit);
		$output .= "</td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=wpsc-sales-logs'>".$accept_sales."</a>";
		$output .= "</td>";
		$output .= "<td class='last t approved'>".__('Closed', 'wpsc')." ";
		$output .= ucfirst($accept_sales_unit);
		$output .= "</td>";
		$output .= "</tr>";
		
		$output .= "</table>";
		$output .= "</div>";
		$output .= "<div class='versions'>";
		$output .= "<p><a class='button rbutton' href='admin.php?page=wpsc-edit-products'><strong>".__('Add New Product', 'wpsc')."</strong></a>".__('Here you can add products, groups or variations', 'wpsc')."</p>";
		$output .= "</div>";
		$output .= "</div>";
		$output.="</div>";
	} else {  
		$output="";	
		$output.="<div id='rightnow'>\n\r";
		$output.="	<h3 class='reallynow'>\n\r";
		$output.="		<a class='rbutton' href='admin.php?page=wpsc-edit-products'><strong>".__('Add New Product', 'wpsc')."</strong></a>\n\r";
		$output.="		<span>"._('Right Now')."</span>\n\r";
		
		//$output.="		<br class='clear'/>\n\r";
		$output.="	</h3>\n\r";
		
		$output.="<p class='youhave'>".__('You have <a href="admin.php?page=wpsc-edit-products">:productcount:</a>, contained within <a href="admin.php?page=wpsc-edit-groups">:groupcount:</a>. This month you made :salecount: and generated a total of :monthtotal: and your total sales ever is :overaltotal:. You have :pendingcount: awaiting approval.', 'wpsc')."</p>\n\r";
		$output.="	<p class='youare'>\n\r";
		$output.="		".__('You are using the :theme: style. This is WP e-Commerce :versionnumber:.', 'wpsc')."\n\r";
		//$output.="		<a class='rbutton' href='themes.php'>Change Theme</a>\n\r";
		//$output.="<span id='wp-version-message'>This is WordPress version 2.6. <a class='rbutton' href='http://wordpress.org/download/'>Update to 2.6.1</a></span>\n\r";
		$output.="		</p>\n\r";
		$output.="</div>\n\r";
		$output.="<br />\n\r";
		$output = str_replace(array_keys($replace_values), array_values($replace_values),$output);
	}
	
	return $output;
}


function wpsc_packing_slip($purchase_id) {
  global $wpdb, $purchlogitem, $wpsc_cart,$purchlog;
  if(isset($_REQUEST['purchaselog_id'])){
	$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
  }

	$purch_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='".$purchase_id."'";
		$purch_data = $wpdb->get_row($purch_sql,ARRAY_A) ;
			

	  //echo "<p style='padding-left: 5px;'><strong>".__('Date', 'wpsc')."</strong>:".date("jS M Y", $purch_data['date'])."</p>";

		$cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase_id."";
		$cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
		$j = 0;
	
		if($cart_log != null) {
      echo "<div class='packing_slip'>\n\r";
			echo apply_filters( 'wpsc_packing_slip_header', '<h2>' . __( 'Packing Slip', 'wpsc' ) . "</h2>\n\r" );
			echo "<strong>".__('Order', 'wpsc')." #</strong> ".$purchase_id."<br /><br />\n\r";
			
			echo "<table>\n\r";
	/*
		
			$form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE  `log_id` = '".(int)$purchase_id."'";
			$input_data = $wpdb->get_results($form_sql,ARRAY_A);
	
*/		
			echo "<tr class='heading'><td colspan='2'><strong>Billing Info</strong></td></tr>";
			foreach((array)$purchlogitem->userinfo as $userinfo){
				if($userinfo['unique_name'] != 'billingcountry'){
					echo "<tr><td>".$userinfo['name'].": </td><td>".$userinfo['value']."</td></tr>";
				}else{
					$userinfo['value'] = maybe_unserialize($userinfo['value']);
					if(is_array($userinfo['value'] )){
						if(!empty($userinfo['value'][1]) && !is_numeric($userinfo['value'][1])){
							echo "<tr><td>State: </td><td>".$userinfo['value'][1]."</td></tr>";
						}elseif(is_numeric($userinfo['value'][1])){
							echo "<tr><td>State: </td><td>".wpsc_get_state_by_id($userinfo['value'][1],'name')."</td></tr>";
						}
						if(!empty($userinfo['value'][0])){
							echo "<tr><td>Country: </td><td>".$userinfo['value'][0]."</td></tr>";
						}
					}else{
						echo "<tr><td>".$userinfo['name'].": </td><td>".$userinfo['value']."</td></tr>";	
					}
				}
			}
			
			echo "<tr class='heading'><td colspan='2'><strong>Shipping Info</strong></td></tr>";
			foreach((array)$purchlogitem->shippinginfo as $userinfo){
				if($userinfo['unique_name'] != 'shippingcountry' && $userinfo['unique_name'] != 'shippingstate'){
					echo "<tr><td>".$userinfo['name'].": </td><td>".$userinfo['value']."</td></tr>";
				}elseif($userinfo['unique_name'] == 'shippingcountry'){
					$userinfo['value'] = maybe_unserialize($userinfo['value']);
					if(is_array($userinfo['value'] )){
						if(!empty($userinfo['value'][1]) && !is_numeric($userinfo['value'][1])){
							echo "<tr><td>State: </td><td>".$userinfo['value'][1]."</td></tr>";
						}elseif(is_numeric($userinfo['value'][1])){
							echo "<tr><td>State: </td><td>".wpsc_get_state_by_id($userinfo['value'][1],'name')."</td></tr>";
						}
						if(!empty($userinfo['value'][0])){
							echo "<tr><td>Country: </td><td>".$userinfo['value'][0]."</td></tr>";
						}
					}else{
						echo "<tr><td>".$userinfo['name'].": </td><td>".$userinfo['value']."</td></tr>";	
					}
				}elseif($userinfo['unique_name'] == 'shippingstate'){
					if(!empty($userinfo['value']) && !is_numeric($userinfo['value'])){
						echo "<tr><td>".$userinfo['name'].": </td><td>".$userinfo['value']."</td></tr>";
					}elseif(is_numeric($userinfo['value'])){
							echo "<tr><td>State: </td><td>".wpsc_get_state_by_id($userinfo['value'],'name')."</td></tr>";
					}
				}
			}
	//		echo('<pre>'.print_r($purchlogitem,true).'</pre>');
			
		/*
	foreach($input_data as $input_row) {
			  $rekeyed_input[$input_row['form_id']] = $input_row;
			}
			
			
			if($input_data != null) {
        $form_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'",ARRAY_A);
    // exit('<pre>'.print_r($purch_data, true).'</pre>');
        foreach($form_data as $form_field) {
          switch($form_field['type']) {
			case 'country':

						$delivery_region_count = $wpdb->get_var("SELECT COUNT(`regions`.`id`) FROM `".WPSC_TABLE_REGION_TAX."` AS `regions` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `country`.`id` = `regions`.`country_id` WHERE `country`.`isocode` IN('".$wpdb->escape( $purch_data['billing_country'])."')");

            if(is_numeric($purch_data['billing_region']) && ($delivery_region_count > 0)) {
              echo "  <tr><td>".__('State', 'wpsc').":</td><td>".wpsc_get_region($purch_data['billing_region'])."</td></tr>\n\r";
            }
            echo "  <tr><td>".wp_kses($form_field['name'], array() ).":</td><td>".wpsc_get_country($purch_data['billing_country'])."</td></tr>\n\r";
            break;
                
            case 'delivery_country':
            echo "  <tr><td>".$form_field['name'].":</td><td>".wpsc_get_country($purch_data['shipping_country'])."</td></tr>\n\r";
            break;
                
            case 'heading':
            echo "  <tr><td colspan='2'><strong>".wp_kses($form_field['name'], array()).":</strong></td></tr>\n\r";
            break;
            
            default:
            if($form_field['unique_name'] == 'shippingstate'){
            	echo "  <tr><td>".wp_kses($form_field['name'], array() ).":</td><td>".wpsc_get_region($purch_data['shipping_region'])."</td></tr>\n\r";
            }else{
            	echo "  <tr><td>".wp_kses($form_field['name'], array() ).":</td><td>".htmlentities(stripslashes($rekeyed_input[$form_field['id']]['value']), ENT_QUOTES,'UTF-8')."</td></tr>\n\r";
            }
            break;
          }
        }
			} else {
        echo "  <tr><td>".__('Name', 'wpsc').":</td><td>".$purch_data['firstname']." ".$purch_data['lastname']."</td></tr>\n\r";
        echo "  <tr><td>".__('Address', 'wpsc').":</td><td>".$purch_data['address']."</td></tr>\n\r";
        echo "  <tr><td>".__('Phone', 'wpsc').":</td><td>".$purch_data['phone']."</td></tr>\n\r";
        echo "  <tr><td>".__('Email', 'wpsc').":</td><td>".$purch_data['email']."</td></tr>\n\r";
			}
*/
			
			if(get_option('payment_method') == 2) {
				$gateway_name = '';
				foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
					if($purch_data['gateway'] != 'testmode') {
						if($gateway['internalname'] == $purch_data['gateway'] ) {
							$gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
					}
				}
			}
// 			echo "  <tr><td colspan='2'></td></tr>\n\r";
// 			echo "  <tr><td>".__('Payment Method', 'wpsc').":</td><td>".$gateway_name."</td></tr>\n\r";
// 			//echo "  <tr><td>".__('Purchase No.', 'wpsc').":</td><td>".$purch_data['id']."</td></tr>\n\r";
// 			echo "  <tr><td>".__('How The Customer Found Us', 'wpsc').":</td><td>".$purch_data['find_us']."</td></tr>\n\r";
// 			$engrave_line = explode(",",$purch_data['engravetext']);
// 			echo "  <tr><td>".__('Engrave text', 'wpsc')."</td><td></td></tr>\n\r";
// 			echo "  <tr><td>".__('Line 1', 'wpsc').":</td><td>".$engrave_line[0]."</td></tr>\n\r";
// 			echo "  <tr><td>".__('Line 2', 'wpsc').":</td><td>".$engrave_line[1]."</td></tr>\n\r";
// 			if($purch_data['transactid'] != '') {
// 				echo "  <tr><td>".__('Transaction Id', 'wpsc').":</td><td>".$purch_data['transactid']."</td></tr>\n\r";
// 			}
			echo "</table>\n\r";
			
			
			
			
      echo "<table class='packing_slip'>";
				
				echo "<tr>";
				echo " <th>".__('Quantity', 'wpsc')." </th>";
				
				echo " <th>".__('Name', 'wpsc')."</th>";
				
				
				echo " <th>".__('Price', 'wpsc')." </th>";
				
				echo " <th>".__('Shipping', 'wpsc')." </th>";
				echo "<th>".wpsc_display_tax_label(false)."</th>";
				echo '</tr>';
			$endtotal = 0;
			$all_donations = true;
			$all_no_shipping = true;
			$file_link_list = array();
//			exit('<pre>'.print_r($cart_log,true).'</pre>');
			foreach($cart_log as $cart_row) {
			$purchlogitem->the_purch_item();
//			exit('<pre>'.print_r, true).'</pre>');
				$alternate = "";
				$j++;
				if(($j % 2) != 0) {
					$alternate = "class='alt'";
        }
				$productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$cart_row['prodid']."";
				$product_data = $wpdb->get_results($productsql,ARRAY_A); 
			
			
			
				$variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$cart_row['id']."'";
				$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
				$variation_count = count($variation_data);
				
				if($variation_count > 1) {
					$variation_list = " (";
					$i = 0;
					foreach($variation_data as $variation) {
						if($i > 0) {
							$variation_list .= ", ";
            }
						$value_id = $variation['value_id'];
						$value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
						$variation_list .= $value_data[0]['name'];
						$i++;
          }
					$variation_list .= ")";
        } else if($variation_count == 1) {
          $value_id = $variation_data[0]['value_id'];
          $value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          $variation_list = " (".$value_data[0]['name'].")";
        } else {
							$variation_list = '';
        }
				
				
				if($cart_row['donation'] != 1) {
					$all_donations = false;
				}
				if($cart_row['no_shipping'] != 1) {
					$shipping = $cart_row['pnp'] * $cart_row['quantity'];
					$total_shipping += $shipping;            
					$all_no_shipping = false;
				} else {
					$shipping = 0;
				}
				
				$price = $cart_row['price'] * $cart_row['quantity'];
				$gst = $price - ($price  / (1+($cart_row['gst'] / 100)));
				
				if($gst > 0) {
				  $tax_per_item = $gst / $cart_row['quantity'];
				}


				echo "<tr $alternate>";
		
		
				echo " <td>";
				echo $cart_row['quantity'];
				echo " </td>";
				
				echo " <td>";
				echo $product_data[0]['name'];
				echo stripslashes($variation_list);
				echo " </td>";
				
				
				echo " <td>";
				echo nzshpcrt_currency_display( $price, 1);
				echo " </td>";
				
				echo " <td>";
				echo nzshpcrt_currency_display($shipping, 1);
				echo " </td>";
							
	

				echo '<td>';
				if(wpsc_tax_isincluded()){
					echo (wpsc_purchaselog_details_tax());
				}else{
					echo nzshpcrt_currency_display($cart_row['tax_charged'],1);
				}
				echo '<td>';
				echo '</tr>';
			}
			echo "</table>";
			
			echo '<table class="packing-slip-totals">';
			echo '<tr><th>Base Shipping</th><td>' . nzshpcrt_currency_display( $purch_data['base_shipping'], 1 ) . '</td></tr>';
			echo '<tr><th>Total Shipping</th><td>' . nzshpcrt_currency_display( $purch_data['base_shipping'] + $total_shipping, 1 ) . '</td></tr>';
			echo '<tr><th>Total Price</th><td>' . nzshpcrt_currency_display( $purch_data['totalprice'], 1 ) . '</td></tr>';
			echo '</table>';

			echo "</div>\n\r";
		} else {
			echo "<br />".__('This users cart was empty', 'wpsc');
		}

}


    


function wpsc_product_item_row() {
}

?>
