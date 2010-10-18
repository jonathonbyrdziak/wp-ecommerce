<?php
class ups {
	var $internal_name, $name;
        public $service_url = "";
        private $MessageStart = "";
        private $RateRequest = "";
        private $RatePackage = "";
        private $RateCustomPackage = "";
        private $RateRequestEnd = "";
        private $Services = "";

	function ups() {
            $this->internal_name = "ups";
            $this->name="UPS";
            $this->is_external=true;
            $this->requires_curl=true;
            $this->requires_weight=true;
            $this->needs_zipcode=true;
            $this->_includeUPSData();
            $this->_setServiceURL();
            return true;
	}

	function getId() {
// 		return $this->usps_id;
	}

	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}

        private function _setServiceURL(){
            global $wpdb;
            $wpsc_ups_settings = get_option("wpsc_ups_settings");
            $wpsc_ups_environment = $wpsc_ups_settings["upsenvironment"];
            if ($wpsc_ups_environment == "1"){
                $this->service_url = "https://www.ups.com/ups.app/xml/Rate";
            }else{
                $this->service_url = "https://wwwcie.ups.com/ups.app/xml/Rate";
            }
        }

	function getName() {
            return $this->name;
	}

	function getInternalName() {
            return $this->internal_name;
	}

        private function _includeUPSData(){
            list($eol,$Auth,$MessageStart,$RateRequest,
                 $RateService, $RatePackage,
                 $RateCustomPacakge,$RateRequestEnd,
                 $Services) = include 'ups_data.php';
            $this->MessageStart = $MessageStart;
            $this->RateRequest = $RateRequest;
            $this->RateService = $RateService;
            $this->RatePackage = $RatePackage;
            $this->RateCustomPackage = $RateCustomPacakge;
            $this->RateRequestEnd = $RateRequestEnd;
            $this->Services = $Services;
        }

	function getForm(){
            if (!isset($this->Services)){
                $this->_includeUPSData();
            }

            //__('Your Packaging', 'wpsc');  <-- use to translate
            $wpsc_ups_settings = get_option("wpsc_ups_settings");
            $wpsc_ups_services = get_option("wpsc_ups_services");
            // Defined on page 41 in UPS API documentation RSS_Tool_06_10_09.pdf
            /*$packaging_options['00'] = __('**UNKNOWN**', 'wpsc');*/
            $packaging_options['01'] = __('UPS Letter', 'wpsc');
            $packaging_options['02'] = __('Your Packaging', 'wpsc');
            $packaging_options['03'] = __('UPS Tube', 'wpsc');
            $packaging_options['04'] = __('UPS Pak', 'wpsc');
            $packaging_options['21'] = __('UPS Express Box', 'wpsc');
            $packaging_options['2a'] = __('UPS Express Box - Small', 'wpsc');
            $packaging_options['2b'] = __('UPS Express Box - Medium', 'wpsc');
            $packaging_options['2c'] = __('UPS Express Box - Large', 'wpsc');

            $output = "<tr>\n\r";
            $output .= "	<td>".__('Destination Type', 'wpsc')."</td>\n\r";
            $output .= "	<td>\n\r";

            // Default is Residential
            $checked[0] = "checked='checked'";
            $checked[1] = "";
            if ($wpsc_ups_settings['49_residential'] == "02"){
                $checked[0] = "";
                $checked[1] = "checked='checked'";
            }

            $output .= "		<label><input type='radio' {$checked[0]} value='01' name='wpsc_ups_settings[49_residential]'/>".__('Residential Address', 'wpsc')."</label><br />\n\r";
            $output .= "		<label><input type='radio' {$checked[1]} value='02' name='wpsc_ups_settings[49_residential]'/>".__('Commercial Address', 'wpsc')."</label>\n\r";
            $output .= "	</td>\n\r";
            $output .= "</tr>\n\r";


            $output .= "<tr>\n\r";
            $output .= "	<td>".__('Packaging', 'wpsc')."</td>\n\r";
            $output .= "	<td>\n\r";
            $output .= "		<select name='wpsc_ups_settings[48_container]'>\n\r";
            foreach($packaging_options as $key => $name) {
              $selected = '';
                    if($key == $wpsc_ups_settings['48_container']) {
                            $selected = "selected='true' ";
                    }
                    $output .= "			<option value='{$key}' {$selected}>{$name}</option>\n\r";
            }
            $output .= "		</select>\n\r";
            $output .= "	</td>\n\r";
            $output .= "</tr>\n\r";
            // Added by Greg Gullett --
            $selected_env = $wpsc_ups_settings['upsenvironment'];
            if ($selected_env == "0"){
                $env_test = "checked=\"checked\"";
            }
            $output .= ("
                        <tr>
                            <td><label for=\"ups_env_test\" >".__('Use Testing Environment', 'wpsc')."</label></td>
                            <td>
                                <input type=\"checkbox\" id=\"ups_env_test\" name=\"wpsc_ups_settings[upsenvironment]\" value=\"0\" ".$env_test." /><br />
                            </td>
                        </tr>
                        ");
            $output .= ("
                        <tr>
                            <td>
                                ".__('UPS Preferred Services', 'wpsc')."
                            </td>
                            <td>
                                <div id=\"resizeable\" class=\"ui-widget-content multiple-select\">");

            ksort($this->Services);
            $first=false;
            foreach(array_keys($this->Services) as $service){
                $checked = "";
                if(is_array($wpsc_ups_services)){
                    if ((array_search($service,$wpsc_ups_services) !== false)){
                        $checked = "checked=\"checked\"";
                    }
                }
                $output .= ("<input type=\"checkbox\" id=\"wps_ups_srv_$service\" name=\"wpsc_ups_services[]\" value=\"$service\" $checked />
                             <label for=\"wps_ups_srv_$service\">".$this->Services[$service]."</label>
                             <br />");
            }

            $output .= ("       </div>
                                <br />
                                *".__('All services used if no services selected','wpsc')."
                            </td>
                        </tr>");
            $output .= ("<tr>
                             <td>".__('UPS Username', 'wpsc')." :</td>
                             <td>
                                 <input type=\"text\" name='wpsc_ups_settings[upsusername]' value=\"".base64_decode($wpsc_ups_settings['upsusername'])."\" />
                             </td>
                         </tr>");
            $output .= ("<tr>
                            <td>".__('UPS Password', 'wpsc')." :</td>
                            <td>
                                <input type=\"password\" name='wpsc_ups_settings[upspassword]' value=\"".base64_decode($wpsc_ups_settings['upspassword'])."\" />
                            </td>
                        </tr>");
            $output .= ("<tr>
                            <td>".__('UPS XML API Key', 'wpsc')." :</td>
                            <td>
                                <input type=\"text\" name='wpsc_ups_settings[upsid]' value=\"".base64_decode($wpsc_ups_settings['upsid'])."\" />
                                <br />
                                ".__('Don\'t have an API login/ID ?', 'wpsc')."
                                    <a href=\"https://www.ups.com/upsdeveloperkit?loc=en_US\" target=\"_blank\">".__('Click Here','wpsc')."</a>.
                            </td>
                        </tr>");
            // End new Code
            return $output;
	}

	function submit_form() {
            /* This function is called when the user hit "submit" in the
             * UPS settings area under Shipping to update the setttings.
             */
            $wpsc_ups_services = $_POST['wpsc_ups_services'];
            update_option('wpsc_ups_services',$wpsc_ups_services);
            if ($_POST['wpsc_ups_settings'] != '') {
                $temp = $_POST['wpsc_ups_settings'];
                // base64_encode the information so it isnt stored as plaintext.
                // base64 is by no means secure but without knowing if the server
                // has mcrypt installed what can you do really?
                $temp['upsusername'] = base64_encode($temp['upsusername']);
                $temp['upspassword'] = base64_encode($temp['upspassword']);
                $temp['upsid'] = base64_encode($temp['upsid']);

                update_option('wpsc_ups_settings', $temp);
            }
            return true;
	}

        private function _buildRateRequest($args){
            // Vars is an array
            // $RateRequest, $RatePackage, $RateCustomPackage, $RateRequestEnd
            // Are defined in ups_data.php that is included below if not
            // done so by instantiating class ... shouldnt ever need to
            if (!isset($this->MessageStart)){
                $this->_includeUPSData();
            }
            // By Default we will shop. Shop when you do not have a service type
            // and you want to get a set of services and rates back!
            $RequestType = "Shop";
            // If service type is set we cannot shop so instead we Rate!
            if (isset($args["service"])){
                $RequestType = "Rate";
            }
            // Always start of with this, it includes the auth block//
            $request = sprintf($this->MessageStart,
                                // base64_encode the information so it isnt stored as plaintext.
                                // base64 is by no means secure but without knowing if the server
                                // has mcrypt installed what can you do really?
                                base64_decode($args['api_id']),   // UPS API ID#
                                base64_decode($args['username']), // UPS API Username
                                base64_decode($args['password'])  // UPS API Password
                               );
            // This starts this as a request for
            $request .= sprintf($this->RateRequest,
                                $RequestType, // Options :: Rate or Shop
                                $RequestType, // Options :: Rate or Shop
                                $args['shipr_pcode'], // The shipper Postal Code
                                $args['shipr_ccode'], // The shipper Country Code
                                $args['dest_pcode'], // The Destination Postal Code
                                $args['dest_ccode'] // The Destination
                               );
            // If there is a specific service being requested then
            // we want to pass the service into the XML
            if (isset($args["service"])){
                $request .= sprintf($this->RateService,
                                $args['service']);
            }
            // Always include this section, it starts the Package block, required
            $request .= sprintf($this->RatePackage,
                                $args['packaging']);
            // If the packaging type is "Your Own aka Package" then UPS needs
            // the dimensions of the box you are going to use to accurately rate
            if ($args['packaging'] == "02"){
                $request .= sprintf($this->RateCustomPackage,
                                    $args['units'],
                                    $args['length'],
                                    $args['width'],
                                    $args['height']
                                   );
            }
            // Okay, time to wrap it all up!
            $request .= sprintf($this->RateRequestEnd,
                                $args['units'],
                                $args['weight']
                               );
            // Return the final XML document as a string to be used by _makeRateRequest
            return $request;
        }

        private function _makeRateRequest($message){
            // Make the XML request to the server and retrieve the response
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,$this->service_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                        $message);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }

        private function _parseQuote($raw){
            $rate_table = array();
            $wpsc_ups_services = get_option("wpsc_ups_services");
            // Initialize a DOM using the XML passed in!
            $objDOM = new DOMDocument();
            if($raw != '') {
	            $objDOM->loadXML($raw);
	
	            // Get the <ResponseStatusCode> from the UPS XML
	            $getStatusNode = $objDOM->getElementsByTagName("ResponseStatusCode");
	            // Get the value of the error code, 1 == No Error, 0 == Error !!!
	            $statusCode = $getStatusNode->item(0)->nodeValue;
	
	            if ($statusCode == "0"){
	                // Usually I dont leave debug stuff in but this is handy stuff!!
	                // it will print out the error message returned by UPS!
	                /*$getErrorDescNode = $objDOM->getElementsByTagName("ErrorDescription");
	                $ErrorDesc = $getErrorDescNode->item(0)->nodeValue;
	                echo "<br />Error : ".$ErrorDesc."<br />";*/
	                return false;
	            }else{
	                $RateBlocks = $objDOM->getElementsByTagName("RatedShipment");
	                foreach($RateBlocks as $rate_block){
	                    // Get the <Service> Node from the XML chunk
	                    $getServiceNode = $rate_block->getElementsByTagName("Service");
	                    $serviceNode = $getServiceNode->item(0);
	
	                    // Get the <Code> Node from the <Service> chunk
	                    $getServiceCodeNode = $serviceNode->getElementsByTagName("Code");
	                    // Get the value from <Code>
	                    $serviceCode = $getServiceCodeNode->item(0)->nodeValue;
	
	                    // Get the <TotalCharges> Node from the XML chunk
	                    $getChargeNodes = $rate_block->getElementsByTagName("TotalCharges");
	                    $chargeNode = $getChargeNodes->item(0);
	
	                    // Get the <CurrencyCode> from the <TotalCharge> chunk
	                    $getCurrNode= $chargeNode->getElementsByTagName("CurrencyCode");
	                    // Get the value of <CurrencyCode>
	                    $currCode = $getCurrNode->item(0)->nodeValue;
	
	                    // Get the <MonetaryValue> from the <TotalCharge> chunk
	                    $getMonetaryNode= $chargeNode->getElementsByTagName("MonetaryValue");
	                    // Get the value of <MonetaryValue>
	                    $price = $getMonetaryNode->item(0)->nodeValue;
	                    // If there are any services specified in the admin area
	                    // this will check that list and pass on adding any services that
	                    // are not explicitly defined.
	                    if (!empty($wpsc_ups_services)){
	                        if (is_array($wpsc_ups_services)){
	                            if (array_search($serviceCode, $wpsc_ups_services) === false){
	                                continue;
	                            }
	                        }else if ($wpsc_ups_services != $serviceCode){
	                            continue;
	                        }
	                    }
	                    if(array_key_exists($serviceCode,$this->Services)){
	                        $rate_table[$this->Services[$serviceCode]] = array($currCode,$price);
	                    }
	
	                } // End foreach rated shipment block
	            }
            }
            // Revers sort the rate selection so it is cheapest First!
            asort($rate_table);
            return $rate_table;
        }

        private function _formatTable($services, $currency=false){
            /* The checkout template expects the array to be in a certain
             * format. This function will iterate through the provided
             * services array and format it for use. During the loop
             * we take advantage of the loop and translate the currency
             * if necessary based off of what UPS tells us they are giving us
             * for currency and what is set for the main currency in the settings
             * area
             */
            $converter = null;
            if ($currency){
                $converter = new CURRENCYCONVERTER();
            }
            $finalTable = array();
            foreach(array_keys($services) as $service){
                if ($currency != false && $currency != $services[$service][0]){
                    $temp =$services[$service][1];
                    $services[$service][1] = $converter->convert($services[$service][1],
                                                                 $currency,
                                                                 $services[$service][0]);
                }
                $finalTable[$service] = $services[$service][1];
            }
            return $finalTable;
        }

        function getQuote(){
            global $wpdb;
            // Arguments array for various functions to use
            $args = array();
            // Final rate table
            $rate_table = array();
            // Get the ups settings from the ups account info page (Shipping tab)
            $wpsc_ups_settings = get_option("wpsc_ups_settings");
            // Get the wordpress shopping cart options
            $wpsc_options = get_option("wpsc_options");

            // API Auth settings //
            $args['username'] = $wpsc_ups_settings['upsusername'];
            $args['password'] = $wpsc_ups_settings['upspassword'];
            $args['api_id']   = $wpsc_ups_settings['upsid'];
            // What kind of pickup service do you use ?
            $args['pickup_type'] = "01";
            $args['packaging'] = $wpsc_ups_settings['48_container'];
            // Preferred Currency to display
            $currency_data = $wpdb->get_row("SELECT `code`
                                             FROM `".WPSC_TABLE_CURRENCY_LIST."`
                                             WHERE `isocode`='".$wpsc_options['currency_type']."'
                                             LIMIT 1",ARRAY_A) ;
            if ($currency_data){
                $args['currency'] = $currency_data['code'];
            }else{
                $args['currency'] = "USD";
            }
            // Shipping billing / account address
            $args['shipr_ccode'] = get_option('base_country');
            $args['shipr_pcode'] = get_option('base_zipcode');
            // Physical Shipping addres being shipped from
            $args['shipf_ccode'] = get_option('base_country');
            $args['shipf_pcode'] = get_option('base_zipcode');
            // Get the total weight from the shopping cart
            $args['units'] = "LBS";
            $args['weight'] = wpsc_cart_weight_total();
            // Destination zip code
            $args['dest_ccode'] = $_SESSION['wpsc_delivery_country'];
            if ($args['dest_ccode'] == "UK"){
                // So, UPS is a little off the times
                $args['dest_ccode'] = "GB";
            }

            // If ths zip code is provided via a form post use it!
            if(isset($_POST['zipcode'])) {
              $args['dest_pcode'] = $_POST['zipcode'];
              $_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
            } else if(isset($_SESSION['wpsc_zipcode'])) {
              // Well, we have a zip code in the session and no new one provided
              $args['dest_pcode'] = $_SESSION['wpsc_zipcode'];
            }else{
                // We cannot get a quote without a zip code so might as well return!
                return array();
            }

            $shipping_cache_check['zipcode'] = $args['dest_pcode'];
            $shipping_cache_check['weight'] = $args['weight'];
            // This is where shipping breaks out of UPS if weight is higher than 150 LBS
            if($weight > 150){
                    unset($_SESSION['quote_shipping_method']);
                    $shipping_quotes[TXT_WPSC_OVER_UPS_WEIGHT] = 0;
                    $_SESSION['wpsc_shipping_cache_check']['weight'] = $args['weight'];
                    $_SESSION['wpsc_shipping_cache'][$this->internal_name] = $shipping_quotes;
                    $_SESSION['quote_shipping_method'] = $this->internal_name;
                    return array($shipping_quotes);
            }
            // We do not want to spam UPS (and slow down our process) if we already
            // have a shipping quote!
            if(($_SESSION['wpsc_shipping_cache_check'] === $shipping_cache_check)
                    && ($_SESSION['wpsc_shipping_cache'][$this->internal_name] != null)) {
                $rate_table = $_SESSION['wpsc_shipping_cache'][$this->internal_name];
            }else{
                // Build the XML request
                $request = $this->_buildRateRequest($args);
                // Now that we have the message to send ... Send it!
                $raw_quote = $this->_makeRateRequest($request);
                // Now we have the UPS response .. unfortunately its not ready
                // to be viewed by normal humans ...
                $quotes = $this->_parseQuote($raw_quote);
                // If we actually have rates back from UPS we can use em!
                if ($quotes != false){
                    $rate_table = $this->_formatTable($quotes,$args['currency']);
                }
            }
            // return the final formatted array !
  			$_SESSION['wpsc_shipping_cache_check']['zipcode'] = $args['dest_pcode'];
			$_SESSION['wpsc_shipping_cache_check']['weight'] = $args['weight'];
			$_SESSION['wpsc_shipping_cache'][$this->internal_name] = $rate_table;
            return $rate_table;
        }

        // Empty Function, this exists just b/c it is prototyped elsewhere
	function get_item_shipping(){
	}
}
$ups = new ups();
$wpsc_shipping_modules[$ups->getInternalName()] = $ups;
?>