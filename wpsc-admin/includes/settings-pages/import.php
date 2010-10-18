<?php
function wpsc_options_import(){
global $wpdb;
?>
	<form name='cart_options' enctype='multipart/form-data' id='cart_options' method='post' action='<?php echo 'admin.php?page=wpsc-settings&tab=import'; ?>'>
	<div class="wrap">
		<h2><?php echo __('Import Products CSV', 'wpsc');?></h2>
		<?php echo __('<p>You can import your products from a comma delimited text file.</p><p>An example of a cvs import file would look like this: </p><p>Description, Additional Description, Product Name, Price, SKU, weight, weight unit, stock quantity, is limited quantity</p>', 'wpsc');?>
		
		<?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
		<input type='hidden' name='MAX_FILE_SIZE' value='5000000' />
		<input type='file' name='csv_file' />
		<input type='submit' value='Import' class='button-primary'>
<?php
//exit('<pre>'.print_r($_FILES, true).'</pre>');
if ($_FILES['csv_file']['name'] != '') {
ini_set("auto_detect_line_endings", 1);
	$file = $_FILES['csv_file'];
	//exit('<pre>'.print_r($file,true).'</pre>');
	if(move_uploaded_file($file['tmp_name'],WPSC_FILE_DIR.$file['name'])){
		$content = file_get_contents(WPSC_FILE_DIR.$file['name']);
		//exit('<pre>'.print_r(WPSC_FILE_DIR.$file['name'], true).'</pre>');
		$handle = @fopen(WPSC_FILE_DIR.$file['name'], 'r');
		while (($csv_data = @fgetcsv($handle, filesize($handle), ",")) !== false) {
			$fields = count($csv_data);
			for ($i=0;$i<$fields;$i++) {
				if (!is_array($data1[$i])){
					$data1[$i] = array();
				}
				array_push($data1[$i], $csv_data[$i]);
			}
		}
		//exit("<pre>".print_r($data1, 1)."</pre>");
		$_SESSION['cvs_data'] = $data1;
		$categories_sql = "SELECT `id`,`name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1'";
		$categories = $wpdb->get_results($categories_sql, ARRAY_A);
		?>
		
		<p>For each column, select the field it corresponds to in 'Belongs to'. You can upload as many products as you like.</p>
		<div class='metabox-holder' style='width:90%'>
		<input type='hidden' name='csv_action' value='import'>
		<?php
	//	exit('<pre>'.print_r($_SESSION['cvs_data'], true).'</pre>');
		foreach ((array)$data1 as $key => $datum) {
		?>
			<div style='width:100%;' class='postbox'>
			<h3 class='hndle'>Column (<?php echo $key+1; ?>)</h3>
			<div class='inside'>
			<table>
			<tr><td style='width:80%;'>
			<input type='hidden' name='column[]' value='<?php echo $key+1; ?>'>
			<?php
			foreach ($datum as $column) {
				echo $column;
				break;
			} ?>
				<br />
			</td><td>
			<select  name='value_name[]'>
	<!-- /* 		These are the current fields that can be imported with products, to add additional fields add more <option> to this dorpdown list */ -->
			<option value='name'>Product Name</option>
			<option value='description'>Description</option>
			<option value='additional_description'>Additional Description</option>
			<option value='price'>Price</option>
			<option value='sku'>SKU</option>
			<option value='weight'>Weight</option>
			<option value='weight_unit'>Weight Unit</option>
			<option value='quantity'>Stock Quantity</option>
			<option value='quantity_limited'>Stock Quantity Limit</option>
			</select>
			</td></tr>
			</table>
			</div>
			</div>
			<?php
		} ?>
		<label for='category'>Please select a category you would like to place all products from this CSV into:</label>
		<select id='category' name='category'>
		<?php
		foreach($categories as $category){
		echo '<option value="'.$category['id'].'">'.$category['name'].'</option>';
		
		}
		?>
		</select>
		<input type='submit' value='Import' class='button-primary'>
		</div>
	<?php
	}else{
	echo "<br /><br />There was an error while uploading your csv file.";

	}
}
if($_POST['csv_action'] == 'import'){
	global $wpdb;

	$cvs_data = $_SESSION['cvs_data'];
	//exit('<pre>'.print_r($_SESSION['cvs_data'], true).'</pre>');
	$column_data = $_POST['column'];
	$value_data = $_POST['value_name'];
	$name = array();
/*
	foreach ($value_data as $key => $value) {
		$value_data[$key] = $cvs_data[$key];
	}
*/
	//echo('<pre>'.print_r($value_data, true).'</pre><pre>'.print_r($column_data, true).'</pre>');
	foreach ($value_data as $key => $value) {

			$cvs_data2[$value] = $cvs_data[$key];

	}
	//exit('<pre>'.print_r($cvs_data2, true).'</pre>');
	$num = count($cvs_data2['name']);
	
	for($i =0; $i < $num; $i++){
		
		 $cvs_data2['price'][$i] = str_replace('$','',$cvs_data2['price'][$i]);
		//exit( $cvs_data2['price'][$i]);
		
	//	exit($key. ' ' . print_r($data));		
		$query = "('".$cvs_data2['name'][$i]."', '".$cvs_data2['description'][$i]."', '".$cvs_data2['additional_description'][$i]."','".$cvs_data2['price'][$i]."','".$cvs_data2['weight'][$i]."','".$cvs_data2['weight_unit'][$i]."','".$cvs_data2['quantity'][$i]."','".$cvs_data2['quantity_limited'][$i]."')";
		$query = "INSERT INTO `".WPSC_TABLE_PRODUCT_LIST."` (name, description, additional_description, price, weight, weight_unit, quantity, quantity_limited) VALUES ".$query;
	//	echo($query);
		$wpdb->query($query);
		$id = $wpdb->get_var("SELECT LAST_INSERT_ID() as id FROM `".WPSC_TABLE_PRODUCT_LIST."`");
		$meta_query = "INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` VALUES ('', '$id', 'sku', '".$cvs_data2['sku'][$i]."', '0')";
		$wpdb->query($meta_query);
		$category_query = "INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` VALUES ('','{$id}','".$wpdb->escape($_POST['category'])."')";
		$wpdb->query($category_query);
		$existing_name = get_product_meta($id, 'url_name');
		// strip slashes, trim whitespace, convert to lowercase
		$tidied_name = strtolower(trim(stripslashes($cvs_data2['name'][$i])));
		// convert " - " to "-", all other spaces to dashes, and remove all foward slashes.
		//$url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/", "/(\/)+/"), array("-","-", ""), $tidied_name);
		$url_name =  sanitize_title($tidied_name);
		//exit('NAMES >>'.$url_name.' '.$existing_name);
		// Select all similar names, using an escaped version of the URL name 
		$similar_names = (array)$wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` NOT IN('{$id}}') AND `meta_key` IN ('url_name') AND `meta_value` REGEXP '^(".$wpdb->escape(preg_quote($url_name))."){1}[[:digit:]]*$' ");

		// Check desired name is not taken
		if(array_search($url_name, $similar_names) !== false) {
		  // If it is, try to add a number to the end, if that is taken, try the next highest number...
			$j = 0;
			do {
				$j++;
			} while(array_search(($url_name.$j), $similar_names) !== false);
			// Concatenate the first number found that wasn't taken
			$url_name .= $j;
		}
	  // If our URL name is the same as the existing name, do othing more.
		if($existing_name != $url_name) {
			update_product_meta($id, 'url_name', $url_name);
		}
		}

/* 	$query = "INSERT INTO {$wpdb->prefix}product_list (name, description, addictional_description, price) VALUES ".$query; */
	echo "<br /><br />Success, your <a href='?page=wpsc-edit-products'>products</a> have been upload.";
		
}

?>	</div>
</form>
<?php

}
?>