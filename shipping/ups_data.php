<?php
$eol = "\n";
/*############################################################################*\
| * XML chunk that must be appended to ALL outgoing requests to the UPS server*|
| * Fields are                                                                *|
| *  - AccessLicenseNumber aka $wpsc_ups_settings['upsemail']                 *|
| *  - UserId aka $wpsc_ups_settings['upsid']                                 *|
| *  - Password aka $wpsc_ups_settings['upspassword']                         *|
\*############################################################################*/
$Auth = $eol."<AccessRequest xml:lang='en-US'>".$eol."
    <AccessLicenseNumber>".$eol."
         %s".$eol."
    </AccessLicenseNumber>".$eol."
    <UserId>".$eol."
         %s".$eol."
    </UserId>".$eol."
    <Password>".$eol."
         %s".$eol."
    </Password>".$eol."
</AccessRequest>".$eol;
/*############################################################################*\
| * Start the XML Message and Include the AUTH PART !!! ($auth)              | *                                                                           *|
\*############################################################################*/

$MessageStart = "<?xml version=\"1.0\"?>".$Auth;

/*############################################################################*\
| * XML request body                                                          *|
\*############################################################################*/
$RateRequest = ("<RatingServiceSelectionRequest xml:lang=\"en-US\">".$eol."
    <Request>".$eol."
        <TransactionReference>".$eol."
          <CustomerContext>Rate Request</CustomerContext>".$eol."
          <XpciVersion>1.0</XpciVersion>".$eol."
        </TransactionReference>".$eol."
            <RequestAction>%s</RequestAction>".$eol."
	<RequestOption>%s</RequestOption>".$eol."
    </Request>".$eol."
    <Shipment>".$eol."
        <Shipper>".$eol."
            <Address>".$eol."
                    <PostalCode>%s</PostalCode>".$eol."
                    <CountryCode>%s</CountryCode>".$eol."
            </Address>".$eol."
        </Shipper>".$eol."
        <ShipTo>".$eol."
            <Address>".$eol."
                    <PostalCode>%s</PostalCode>".$eol."
                    <CountryCode>%s</CountryCode>".$eol."
            </Address>".$eol."
        </ShipTo>".$eol);
$RateService = ("<Service>".$eol."
                <Code>%s</Code>".$eol."
        </Service>".$eol);
$RatePackage = ("<Package>".$eol."
            <PackagingType>".$eol."
                    <Code>%s</Code>".$eol."
            </PackagingType>".$eol);

$RateCustomPackage = ("<dimensions>".$eol."
        <UnitOfMeasurement>".$eol."
          <Code>%s</Code>".$eol."
        </UnitOfMeasurement>".$eol."
        <Length>%s</Length>".$eol."
        <Width>%s</Width>".$eol."
        <Height>%s</Height>".$eol."
    </dimensions>");

$RateRequestEnd = ("<PackageWeight>".$eol."
                <UnitOfMeasurement>".$eol."
                         <Code>%s</Code>".$eol."
                </UnitOfMeasurement>".$eol."
                <Weight>%s</Weight>".$eol."
            </PackageWeight>".$eol."
        </Package>".$eol."
    </Shipment>".$eol."
</RatingServiceSelectionRequest>".$eol);
/*############################################################################*\
| * Array of countries (and US-domestic) to list the services available       *|
\*############################################################################*/
$Services = array(
    "14" => "Next Day Air Early AM",
    "01" => "Next Day Air",
    "13" => "Next Day Air Saver",
    "59" => "2nd Day Air AM",
    "02" => "2nd Day Air",
    "12" => "3 Day Select",
    "03" => "Ground",
    "11" => "Standard",
    "07" => "Worldwide Express",
    "54" => "Worldwide Express Plus",
    "08" => "Worldwide Expedited",
    "65" => "Saver",
    "82" => "UPS Today Standard",
    "83" => "UPS Today Dedicated Courier",
    "84" => "UPS Today Intercity",
    "85" => "UPS Today Express",
    "86" => "UPS Today Express Saver"
);

return array($eol, $Auth, $MessageStart, $RateRequest, $RateService,
             $RatePackage, $RateCustomPackage, $RateRequestEnd,
             $Services);
?>