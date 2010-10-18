<?php
class usps {
	var $usps_id, $usps_password, $internal_name, $name;
	function usps () {
		$this->internal_name = "usps";
		$this->name="USPS";
		$this->is_external=true;
		$this->requires_curl=true;
		$this->needs_zipcode=true;
		return true;
	}
	
	function getId() {
		return $this->usps_id;
	}
	
	function setId($id) {
		$usps_id = $id;
		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
		$checked = '';
		if(get_option("usps_test_server") == '1'){
			$checked = 'checked = "checked"';
		}
		$output="<tr>
					<td>
						".__('USPS ID', 'wpsc').":
					</td>
					<td>
						<input type='text' name='uspsid' value='".get_option("uspsid")."' />
					</td>
				</tr>
				<tr>
					<td>
						".__('USPS Password', 'wpsc').":
					</td>
					<td>
						<input type='text' name='uspspw' value='".get_option("uspspw")."' />
					</td>
				</tr>
				<tr>
					<td>
						".__('Use Test Server:','wpsc')."
					</td>
					<td>
						<input type='checkbox' ".$checked." name='usps_test_server' value='1' />
					</td>
				</tr>
			
				";
		return $output;
	}
	
	function submit_form() {	
	//	exit('<pre>'.print_r($_POST, true).'</pre>');
		if ($_POST['uspsid'] != '') {
			update_option('uspsid', $_POST['uspsid']);
		}
		if ($_POST['uspspw'] != '') {
			update_option('uspspw', $_POST['uspspw']);
		}
		if($_POST['usps_test_server'] != ''){
			update_option('usps_test_server', $_POST['usps_test_server']);
		}else{
			update_option('usps_test_server', '');
		}
		return true;
	}
	
