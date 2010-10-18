<?php
 /*
  * more code to update from old versions, messy code too
  */

  $coldata  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` LIKE 'image'",ARRAY_A);
  if($coldata == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_CATEGORIES."` ADD `image` TEXT NOT NULL DEFAULT '' AFTER `description`");
    }
    
  $coldata2  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'quantity_limited'",ARRAY_A);
  if($coldata2 == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `quantity_limited` VARCHAR( 1 ) NOT NULL  DEFAULT '0' AFTER `category`");
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `quantity` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `quantity_limited`");
    }
    
  $coldata3  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'file'",ARRAY_A);
  if($coldata3 == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `file` BIGINT UNSIGNED NOT NULL  AFTER `category`");
    }
    
  $coldata4  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'special_price'",ARRAY_A);
  if($coldata4 == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `special_price` VARCHAR( 20 ) NOT NULL AFTER `special`");
    }
    
  $coldata5  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'processed'",ARRAY_A);
  if($coldata5[0]['Type'] == "char(1)")
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` CHANGE `processed` `processed` BIGINT UNSIGNED NOT NULL DEFAULT '1'");
    }
    
  $coldata6  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_CART_CONTENTS."` LIKE 'price'",ARRAY_A);
  if($coldata6 == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CART_CONTENTS."` ADD `price` VARCHAR( 128 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CART_CONTENTS."` ADD `pnp` VARCHAR( 128 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CART_CONTENTS."` ADD `gst` VARCHAR( 128 ) NOT NULL");
    }
    
  $coldata7  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'brand'",ARRAY_A);
  if($coldata7 == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `brand` BIGINT UNSIGNED NOT NULL  AFTER `category`");
    }

  $coldata8  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'additional_description'",ARRAY_A);
  if($coldata8 == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `additional_description` LONGTEXT NOT NULL AFTER `description`");
    }
    
  $coldata9  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'pnp'",ARRAY_A);
  if($coldata9[0]['Type'] != "varchar(20)")
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` CHANGE `pnp` `pnp` VARCHAR( 20 ) DEFAULT '0' NOT NULL");
    }
    
$add_cart_quantity  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_CART_CONTENTS."` LIKE 'quantity'",ARRAY_A);
  if($add_cart_quantity == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CART_CONTENTS."` ADD `quantity` INT UNSIGNED NOT NULL AFTER `gst` ;");
    }
    
  $add_cart_donation  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_CART_CONTENTS."` LIKE 'donation'",ARRAY_A);
  if($add_cart_donation == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CART_CONTENTS."` ADD `donation` VARCHAR( 1 ) NOT NULL AFTER `quantity` ;");
    }
    
  $add_international_pnp  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'international_pnp'",ARRAY_A);
  if($add_international_pnp == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `international_pnp` VARCHAR( 20 ) NOT NULL AFTER `pnp`;");
    }
    
  $add_gateway_log  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'gateway'",ARRAY_A);
  if($add_gateway_log == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD `gateway` VARCHAR( 64 ) NOT NULL AFTER `date`;");
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD INDEX ( `gateway` ) ;");
    }    

  $add_shipping_country  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'shipping_country'",ARRAY_A);
  if($add_shipping_country == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD `shipping_country` CHAR( 6 ) NOT NULL AFTER `gateway`;");
    }  

  $add_shipping_country  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'user_ID'",ARRAY_A);
  if($add_shipping_country == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD `user_ID` BIGINT UNSIGNED NULL AFTER `processed`;");
    }  

  $add_billing_country  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'billing_country'",ARRAY_A);
  if($add_billing_country == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD `billing_country` CHAR( 6 ) NOT NULL AFTER `gateway`;");
    // copy shipping_country into billing_country, shipping_country did the job of both before
    $wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `billing_country` = `shipping_country`");
    } 
//  `email_sent` CHAR( 1 ) NOT NULL,

  $add_email_sent  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'email_sent'",ARRAY_A);
  if($add_email_sent == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD `email_sent` CHAR( 1 ) DEFAULT '0' NOT NULL AFTER `shipping_country`;");
    } 
     
  $add_shipping_region  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PURCHASE_LOGS."` LIKE 'shipping_region'",ARRAY_A);
  if($add_shipping_region == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PURCHASE_LOGS."` ADD `shipping_region` CHAR( 6 ) NOT NULL AFTER `shipping_country`;");
    }
  $add_display_frontpage  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'display_frontpage';",ARRAY_A);
  if($add_display_frontpage == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `display_frontpage` VARCHAR( 1 ) NOT NULL AFTER `special_price`;");
    }
  
  $add_currency_tax  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_CURRENCY_LIST."` LIKE 'tax';",ARRAY_A);
  if($add_currency_tax == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CURRENCY_LIST."` ADD `tax` VARCHAR( 8 ) NOT NULL AFTER `code`;");
    }
  
  $add_currency_has_regions  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_CURRENCY_LIST."` LIKE 'has_regions';",ARRAY_A);
  if($add_currency_has_regions == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CURRENCY_LIST."` ADD `has_regions` VARCHAR( 8 ) NOT NULL AFTER `code`;");
    }
  
  $add_product_thumbnail  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'thumbnail_image';",ARRAY_A);
  if($add_product_thumbnail == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `thumbnail_image` TEXT NULL AFTER `active`;");
    }
  
  $add_thumbnail_state  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'thumbnail_state';",ARRAY_A);
  if($add_thumbnail_state == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `thumbnail_state` INTEGER NOT NULL AFTER `active`;");
    }
  
  $add_thumbnail_state  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'donation';",ARRAY_A);
  if($add_thumbnail_state == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `donation` VARCHAR( 1 ) DEFAULT '0' NOT NULL AFTER `active`;");
    }
    
  $add_no_shipping  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_LIST."` LIKE 'no_shipping'",ARRAY_A);
  if($add_no_shipping == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_LIST."` ADD `no_shipping` VARCHAR( 1 ) DEFAULT '0' NOT NULL AFTER `donation` ;");
    }    
    
  $add_cart_no_shipping  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_CART_CONTENTS."` LIKE 'no_shipping'",ARRAY_A);
  if($add_cart_no_shipping == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_CART_CONTENTS."` ADD `no_shipping` VARCHAR( 1 ) DEFAULT '0' NOT NULL AFTER `donation` ;");
    }


  $add_category_parent  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` LIKE 'category_parent'",ARRAY_A);
  if($add_category_parent == null)
    {
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_CATEGORIES."` ADD `category_parent` BIGINT UNSIGNED DEFAULT '0' NOT NULL AFTER `active`") ;
    $wpdb->query("ALTER TABLE `".WPSC_TABLE_PRODUCT_CATEGORIES."` ADD INDEX ( `category_parent` )");
    }    
?>