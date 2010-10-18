<?php /*
class nzpost {
	var $internal_name, $name;
	function nzpost () {

		$this->internal_name = "nzpost";
		$this->name="NZ Post";
		$this->is_external=true;
		$this->requires_curl=true;
		$this->requires_weight=false;
		$this->needs_zipcode=false;
		$this->xml2Array;

		return true;
	}
	
	function getId() {
// 		return $this->usps_id;
	}
	
	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
		$output = "<label for='wpsc_nzpost_trackAPI'>Tracking API: </label>";
		$output .= "<input type='text' value='".get_option('wpsc_nzpost_trackAPI')."' name='wpsc_nzpost_trackAPI' />";
		return $output;
	}
	
	function submit_form() {
		global $wpdb;
		if ($_POST['wpsc_nzpost_trackAPI'] != '') {
			$value = $wpdb->escape($_POST['wpsc_nzpost_trackAPI']);
			update_option('wpsc_nzpost_trackAPI', $value);
		}
		return true;
	}
	function getStatus($trackid){
		require_once(WPSC_FILE_PATH."/wpsc-includes/xmlparser.php");
		$url = 'http://services.nzpost.co.nz/TrackAndTrace.svc/TrackID/';
		$nzposttrackAPI = get_option('wpsc_nzpost_trackAPI');
		$version = '10.1.2.3';
		$trackid = 'JB101069625NZ';
		$url = $url.$nzposttrackAPI.'/'.$version.'/'.$trackid;
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$Results=curl_exec($ch);
			
		curl_close($ch);
		$parser = new xml2array;
		$parsed = $parser->parse($Results);
		$_SESSION['wpsc_nzpost_parsed'] = $parsed;
		$this->xml2Array = $parsed;
		return 	$parsed[0]['children'][0]['children'][2]['tagData'];
	}
	function getMethod() {

	}

	function getQuote() {

	}
	
	function get_item_shipping() {
	}
}
$nzpost = new nzpost();
$wpsc_shipping_modules[$nzpost->getInternalName()] = $nzpost;*/
?>