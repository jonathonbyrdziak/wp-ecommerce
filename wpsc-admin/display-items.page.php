<?php
/**
 * WP eCommerce edit and add product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_display_edit_products_page() {
  global $wpdb;
	$category_id = absint($_GET['category_id']);
	
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'image' => 'Name',
		'title' => '',
		'price' => 'Price',
		'categories' => 'Categories',
	);
	register_column_headers('display-product-list', $columns);	
	
	$baseurl = includes_url('js/tinymce');

  ?>
	<div class="wrap">
		<?php // screen_icon(); ?>
		<h2><?php echo wp_specialchars( __('Display Products', 'wpsc') ); ?> </h2>
		
		<?php if(isset($_GET['ErrMessage']) && is_array($_SESSION['product_error_messages'])){ ?>
				<div id="message" class="error fade">
					<p>
						<?php
						foreach($_SESSION['product_error_messages'] as $error) {
							echo $error;
						}
						?>
					</p>
				</div>
				<?php 	unset($_GET['ErrMessage']); ?>
				<?php $_SESSION['product_error_messages'] = ''; ?>
		<?php } ?>
			
		<?php if (isset($_GET['flipped']) || isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) || isset($_GET['message']) || isset($_GET['duplicated']) ) { ?>
			<div id="message" class="updated fade">
				<p>
				<?php if ( isset($_GET['updated'])) {
					printf( __ngettext( '%s product updated.', '%s products updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
					unset($_GET['updated']);
				}
				
				if ( isset($_GET['flipped'])) {
					printf( __ngettext( '%s product updated.', '%s products updated.', $_GET['flipped'] ), number_format_i18n( $_GET['flipped'] ) );
					unset($_GET['flipped']);
				}
				
				if ( isset($_GET['skipped'])) {
					unset($_GET['skipped']);
				}
				
				if ( isset($_GET['deleted'])) {
					printf( __ngettext( 'Product deleted.', '%s products deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
					unset($_GET['deleted']);
				}
				
				if ( isset($_GET['duplicated']) ) {
					printf( __ngettext( 'Product duplicated.', '%s products duplicated.', $_GET['duplicated'] ), number_format_i18n( $_GET['duplicated'] ) );
					unset($_GET['duplicated']);
				}
				
				if ( isset($_GET['message']) ) {
					$message = absint( $_GET['message'] );
					$messages[1] =  __( 'Product updated.' );
					echo $messages[$message];			
					unset($_GET['message']);
				}
				
				
				$_SERVER['REQUEST_URI'] = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted', 'message', 'duplicated'), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
		<?php } ?>
		
		<?php		 
			$unwriteable_directories = Array();
			
			if(!is_writable(WPSC_FILE_DIR)) {
				$unwriteable_directories[] = WPSC_FILE_DIR;
			}
			
			if(!is_writable(WPSC_PREVIEW_DIR)) {
				$unwriteable_directories[] = WPSC_PREVIEW_DIR;
			}
		
			if(!is_writable(WPSC_IMAGE_DIR)) {
				$unwriteable_directories[] = WPSC_IMAGE_DIR;
			}
			
			if(!is_writable(WPSC_THUMBNAIL_DIR)) {
				$unwriteable_directories[] = WPSC_THUMBNAIL_DIR;
			}
			
			if(!is_writable(WPSC_CATEGORY_DIR)) {
				$unwriteable_directories[] = WPSC_CATEGORY_DIR;
			}
			
			if(!is_writable(WPSC_UPGRADES_DIR)) {
				$unwriteable_directories[] = WPSC_UPGRADES_DIR;
			}
				
			if(count($unwriteable_directories) > 0) {
				echo "<div class='error fade'>".str_replace(":directory:","<ul><li>".implode($unwriteable_directories, "</li><li>")."</li></ul>",__('The following directories are not writable: :directory: You won&#39;t be able to upload any images or files here. You will need to change the permissions on these directories to make them writable.', 'wpsc'))."</div>";
			}
			// class='stuffbox'
	?>
		
		<div id="col-container">
			<div id="wpsc-col-right">			
				<div id='poststuff' class="col-wrap">
					<form id="modify-products" method="post" action="" enctype="multipart/form-data" >
					<?php
						$product_id = absint($_GET['product_id']);
						wpsc_display_product_form($product_id);
					?>
					</form>
				</div>
			</div>
			
			<div id="wpsc-col-left">
				<div class="col-wrap">		
					<?php
						wpsc_admin_products_list($category_id);
					?>
				</div>
			</div>
		</div>

	</div>
	<script type="text/javascript">
	/* <![CDATA[ */
	(function($){
		$(document).ready(function(){
			$('#doaction, #doaction2').click(function(){
				if ( $('select[name^="action"]').val() == 'delete' ) {
					var m = '<?php echo js_escape(__("You are about to delete the selected products.\n  'Cancel' to stop, 'OK' to delete.")); ?>';
					return showNotice.warn(m);
				}
			});
		});
	})(jQuery);
	/* ]]> */
	</script>
	<?php
}


