<?php
	global $wpdb, $user_ID;
	$purchases= $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE user_ID = ".(int)$user_ID." AND user_ID !='0'") ;
	$rowcount = count($purchases);
	//echo "<pre>".print_r($purchases,true)."</pre>";
	
	if($rowcount >= 1) {
		$perchidstr = "(";
		$perchidstr .= implode(',',$purchases);
	  $perchidstr .= ")";		
		$sql = "SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `purchid` IN ".$perchidstr." AND `active` IN ('1') ORDER BY `datetime` DESC";
		$products = $wpdb->get_results($sql,ARRAY_A) ;
	}
	//exit($products);
	foreach ((array)$products as $key => $product){
	  $sql = "SELECT `processed` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`=".$product['purchid'];
	  $isOrderAccepted = $wpdb->get_var($sql);
	 if($isOrderAccepted > 1){
		if($product['uniqueid'] == null) {  // if the uniqueid is not equal to null, its "valid", regardless of what it is 
		  	$links[] = get_option('siteurl')."?downloadid=".$product['id'];
		} else {
			$links[] = get_option('siteurl')."?downloadid=".$product['uniqueid'];
		}	
		$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE id = ".(int)$product['fileid']."";
		$file = $wpdb->get_results($sql,ARRAY_A) ;
		$files[] = $file[0];
	 }
	}
	
	//exit("---------------<pre>".print_r($files,1)."</pre>");
	?>
<div class="wrap" style=''>
	<?php
		echo " <div class='user-profile-links'><a href='".get_option('user_account_url')."'>Purchase History</a> | <a href='".get_option('user_account_url').$seperator."edit_profile=true'>Your Details</a> | <a href='".get_option('user_account_url').$seperator."downloads=true'>Your Downloads</a></div><br />";
	?>
	<?php
if(count($files) > 0) {
    ?>
		<table class='logdisplay'>
			<tr>
				<th><?php echo __('File Names', 'wpsc'); ?> </th>
				<th><?php echo __('Downloads Left', 'wpsc'); ?> </th>
				<th><?php echo __('Date', 'wpsc'); ?> </th>
			</tr>
      <?php
        $i=0;
        foreach((array)$files as $file){
          $alternate = "";
          if(($i % 2) != 1) {
            $alternate = "class='alt'";
          }
          echo "			<tr $alternate>\n\r";
          echo "			  <td>";
          if ($products[$i]['downloads'] > 0) {
            echo "<a href = ".$links[$i].">".$file['filename']."</a>";
          } else {
            echo $file['filename']."";
          }
          echo "</td>\n\r";
          echo "			  <td>".$products[$i]['downloads']."</td>\n\r";
          echo "			  <td>".date("j M Y", strtotime($products[$i]['datetime']))."</td>\n\r";
          echo "			</tr>\n\r";
          $i++;
        }
      ?>
		</table>
	<?php
} else {	
	echo __('You have not purchased any downloadable products yet.', 'wpsc'); 
}?>
</div>
<?php

?>