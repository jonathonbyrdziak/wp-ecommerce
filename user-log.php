<?php
global $wpdb, $user_ID;
if(get_option('permalink_structure') != '') {
  $seperator ="?";
} else {
  $seperator ="&amp;";
}
$siteurl = get_option('siteurl'); 

if($_GET['edit_profile'] == 'true') {
  require('edit-profile.php');
}  else if ($_GET['downloads'] == 'true'){
	require('user-downloads.php');
} else {
/*
 * this finds the earliest timedit-profile in the shopping cart and sorts out the timestamp system for the month by month display
 */  
$sql = "SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date`!='' ORDER BY `date` DESC";
$purchase_count= $wpdb->get_results($sql,ARRAY_A) ;

$earliest_record_sql = "SELECT MIN(`date`) AS `date` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date`!=''";
$earliest_record = $wpdb->get_results($earliest_record_sql,ARRAY_A) ;

$current_timestamp = time();
$earliest_timestamp = $earliest_record[0]['date'];

$current_year = date("Y");
$earliest_year = date("Y",$earliest_timestamp);


$date_list[0]['start'] = $start_timestamp;
$date_list[0]['end'] = $end_timestamp;
?>
<div class="wrap" style=''>
<?php
  if(is_numeric($user_ID) && ($user_ID > 0)) {
    echo " <div class='user-profile-links'><a href='".get_option('user_account_url')."'>Purchase History</a> | <a href='".get_option('user_account_url').$seperator."edit_profile=true'>Your Details</a> | <a href='".get_option('user_account_url').$seperator."downloads=true'>Your Downloads</a></div><br />";
    if(($purchase_log == null) && !is_numeric($_GET['purchaseid'])) {
      if($earliest_record[0]['date'] != null) {
        $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
        $col_count = 4 + count($form_data);
        echo "<table class='logdisplay'>";    
        $sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `user_ID` IN ('".$user_ID."') ORDER BY `date` DESC";
        $purchase_log = $wpdb->get_results($sql,ARRAY_A) ;

        $i = 0;
        $subtotal = 0;
        if($purchase_log != null) {
          echo "<tr class='toprow'>";
          
          echo " <td>";
          echo __('Status', 'wpsc');
          echo " </td>";
            
          echo " <td>";
          echo __('Date', 'wpsc');
          echo " </td>";
            
          echo " <td>";
          echo __('Price', 'wpsc');
          echo " </td>";  
          
          if(get_option('payment_method') == 2) {
            echo " <td>";
            echo __('Payment Method', 'wpsc');
            echo " </td>";  
          }
        
          echo "</tr>";
      
          foreach((array)$purchase_log as $purchase) {
            $status_state = "expand";
            $status_style = "";
            $alternate = "";
            $i++;
            if(($i % 2) != 0) {
              $alternate = "class='alt'";
            }
            echo "<tr $alternate>\n\r";
            //  echo " <td>";
            //  echo $purchase['id'];
            //  echo " </td>";
            
            echo " <td class='processed'>";
            echo "<a href='#' onclick='return show_details_box(\"status_box_".$purchase['id']."\",\"log_expander_icon_".$purchase['id']."\");'>";
            if($_GET['id'] == $purchase['id']) {
              $status_state = "collapse";
              $status_style = "style='display: block;'";
            }
            echo "<img class='log_expander_icon' id='log_expander_icon_".$purchase['id']."' src='".WPSC_URL."/images/icon_window_$status_state.gif' alt='' title='' />";
            if($stage_data['colour'] != '') {
              $colour = "style='color: #".$stage_data['colour'].";'";
            }
            echo "<span id='form_group_".$purchase['id']."_text'>".__('Details', 'wpsc')."</span>";
            echo "</a>";
            echo " </td>\n\r";
      
            echo " <td>";
            echo date("jS M Y",$purchase['date']);
            echo " </td>\n\r";
      
            echo " <td>";

            if($purchase['shipping_country'] != '') {
              $billing_country = $purchase['billing_country'];
              $shipping_country = $purchase['shipping_country'];
            } else {
              $country_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
              $country_data = $wpdb->get_results($country_sql,ARRAY_A);
              $billing_country = $country_data[0]['value'];
              $shipping_country = $country_data[0]['value'];
            }
            echo nzshpcrt_currency_display($purchase['totalprice'],1);
            $subtotal += $purchase['totalprice'];
            echo " </td>\n\r";
      
            
            if(get_option('payment_method') == 2) {
              echo " <td>";
              $gateway_name = '';
              foreach((array)$GLOBALS['nzshpcrt_gateways'] as $gateway) {
                if($purchase['gateway'] != 'testmode') {
                  if($gateway['internalname'] == $purchase['gateway'] ) {
                    $gateway_name = $gateway['name'];
                  }
                } else {
                  $gateway_name = "Manual Payment";
                }
              }
              echo $gateway_name;
              echo " </td>\n\r";
            }
        
            echo "</tr>\n\r";
            
            //$stage_list_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_STATUSES."` ORDER BY `id` ASC";
            //$stage_list_data = $wpdb->get_results($stage_list_sql,ARRAY_A);

            echo "<tr>\n\r";
            echo " <td colspan='$col_count' class='details'>\n\r";
            echo "  <div id='status_box_".$purchase['id']."' class='order_status' $status_style>\n\r";
            echo "  <div>\n\r";

            //order status code lies heret
            $stage_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_STATUSES."` WHERE `id`='".$purchase['processed']."' AND `active`='1' LIMIT 1";
            $stage_data = $wpdb->get_row($stage_sql,ARRAY_A);
            echo "  <strong class='form_group'>".__('Order Status', 'wpsc').":</strong>\n\r";
            echo $stage_data['name']."<br /><br />";

           //written by allen
					$usps_id = get_option('usps_user_id');
					if ($usps_id!=null) {
						$XML1 = "<TrackFieldRequest USERID=\"$usps_id\"><TrackID ID=\"".$purchase['track_id']."\"></TrackID></TrackFieldRequest>";
						//eecho cho  "--->".$purchase['track_id'];
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, "http://secure.shippingapis.com/ShippingAPITest.dll?");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						$postdata = "API=TrackV2&XML=".$XML1;
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
				// 		$result = curl_exec($ch);

						$parser = new xml2array;
						$parsed = $parser->parse($result);
						$parsed = $parsed[0]['children'][0]['children'];
						if($purchase['track_id'] != null){
							echo "<br /><br />";
							echo " <strong class='form_group'>".__('Shipping Details', 'wpsc')."</strong>\n\r";
							echo "<table>";
							foreach((array)$parsed as $parse){
								if ($parse['name'] == "TRACKSUMMARY")
								foreach((array)$parse['children'] as $attrs){
									if ($attrs['name']!="EVENT")
									$attrs['name'] = str_replace("EVENT", "", $attrs['name']);
									$bar = ucfirst(strtolower($attrs['name']));
									echo "<tr><td>".$bar."</td><td>".$attrs['tagData']."</td></tr>";
								}
							}
							echo "</table>";
						}
					echo "<br /><br />";
					}
			//end of written by allen


	     //cart contents display starts here;
            echo "  <strong class='form_group'>".__('Order Details', 'wpsc').":</strong>\n\r";
            $cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase['id']."";
            $cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
            $j = 0;

            // /*
            if($cart_log != null)
              {
              echo "<table class='logdisplay'>";
              echo "<tr class='toprow2'>";

              echo " <td>";
              echo __('Name', 'wpsc');
              echo " </td>";
      
              echo " <td>";
              echo __('Quantity', 'wpsc');
              echo " </td>";
              
              echo " <td>";
              echo __('Price', 'wpsc');
              echo " </td>";
      
              echo " <td>";
              echo __('GST', 'wpsc');
              echo " </td>";
      
              echo " <td>";
              echo __('P&amp;P', 'wpsc');
              echo " </td>";
      
              echo " <td>";
              echo __('Total', 'wpsc');
              echo " </td>";
      
              echo "</tr>";
              $endtotal = 0;
              foreach((array)$cart_log as $cart_row) {
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
                  foreach((array)$variation_data as $variation) {
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
                
                                
                if($purch_data[0]['shipping_country'] != '') {
                  $billing_country = $purch_data[0]['billing_country'];
                  $shipping_country = $purch_data[0]['shipping_country'];
								} else {
									$country_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
									$country_data = $wpdb->get_results($country_sql,ARRAY_A);
									$billing_country = $country_data[0]['value'];
									$shipping_country = $country_data[0]['value'];
								}
                
                $shipping = $cart_row['pnp'];
                $total_shipping += $shipping;
                echo "<tr $alternate>";
            
                echo " <td>";
                echo $product_data[0]['name'];
                echo $variation_list;
                echo " </td>";
            
                echo " <td>";
                echo $cart_row['quantity'];
                echo " </td>";
            
                echo " <td>";
                $price = $cart_row['price'] * $cart_row['quantity'];
                echo nzshpcrt_currency_display($price, 1);
                echo " </td>";
            
                echo " <td>";
                $gst = $cart_row['tax_charged'];
                $endtotal += $gst * $cart_row['quantity'];
                
                echo nzshpcrt_currency_display($gst, 1);
                echo " </td>";
            
                echo " <td>";
                echo nzshpcrt_currency_display($shipping, 1);
                echo " </td>";
            
                echo " <td>";
                $endtotal += $price;
                echo nzshpcrt_currency_display(($shipping + $price + ($gst * $cart_row['quantity'])), 1);
                echo " </td>";
                      
                echo '</tr>';
							}
              echo "<tr >";
            
              echo " <td>";
              echo " </td>";
          
              echo " <td>";
              echo " </td>";
          
              echo " <td>";
              echo " </td>";
          
              echo " <td>";
              echo "<strong>".__('Total Shipping', 'wpsc').":</strong><br />";    
              echo "<strong>".__('Final Total', 'wpsc').":</strong>";
              echo " </td>";
          
              echo " <td>";
              $total_shipping += $purchase['base_shipping'];
              $endtotal += $total_shipping;    
              echo nzshpcrt_currency_display($total_shipping, 1) . "<br />";
              echo nzshpcrt_currency_display($endtotal,1);
              echo " </td>";
                    
              echo '</tr>';
              
              echo "</table>";
              echo "<br />";
              
              
      
              
              echo "<strong>".__('Customer Details', 'wpsc').":</strong>";
              echo "<table class='customer_details'>";
              $form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE  `log_id` = '".$purchase['id']."'";
              $input_data = $wpdb->get_results($form_sql,ARRAY_A);
              //exit("<pre>".print_r($input_data,true)."</pre>");
              if($input_data != null) {
                foreach((array)$input_data as $form_field) {
                  $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `id` = '".$form_field['form_id']."' LIMIT 1";
                  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
                  if($form_data != null) {
                    $form_data = $form_data[0];
                    if($form_data['type'] == 'country' ) {
                      if($form_field['value'] != null) {
                        echo "  <tr><td>".$form_data['name'].":</td><td>".wpsc_get_country($form_field['value'])."</td></tr>";
											} else {
												echo "  <tr><td>".$form_data['name'].":</td><td>".wpsc_get_country($purchase['shipping_country'])."</td></tr>";
											}
										} else {
											echo "  <tr><td>".$form_data['name'].":</td><td>".$form_field['value']."</td></tr>";
										}
									}
								}
							} else {
								echo "  <tr><td>".__('Name', 'wpsc').":</td><td>".$purchase['firstname']." ".$purchase['lastname']."</td></tr>";
								echo "  <tr><td>".__('Address', 'wpsc').":</td><td>".$purchase['address']."</td></tr>";
								echo "  <tr><td>".__('Phone', 'wpsc').":</td><td>".$purchase['phone']."</td></tr>";
								echo "  <tr><td>".__('Email', 'wpsc').":</td><td>".$purchase['email']."</td></tr>";
							}
              
              //if(get_option('payment_method') == 2)
                //{
                $gateway_name = '';
                foreach((array)$GLOBALS['nzshpcrt_gateways'] as $gateway)
                  {
                  if($purchase_log[0]['gateway'] != 'testmode')
                    {
                    if($gateway['internalname'] == $purchase_log[0]['gateway'] )
                      {
                      $gateway_name = $gateway['name'];
                      }
                    }
                    else
                      {
                      $gateway_name = "Manual Payment";
                      }
                  }
                //}
              echo "  <tr><td>".__('Payment Method', 'wpsc').":</td><td>".$gateway_name."</td></tr>";
              echo "  <tr><td>".__('Purchase No.', 'wpsc').":</td><td>".$purchase['id']."</td></tr>";
              if($purchase['transactid'] != '')
                {
                echo "  <tr><td>".__('Transaction Id', 'wpsc').":</td><td>".$purchase['transactid']."</td></tr>";
                }
              echo "</table>";
              } // */           
            echo "  </div>\n\r";
            echo "  </div>\n\r";
            echo " </td>\n\r";
            echo "</tr>\n\r";
            }
          }
          else
            {
            echo "<tr>";
            echo " <td colspan='$col_count'>";
            echo __('No transactions for this month.', 'wpsc');
            echo " </td>";      
            echo "</tr>";
            }
        echo " </table>";
        }
        else
          {
          echo " <table>"; 
          echo "<tr>";
          echo " <td>";     
          echo __('There have not been any purchases yet.', 'wpsc');
          echo " </td>";      
          echo "</tr>";
          echo " </table>";
          }
      }
			
			// Commented out as this seemed to be breaking the Your Account layout.
			// Doesn't seem to do anything, does it?
			//$sql = "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `date` != ''";
			//$purchase_log = $wpdb->get_results( $sql, ARRAY_A );
			
    }
   else
     {
     echo __('You must be logged in to use this page. Please use the form below to login to your account.', 'wpsc');
     ?>
     <form name="loginform" id="loginform" action="<?php echo $siteurl; ?>/wp-login.php" method="post"><br />
<label>Username:<br /><input type="text" name="log" id="log" value="" size="20" tabindex="1" /></label><br />
<label>Password:<br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label>
<p>
  <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="3" /> 
  Remember me</label></p>
<p class="submit">
  <input type="submit" name="submit" id="submit" value="Login &raquo;" tabindex="4" />

  <input type="hidden" name="redirect_to" value="<?php echo get_option('user_account_url'); ?>" />
</p>
</form>
			<?php
		}
		?>
	</div>
<?php } ?>