function wpsc_admin_products_list($category_id = 0) {
  global $wpdb,$_wp_column_headers;
  // set is_sortable to false to start with
  $is_sortable = false;
  $page = null;
  
  $search_input = '';

	if($_GET['search']) {
		$search_input = stripslashes($_GET['search']);

		$search_string = "%".$wpdb->escape($search_input)."%";
		
		$search_sql = "AND (`products`.`name` LIKE '".$search_string."' OR `products`.`description` LIKE '".$search_string."')";

	} else {
		$search_sql = '';
	}

	$search_sql = apply_filters('wpsc_admin_products_list_search_sql', $search_sql);

	if($category_id > 0) {  // if we are getting items from only one category, this is a monster SQL query to do this with the product order
		$sql = "SELECT `products`.`id` , `products`.`name` , `products`.`price` , `products`.`image`,`products`.`weight` , `products`.`publish`, `categories`.`category_id`,`order`.`order`, IF(ISNULL(`order`.`order`), 0, 1) AS `order_state`
			FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products`
			LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `categories` ON `products`.`id` = `categories`.`product_id` 
			LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` AS `order` ON ( 
				(	`products`.`id` = `order`.`product_id` )
			AND 
				( `categories`.`category_id` = `order`.`category_id` )
			)
			WHERE `products`.`active` = '1' $search_sql
			AND `categories`.`category_id` 
			IN (
			'".$category_id."'
			)
			ORDER BY `order_state` DESC,`order`.`order` ASC,  `products`.`date_added` DESC,  `products`.`id` DESC";
	  
		// if we are selecting a category, set is_sortable to true
		$is_sortable = true;
	} else {
		$itempp = 10;
		
		if ($_GET['pageno']!='all') {
		  if($_GET['pageno'] > 0) {
				$page = absint($_GET['pageno']);
		  } else {
		    $page = 1;
		  }
			$start = (int)($page * $itempp) - $itempp;
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE `products`.`active`='1' $search_sql ORDER BY `products`.`date_added` DESC LIMIT $start,$itempp";
			if(get_option('wpsc_sort_by') == 'dragndrop'){
				$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` AS `order` ON `products`.`id`= `order`.`product_id` WHERE `products`.`active`='1' AND `order`.`category_id`='0' $search_sql ORDER BY `order`.`order`";
			}
		
		} else {
				$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE `products`.`active`='1' $search_sql ORDER BY `products`.`date_added`";

		}

	}  
			//	exit($sql);
	$product_list = $wpdb->get_results($sql,ARRAY_A);
	//exit('<pre>'.print_r($product_list, true).'</pre>');
	$num_products = $wpdb->get_var("SELECT COUNT(DISTINCT `products`.`id`) FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` WHERE `products`.`active`='1' $search_sql");
	
	if (isset($itempp)) {
		$num_pages = ceil($num_products/$itempp);
	}
	
	if($page !== null) {
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pageno', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $num_pages,
			'current' => $page
		));
	}
	$this_page_url = stripslashes($_SERVER['REQUEST_URI']);
  
	?>
	<div class="wpsc-separator"><br/></div>
	
	<div class="tablenav">
		
		
		
		
		<?php	if(get_option('wpsc_sort_by') != 'dragndrop'){ ?>
		<div class="tablenav-pages">
			<?php
				echo $page_links;
			?>	
		</div>
		<?php } ?>
		
		<div class="alignleft actions">
			<form action="admin.php" method="get">
				<?php
					echo wpsc_admin_category_dropdown();
				?>
			</form>
		</div>	
	</div>
	
	
	<form id="posts-filter" action="" method="get">
		<div class="tablenav">	
			<div class="alignright search-box">
				<input type='hidden' name='page' value='wpsc-edit-products'  />
				<input type="text" class="search-input" id="page-search-input" name="search" value="<?php echo $search_input; ?>" />
				<input type="submit" name='wpsc_search' value="<?php _e( 'Search' ); ?>" class="button" />
			</div>
		
			<div class="alignleft actions">
					<select name="bulkAction">
						<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
						<option value="delete"><?php _e('Delete'); ?></option>
						<option value="show"><?php _e('Publish'); ?></option>
						<option value="hide"><?php _e('Draft'); ?></option>

					</select>
					<input type='hidden' name='wpsc_admin_action' value='bulk_modify' />
					<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
					<?php wp_nonce_field('bulk-products', 'wpsc-bulk-products'); ?>
			</div>
		</div>
	
		<input type='hidden' id='products_page_category_id'  name='category_id' value='<?php echo $category_id; ?>' />
		<table class="widefat page fixed" id='wpsc_product_list' cellspacing="0">
			<thead>
				<tr>
					<?php print_column_headers('display-product-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
					<?php print_column_headers('display-product-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody>
				<?php
				if(count($product_list) > 0) {
					foreach((array)$product_list as $product) {
						//first set the patch to the default
						$image_path = WPSC_URL."/images/no-image-uploaded.gif";
						if(is_numeric($product['image'])) { // check for automatic thumbnail images
							// file_exists(WPSC_THUMBNAIL_DIR.$product['image'])
							$product_image = $wpdb->get_var("SELECT `image` FROM  `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$product['image']}' LIMIT 1");
							// if the image exists, set the image path to it.
							if(($product_image != null) && file_exists(WPSC_THUMBNAIL_DIR.$product_image)) {
								$image_path = WPSC_THUMBNAIL_URL.$product_image;  
							}
						}
						
						// get the  product name, unless there is no name, in which case, display text indicating so
						if ($product['name']=='') {
							$product_name = "(".__('No Name', 'wpsc').")";
						} else {
							$product_name = htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8');
						}
						

					$category_html = '';	
					if(get_option('wpsc_sort_by') != 'dragndrop'){
					$category_list = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id`,`".WPSC_TABLE_PRODUCT_CATEGORIES."`.`name` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` , `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` IN ('".$product['id']."') AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` AND `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`active` IN('1')",ARRAY_A);
					}else{
					$category_list = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id`,`".WPSC_TABLE_PRODUCT_CATEGORIES."`.`name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ON `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id`= `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` WHERE `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` IN ('".$product['product_id']."')  AND `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`active` IN('1')",ARRAY_A);
					}
					$i = 0;
					foreach((array)$category_list as $category_row) {
						if($i > 0) {
							
							$category_html .= "<br />";
						}
						
						
						$category_html .= "<a class='category_link' href='". htmlentities(remove_query_arg('product_id',add_query_arg('category_id', $category_row['id'])))."'>".stripslashes($category_row['name'])."</a>";
						$i++;
					}        
									
								if(get_option('wpsc_sort_by') == 'dragndrop'){ 
									$product['id'] = $product['product_id'];
								}
								
								
						?>
							<tr class="product-edit <?php echo ( wpsc_publish_status($product['id']) ) ? ' wpsc_published' : ' wpsc_not_published'; ?>" id="product-<?php echo $product['id']?>" >
									<th class="check-column" scope="row">
										<input type='checkbox' name='product[]' class='deletecheckbox' value='<?php echo $product['id'];?>' title="ID #<?php echo $product['id']; ?>" />
										<?php echo do_action('wpsc_admin_product_checkbox', $product['id']); ?>
									</th>
									
									
									<td class="product-image ">
										<img title='Drag to a new position' src='<?php echo $image_path; ?>' alt='<?php echo $product['name']; ?>' width='38' height='38' />
									</td>
									<td class="product-title column-title">
									  <?php
									  $edit_product_url = wp_nonce_url(htmlentities(add_query_arg('product_id', $product['id'])), 'edit_product_' . $product['id']);
									  ?>
										<a class='edit-product' href='<?php echo $edit_product_url; ?>' title="ID #<?php echo $product['id']; ?>: <?php echo $product_name; ?>"><?php echo $product_name; ?></a>
											<?php
											if($product['publish'] != 1 ) {
												?> - <strong> <?php 	_e('Draft', 'wpsc'); ?>	</strong>	<?php
											}
											?>
											<?php
											$product_alert = apply_filters('wpsc_product_alert', array(false, ''), $product);
											if(count($product_alert['messages']) > 0) {
												$product_alert['messages'] = implode("\n",(array)$product_alert['messages']);
											}
											if($product_alert['state'] === true) {
												?>
												<img alt='<?php echo $product_alert['messages'];?>' title='<?php echo $product_alert['messages'];?>' class='product-alert-image' src='<?php echo  WPSC_URL;?>/images/product-alert.jpg' alt='' />
												<?php
											}
											
											// If a product alert has stuff to display, show it.
											// Can be used to add extra icons etc
											if ( !empty( $product_alert['display'] ) ) {
												echo $product_alert['display'];
											}
											
											?>
											<img class='loadingImg' style='display:none;' src='<?php echo get_option('siteurl'); ?>/wp-admin/images/wpspin_light.gif' alt='loading' />
									
									
										<div class="wpsc-row-actions">
											<span class="edit">
												<a class='edit-product' title="Edit this post" href='<?php echo $edit_product_url; ?>' style="cursor:pointer;">Edit</a>
											</span>
											 |
											<span class="delete">
												<a class='submitdelete delete_button'
													title='<?php echo attribute_escape(__('Delete this product', 'wpsc')); ?>'
													href='<?php echo wp_nonce_url("admin.php?wpsc_admin_action=delete_product&amp;product={$product['id']}", 'delete_product_' . $product['id']); ?>'
													onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this product '%s'\n 'Cancel' to stop, 'OK' to delete."), $product['name'] )) ?>') ) { return true;}return false;"
													>
													<?php _e('Delete') ?>
												</a>
											</span>
											 |
											<span class="view">
												<a target="_blank" rel="permalink" title='View <?php echo $product_name; ?>' href="<?php echo wpsc_product_url($product['id']); ?>">View</a>
											</span>
											|
											<span class="view">
												<a rel="permalink"
													title='Duplicate <?php echo $product_name; ?>'
													href="<?php echo wp_nonce_url("admin.php?wpsc_admin_action=duplicate_product&amp;product={$product['id']}", 'duplicate_product_' . $product['id']); ?>
													">
													Duplicate
												</a>
											</span>
											|
											<span class="publish_toggle">
												<a title="Change publish status"
													href="<?php echo wp_nonce_url("admin.php?wpsc_admin_action=toggle_publish&product=".$product['id'], 'toggle_publish_'.$product['id']); ?>"
													>
													<?php
													if($product['publish'] == 1 ) {
														_e('Unpublish', 'wpsc');
													} else {
														_e('Publish', 'wpsc');
													}
													?>
												</a>
											</span>
										</div>
									</td>
									
									<td class="product-price column-price">

									<?php echo nzshpcrt_currency_display($product['price'], 1); ?>
									<div class='price-editing-fields' id='price-editing-fields-<?php echo $product['id']; ?>'>
										<input type='text' class='the-product-price' name='product_price[<?php echo $product['id']; ?>][price]' value='<?php echo number_format($product['price'],2,'.',''); ?>' />
										<input type='hidden' name='product_price[<?php echo $product['id']; ?>][id]' value='<?php echo $product['id']; ?>' />
										<input type='hidden' name='product_price[<?php echo $product['id']; ?>][nonce]' value='<?php echo wp_create_nonce('edit-product_price-'.$product['id']); ?>' />
										
									
									</div>
									</td>
									<td class="column-categories"><?php echo $category_html; ?></td>
							</tr>
						<?php
					}
				} else {
				?>
				<tr>
					<td colspan='5'>
					  <?php _e("You have no products added."); ?>
					</td>
				</tr>
				<?php
				}
				?>			
			</tbody>
		</table>
	</form>
	<?php
}

function wpsc_admin_category_dropdown() {
	global $wpdb,$category_data;
	$siteurl = get_option('siteurl');
	$url =  urlencode(remove_query_arg(array('product_id','category_id')));
	
	$options = "<option value=''>".__('View All Categories', 'wpsc')."</option>\r\n";
	$options .= wpsc_admin_category_dropdown_tree(null, 0, absint($_GET['category_id']));
	
	$concat = "<input type='hidden' name='page' value='{$_GET['page']}' />\r\n";
	$concat .= "<select name='category_id' id='category_select'>".$options."</select>\r\n";
	$concat .= "<button class='button' id='submit_category_select'>Filter</button>\r\n";
	return $concat;
}

function wpsc_admin_category_dropdown_tree($category_id = null, $iteration = 0, $selected_id = null) {
		/*
   * Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
   */
  global $wpdb;
  $siteurl = get_option('siteurl');
  $url = $siteurl."/wp-admin/admin.php?page=wpsc-edit-products";

	$search_sql = apply_filters('wpsc_admin_category_dropdown_tree_search_sql', '');

  if(is_numeric($category_id)) {
    $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '$category_id' ".$search_sql." ORDER BY `id` ASC";
	} else {
    $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '0' ".$search_sql." ORDER BY `id` ASC";
	}

//	echo $sql;
  $values = $wpdb->get_results($sql, ARRAY_A);

  foreach((array)$values as $option) {
    if($selected_id == $option['id']) {
      $selected = "selected='selected'";
    }
    //$url = htmlentities(remove_query_arg('product_id',add_query_arg('category_id', $option['id'])));
    $output .= "<option $selected value='{$option['id']}'>".str_repeat("-", $iteration).stripslashes($option['name'])."</option>\r\n";
    $output .= wpsc_admin_category_dropdown_tree($option['id'], $iteration+1, $selected_id);
    $selected = "";
  }
  return $output;
}

?>