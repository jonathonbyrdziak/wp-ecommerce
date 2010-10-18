<?php


function wpsc_feed_publisher() {

	// If the user wants a product feed, then hook-in the product feed function
	if ( $_GET["rss"] == "true" &&
	     $_GET["action"] == "product_list" ) {

    		add_action( 'wp', 'wpsc_generate_product_feed' );

  	}

}

add_action('init', 'wpsc_feed_publisher');


function wpsc_generate_product_feed() {

	global $wpdb;
	
	// Don't cache feed under WP Super-Cache
	define('DONOTCACHEPAGE',TRUE);

	$siteurl = get_option('siteurl');

	// Allow limiting
	if (is_numeric($_GET['limit'])) {
		$limit = "LIMIT ".$_GET['limit']."";
	} else {
		$limit = '';
	}

	$selected_category = '';
	$selected_product = '';

	if (is_numeric($_GET['product_id'])) {

		$selected_product = "&amp;product_id=".$_GET['product_id']."";

		$sql = "    SELECT p.*,
		                   pi.image
		              FROM `".WPSC_TABLE_PRODUCT_LIST."` p
		         LEFT JOIN `".WPSC_TABLE_PRODUCT_IMAGES."` pi
		                ON `p`.`image` = `pi`.`id`
		               AND `pi`.`product_id` = `p`.`id`
		             WHERE `active` = '1'
		               AND `publish` = '1'
		               AND p.id = '".$_GET['product_id']."'
		             LIMIT 1";

	} elseif (is_numeric($_GET['category_id'])) {

		$selected_category = "&amp;category_id=".$_GET['category_id']."";

		$sql = "SELECT `p`.*,
		               `pi`.`image`
		          FROM `".WPSC_TABLE_PRODUCT_LIST."` p
		     LEFT JOIN `".WPSC_TABLE_PRODUCT_IMAGES."` pi
		            ON `p`.`image` = `pi`.`id`
		           AND `p`.`id` = `pi`.`product_id`
		     LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ca
		            ON `p`.`id` = `ca`.`product_id`
		         WHERE `p`.`active` = '1'
		           AND `p`.`publish` = '1'
		           AND `ca`.`category_id` IN ('".$_GET['category_id']."')
		        $limit";

	} else {

		$sql = "SELECT p.*,
		               pi.image
		          FROM `".WPSC_TABLE_PRODUCT_LIST."` p
		     LEFT JOIN `".WPSC_TABLE_PRODUCT_IMAGES."` pi
		            ON `p`.`image` = `pi`.`id`
		           AND `pi`.`product_id` = `p`.`id`
		         WHERE `active` ='1'
		           AND `publish` = '1'
		      ORDER BY `id`
		          DESC $limit";

	}

	$self = get_option('siteurl')."/index.php?rss=true&amp;action=product_list$selected_category$selected_product";

	$product_list = $wpdb->get_results($sql, ARRAY_A);

	header("Content-Type: application/xml; charset=UTF-8");
	header('Content-Disposition: inline; filename="e-Commerce_Product_List.rss"');

	$output = "<?xml version='1.0' encoding='UTF-8' ?>\n\r";
	$output .= "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'";

	$google_checkout_note = FALSE;

	if ($_GET['xmlformat'] == 'google') {
		$output .= ' xmlns:g="http://base.google.com/ns/1.0"';
		// Is Google Checkout available as a payment gateway
        	$selected_gateways = get_option('custom_gateway_options');
		if (in_array('google',$selected_gateways)) {
			$google_checkout_note = TRUE;
		}
	} else {
		$output .= ' xmlns:product="http://www.buy.com/rss/module/productV2/"';
	}

	$output .= ">\n\r";
	$output .= "  <channel>\n\r";
	$output .= "    <title><![CDATA[".get_option('blogname')." Products]]></title>\n\r";
	$output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php</link>\n\r";
	$output .= "    <description>This is the WP e-Commerce Product List RSS feed</description>\n\r";
	$output .= "    <generator>WP e-Commerce Plugin</generator>\n\r";
	$output .= "    <atom:link href='$self' rel='self' type='application/rss+xml' />\n\r";

	foreach ($product_list as $product) {

		$purchase_link = wpsc_product_url($product['id']);

		$output .= "    <item>\n\r";
		if ($google_checkout_note) {
			$output .= "      <g:payment_notes>Google Checkout</g:payment_notes>\n\r";
		}
		$output .= "      <title><![CDATA[".stripslashes($product['name'])."]]></title>\n\r";
		$output .= "      <link>$purchase_link</link>\n\r";
		$output .= "      <description><![CDATA[".stripslashes($product['description'])."]]></description>\n\r";
		$output .= "      <pubDate>".date("r")."</pubDate>\n\r";
		$output .= "      <guid>$purchase_link</guid>\n\r";

		if ($product['thumbnail_image'] != null) {
			$image_file_name = $product['thumbnail_image'];
			$image_path = WP_CONTENT_DIR."/uploads"."/wpsc/product_images/thumbnails/{$image_file_name}";
			$image_link = WP_CONTENT_URL."/uploads"."/wpsc/product_images/thumbnails/".rawurlencode($image_file_name);

		} else {
			$image_file_name = $product['image'];
			$image_path = WP_CONTENT_DIR."/uploads"."/wpsc/product_images/{$image_file_name}";
			$image_link = WP_CONTENT_URL."/uploads"."/wpsc/product_images/".rawurlencode($image_file_name);
		}

		//$image_path = WP_CONTENT_DIR."/uploads"."/wpsc/product_images/thumbnails/{$image_file_name}";

		if (is_file($image_path) && (filesize($image_path) > 0)) {

			$image_data = @getimagesize($image_path);
		//	$image_link = WP_CONTENT_URL."/uploads"."/wpsc/product_images/thumbnails/".urlencode($image_file_name);

			if ($_GET['xmlformat'] == 'google') {
				$output .= "      <g:image_link>$image_link</g:image_link>\n\r";
			} else {
				$output .= "      <enclosure url='$image_link' length='".filesize($image_path)."' type='".$image_data['mime']."' width='".$image_data[0]."' height='".$image_data[1]."' />\n\r";
			}

		}

		if ($_GET['xmlformat'] == 'google') {

			$output .= "      <g:price>".$product['price']."</g:price>\n\r";
			//$output .= "      <g:condition>new</g:condition>\n\r";
		    $meta_sql = "SELECT meta_key, meta_value 
		                 FROM `".WPSC_TABLE_PRODUCTMETA."` pm
                         WHERE `pm`.`product_id` = '".$product['id']."'
                         AND `pm`.`meta_key` LIKE 'g:%'";

                        $google_elements = $wpdb->get_results($meta_sql, ARRAY_A);
			$google_elements = apply_filters('wpsc_google_elements', array('product_id'=>$product['id'],'elements'=>$google_elements));
			$google_elements = $google_elements['elements'];

                         $done_condition = FALSE;
                         if (count($google_elements)) {
                                 foreach ($google_elements as $gelement) {
 
 					$output .= "      <".$gelement['meta_key'].">";
 					$output .= "<![CDATA[".$gelement['meta_value']."]]>";
 					$output .= "</".$gelement['meta_key'].">\n\r";
 
                                         if ($gelement['meta_key'] == 'g:condition')
                                                 $done_condition = TRUE;
                                 }
                         }
                         if (!$done_condition)
                                 $output .= "      <g:condition>new</g:condition>\n\r";

		} else {

			$output .= "      <product:price>".$product['price']."</product:price>\n\r";

		}

		$output .= "    </item>\n\r";

	}

	$output .= "  </channel>\n\r";
	$output .= "</rss>";

	echo $output;

	exit();

}


?>
