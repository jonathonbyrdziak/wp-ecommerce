<?php
$nzshpcrt_gateways[$num]['name'] = 'Manual Payment';
$nzshpcrt_gateways[$num]['admin_name'] = 'Manual Payment / Test Gateway';
$nzshpcrt_gateways[$num]['internalname'] = 'testmode';
$nzshpcrt_gateways[$num]['function'] = 'gateway_testmode';
$nzshpcrt_gateways[$num]['form'] = "form_testmode";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_testmode";
$nzshpcrt_gateways[$num]['payment_type'] = "manual_payment";

function gateway_testmode($seperator, $sessionid) {
  $transact_url = get_option('transact_url');
  // exit("Location: ".$transact_url.$seperator."sessionid=".$sessionid);
  //$_SESSION['nzshpcrt_cart'] = null;
  //$_SESSION['nzshpcrt_serialized_cart'] = null;
  //exit($transact_url.$seperator."sessionid=".$sessionid);
  header("Location: ".$transact_url.$seperator."sessionid=".$sessionid);
  exit();
}

function submit_testmode() {
  return true;
}

function form_testmode() {  
	$output = "<tr>\n\r";
	$output .= "	<td colspan='2'>\n\r";
	// $output = "	</td>\n\r";
	// $output = "	<td>\n\r";
	
	$output .= "<strong>".__('Enter the payment instructions that you wish to display to your customers when they make a purchase', 'wpsc').":</strong><br />\n\r";
	$output .= "<textarea cols='40' rows='9' name='wpsc_options[payment_instructions]'>".get_option('payment_instructions')."</textarea><br />\n\r";
	$output .= "<em>".__('For example, this is where you the Shop Owner might enter your bank account details or address so that your customer can make their manual payment.', 'wpsc')."</em>\n\r";
	$output .= "	</td>\n\r";
	$output .= "</tr>\n\r";
  return $output;
}
?>