	function getQuote() {
		global $wpdb, $wpsc_usps_quote;
		if(isset($wpsc_usps_quote) && (count($wpsc_usps_quote)> 0)) {
		  return $wpsc_usps_quote;
		}
    if(isset($_POST['zipcode'])) {
      $zipcode = $_POST['zipcode'];      
      $_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
    } else if(isset($_SESSION['wpsc_zipcode'])) {
      $zipcode = $_SESSION['wpsc_zipcode'];
    }else{
    	$zipcode = get_option('base_zipcode');
    }
		$dest = $_SESSION['wpsc_delivery_country'];
		$weight = wpsc_cart_weight_total();
		$pound = floor($weight);
		$ounce = ($weight-$pound)*16;
		$machinable = 'true';
		if (($ounce > 13) || ($pound > 1)) {
			define('MODULE_SHIPPING_USPS_TYPES', "PRIORITY, EXPRESS, PARCEL POST");
		} else {
			define('MODULE_SHIPPING_USPS_TYPES', "FIRST CLASS, PRIORITY, EXPRESS, PARCEL POST");
		}
		
		if (($dest =='US') && ('US'== get_option('base_country'))) {
			$request  = '<RateV3Request USERID="' . get_option('uspsid') . '" PASSWORD="' . get_option('uspspw') . '">';
			$allowed_types = explode(", ", MODULE_SHIPPING_USPS_TYPES);
			$types = array("FIRST CLASS" => 0,"PRIORITY" => 0,"EXPRESS" => 0,"PARCEL POST" => 0);
			while (list($key, $value) = each($types)) {
				 if ( !in_array($key, $allowed_types) ) continue;
				if ($key == 'FIRST CLASS'){
					$FirstClassMailType = '<FirstClassMailType>LETTER</FirstClassMailType>';
				} else {
					$FirstClassMailType = '';
				}

				if ($key == 'PRIORITY'){
					$container = 'FLAT RATE ENVELOPE';
				}

				if ($key == 'EXPRESS'){
					$container = 'FLAT RATE ENVELOPE';
				}

				if ($key == 'PARCEL POST'){
					$container = 'REGULAR';
					$machinable = 'false';
					$size =  'REGULAR';
				}
				$pound = round($pound,2);
				$ounce = round($ounce,2);
				$request .= '<Package ID="1">' .
				'<Service>' . $key . '</Service>' .
				$FirstClassMailType .
				'<ZipOrigination>' . get_option("base_zipcode") . '</ZipOrigination>' .
				'<ZipDestination>' . $zipcode . '</ZipDestination>' .
				'<Pounds>' . $pound . '</Pounds>' .
				'<Ounces>' . $ounce . '</Ounces>' .
				'<Container>' . $container . '</Container>' .
				'<Size>' . $size . '</Size>' .
				'<Machinable>' . $machinable . '</Machinable>' .
				'</Package>';

 				if ($transit) {
 					$transitreq  = 'USERID="' . MODULE_SHIPPING_USPS_USERID .
 					 '" PASSWORD="' . MODULE_SHIPPING_USPS_PASSWORD . '">' .
 					 '<OriginZip>' . STORE_ORIGIN_ZIP . '</OriginZip>' .
 					 '<DestinationZip>' . $dest_zip . '</DestinationZip>';
 
 					switch ($key) {
 						case 'EXPRESS':  $transreq[$key] = 'API=ExpressMail&XML=' .
 							urlencode( '<ExpressMailRequest ' . $transitreq . '</ExpressMailRequest>');
 							break;
 						case 'PRIORITY': $transreq[$key] = 'API=PriorityMail&XML=' .
 							urlencode( '<PriorityMailRequest ' . $transitreq . '</PriorityMailRequest>');
 							break;
 						case 'PARCEL':   $transreq[$key] = 'API=StandardB&XML=' .
 							urlencode( '<StandardBRequest ' . $transitreq . '</StandardBRequest>');
 							break;
 						default: $transreq[$key] = '';
 						break;
 					}
				}
				$services_count++;
			}
			$request .= '</RateV3Request>'; //'</RateRequest>'; //Changed by Greg Deeth April 30, 2008
			//exit($request);
			$request = 'API=RateV3&XML=' . urlencode($request);
		} else {
			$dest=$wpdb->get_var("SELECT country FROM ".WPSC_TABLE_CURRENCY_LIST." WHERE isocode='".$dest."'");
			if($dest == 'U.K.'){
				$dest = 'Great Britain and Northern Ireland';
			}

			$pound = round($pound,2);
			$ounce = round($ounce,2);
			$request  = '<IntlRateRequest USERID="' . get_option('uspsid') . '" PASSWORD="' . get_option('uspspw') . '">' .
			'<Package ID="0">' .
			'<Pounds>' . $pound . '</Pounds>' .
			'<Ounces>' . $ounce . '</Ounces>' .
			'<MailType>Package</MailType>' .
			'<Country>' . $dest . '</Country>' .
			'</Package>' .
			'</IntlRateRequest>';
			$request = 'API=IntlRate&XML=' . urlencode($request);
		}
		$usps_server = 'production.shippingapis.com';
		$api_dll = 'shippingAPI.dll';
		if(get_option('usps_test_server') == '1'){
			$url ='http://testing.shippingapis.com/ShippingAPITest.dll?'.$request;
		}else{
			$url = 'http://'.$usps_server.'/' . $api_dll . '?' . $request;
		}
		//exit('URL '.$url);	
		$ch=curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		@ curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 120); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'wp-e-commerce'); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$body = curl_exec($ch);
		//exit('Response:<pre>'.print_r($body, true).'</pre>'.$url);
		curl_close($ch);
		//exit($body);
		$rates=array();
		$response=array();
		while (true) {
			if ($start = strpos($body, '<Package ID=')) {
				$body = substr($body, $start);
				$end = strpos($body, '</Package>');
				$response[] = substr($body, 0, $end+10);
				$body = substr($body, $end+9);
			} else {
				break;
			}
		}
		$rates = array();
		if ($dest == get_option('base_country')) {
			if (sizeof($response) == '1') {
				if (ereg('<Error>', $response[0])) {
					$number = ereg('<Number>(.*)</Number>', $response[0], $regs);
					$number = $regs[1];
					$description = ereg('<Description>(.*)</Description>', $response[0], $regs);
					$description = $regs[1];
					 //return array('error' => $number . ' - ' . $description);
				}
			}

			$n = sizeof($response);
			for ($i=0; $i<$n; $i++) {
				if (strpos($response[$i], '<Rate>')) {
					$service = ereg('<MailService>(.*)</MailService>', $response[$i], $regs);
					$service = $regs[1];
					$postage = ereg('<Rate>(.*)</Rate>', $response[$i], $regs);
					$postage = $regs[1];
					if($postage <= 0) {
					  continue;
					}
					$rates += array($service => $postage);
					if ($transit) {
						switch ($service) {
							case 'EXPRESS':     $time = ereg('<MonFriCommitment>(.*)</MonFriCommitment>', $transresp[$service], $tregs);
								$time = $tregs[1];
								if ($time == '' || $time == 'No Data') {
									$time = 'Estimated 1 - 2 ' . 'Days';
								} else {
									$time = 'Tomorrow by ' . $time;
								}
								break;
							case 'PRIORITY':    $time = ereg('<Days>(.*)</Days>', $transresp[$service], $tregs);
								$time = $tregs[1];
								if ($time == '' || $time == 'No Data') {
									$time = 'Estimated 1 - 3 ' . 'Days';
								} elseif ($time == '1') {
									$time .= ' ' . 'Day';
								} else {
									$time .= ' ' . 'Days';
								}
								break;
							case 'PARCEL':      $time = ereg('<Days>(.*)</Days>', $transresp[$service], $tregs);
								$time = $tregs[1];
								if ($time == '' || $time == 'No Data') {
									$time = 'Estimated 2 - 9 ' . 'Days';
								} elseif ($time == '1') {
									$time .= ' ' . 'Day';
								} else {
									$time .= ' ' . 'Days';
								}
								break;
							case 'First-Class Mail': 
								$time = 'Estimated 1 - 5 ' . 'Days';
								break;
							case 'MEDIA':
								$time = 'Estimated 2 - 9 ' . 'Days';
								break;
							case 'BPM':
								$time = 'Estimated 2 - 9 ' . 'Days';
								break;
							default:
								$time = '';
								break;
						}
						if ($time != '') $transittime[$service] = ': ' . $time . '';
					}
				}
			}
			$wpsc_usps_quote = $rates;
			//return $rates;
		} else {
			if (ereg('<Error>', $response[0])) {
				$number = ereg('<Number>(.*)</Number>', $response[0], $regs);
				$number = $regs[1];
				$description = ereg('<Description>(.*)</Description>', $response[0], $regs);
				$description = $regs[1];
				//return array('error' => $number . ' - ' . $description);
			} else {
				$body = $response[0];
				$services = array();
				while (true) {
					if ($start = strpos($body, '<Service ID=')) {
						$body = substr($body, $start);
						$end = strpos($body, '</Service>');
						$services[] = substr($body, 0, $end+10);
						$body = substr($body, $end+9);
					} else {
						break;
					}
				}
	
				$allowed_types = Array( 'EXPRESS MAIL INT' => "Express Mail International (EMS)", 'EXPRESS MAIL INT FLAT RATE ENV' => "Express Mail International (EMS) Flat-Rate Envelope", 'PRIORITY MAIL INT' => "Priority Mail International", 'PRIORITY MAIL INT FLAT RATE ENV' => "Priority Mail International Flat-Rate Envelope", 'PRIORITY MAIL INT FLAT RATE BOX' => "Priority Mail International Flat-Rate Box", 'FIRST-CLASS MAIL INT' => "First Class Mail International Letters" );
				//foreach( explode(", ", MODULE_SHIPPING_USPS_TYPES_INTL) as $value ) $allowed_types[$value] = $this->intl_types[$value];
			
				$size = sizeof($services);
				for ($i=0, $n=$size; $i<$n; $i++) {
					if (strpos($services[$i], '<Postage>')) {
						$service = ereg('<SvcDescription>(.*)</SvcDescription>', $services[$i], $regs);
						$service = $regs[1];
						$postage = ereg('<Postage>(.*)</Postage>', $services[$i], $regs);
						$postage = $regs[1];
						$time = ereg('<SvcCommitments>(.*)</SvcCommitments>', $services[$i], $tregs);
						$time = $tregs[1];
						$time = preg_replace('/Weeks$/', 'Weeks',$time);
						$time = preg_replace('/Days$/', 'Days', $time);
						$time = preg_replace('/Day$/', 'Day', $time);
						if( !in_array($service, $allowed_types) || ($postage < 0) ) continue;
						$rates += array($service => $postage);
						if ($time != '') $transittime[$service] = ' (' . $time . ')';
					}
				}
				//$uspsQuote=$rates;
			}
		}
		$uspsQuote=$rates;
		$wpsc_usps_quote = $rates;
		//exit('<pre>'.print_r($uspsQuote,true).'</pre>');
		return $uspsQuote;
	}
	
	function get_item_shipping() {
	}
}
$usps = new usps();
$wpsc_shipping_modules[$usps->getInternalName()] = $usps;
?>