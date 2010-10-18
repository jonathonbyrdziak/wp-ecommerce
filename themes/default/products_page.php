<?php
global $wpsc_query, $wpdb;
/*
 * Most functions called in this page can be found in the wpsc_query.php file
 */
?>
<div id='products_page_container' class="wrap wpsc_container">

<?php if(wpsc_has_breadcrumbs()) : ?>
		<div class='breadcrumb'>
			<a href='<?php echo get_option('product_list_url'); ?>'><?php echo get_option('blogname'); ?></a> &raquo;
			<?php while (wpsc_have_breadcrumbs()) : wpsc_the_breadcrumb(); ?>
				<?php if(wpsc_breadcrumb_url()) :?> 	   
					<a href='<?php echo wpsc_breadcrumb_url(); ?>'><?php echo wpsc_breadcrumb_name(); ?></a> &raquo;
				<?php else: ?> 
					<?php echo wpsc_breadcrumb_name(); ?>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
	
	<?php do_action('wpsc_top_of_products_page'); // Plugin hook for adding things to the top of the products page, like the live search ?>
	<?php if(wpsc_display_categories()): ?>
	  <?php if(get_option('wpsc_category_grid_view') == 1) :?>
			<div class='wpsc_categories wpsc_category_grid'>
				<?php wpsc_start_category_query(array('category_group'=> get_option('wpsc_default_category'), 'show_thumbnails'=> 1)); ?>
					<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_grid_item" title='<?php wpsc_print_category_name();?>'>
						<?php wpsc_print_category_image(45, 45); ?>
					</a>
					<?php wpsc_print_subcategory("", ""); ?>
				<?php wpsc_end_category_query(); ?>
				<div class='clear_category_group'></div>
			</div>
	  <?php else:?>
			<ul class='wpsc_categories'>
				<?php wpsc_start_category_query(array('category_group'=>get_option('wpsc_default_category'), 'show_thumbnails'=> get_option('show_category_thumbnails'))); ?>
						<li>
							<?php wpsc_print_category_image(32, 32); ?>
							
							<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link"><?php wpsc_print_category_name();?></a>
							<?php if(get_option('wpsc_category_description')) :?>
								<?php wpsc_print_category_description("<div class='wpsc_subcategory'>", "</div>"); ?>				
							<?php endif;?>
							
							<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
						</li>
				<?php wpsc_end_category_query(); ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>



	
	<?php if(wpsc_display_products()): ?>
		<?php if(wpsc_is_in_category()) : ?>
			<div class='wpsc_category_details'>
				<?php if(get_option('show_category_thumbnails') && wpsc_category_image()) : ?>
					<img src='<?php echo wpsc_category_image(); ?>' alt='<?php echo wpsc_category_name(); ?>' title='<?php echo wpsc_category_name(); ?>' />
				<?php endif; ?>
				
				<?php if(get_option('wpsc_category_description') &&  wpsc_category_description()) : ?>
					<?php echo wpsc_category_description(); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		
		
		<!-- Start Pagination -->
		<?php if ( ( get_option( 'use_pagination' ) == 1 && ( get_option( 'wpsc_page_number_position' ) == 1 || get_option( 'wpsc_page_number_position' ) == 3 ) ) ) : ?>
			<div class="wpsc_page_numbers">
				<?php if ( wpsc_has_pages() ) : ?>
					Pages: <?php echo wpsc_first_products_link( '&laquo; First', true ); ?> <?php echo wpsc_previous_products_link( '&laquo; Previous', true ); ?> <?php echo wpsc_pagination( 10 ); ?> <?php echo wpsc_next_products_link( 'Next &raquo;', true ); ?> <?php echo wpsc_last_products_link( 'Last &raquo;', true ); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>		
		<!-- End Pagination -->
		
		
		<?php /** start the product loop here */?>
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
			<?php if(wpsc_category_transition()) :?>
		  	<h3 class='wpsc_category_boundary'>
		    <?php echo wpsc_current_category_name(); ?>
				</h3>
			<?php endif; ?>
			<div class="productdisplay default_product_display product_view_<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?>">      
				<div class="textcol">
				
					<?php if(get_option('show_thumbnails')) :?>
						<div class="imagecol">
							<?php if(wpsc_the_product_thumbnail()) :?>
								<a rel="<?php echo str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), wpsc_the_product_title()); ?>" class="thickbox preview_link" href="<?php echo wpsc_the_product_image(); ?>">
									<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>
								</a>
							<?php else: ?>
								<div class="item_no_image">
									<a href="<?php echo wpsc_the_product_permalink(); ?>">
									<span>No Image Available</span>
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				
					<div class="producttext">
						<h2 class="prodtitles">
							<?php if(get_option('hide_name_link') == 1) : ?>
								<span><?php echo wpsc_the_product_title(); ?></span>
							<?php else: ?> 
								<a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
							<?php endif; ?> 				
							<?php echo wpsc_edit_the_product_link(); ?>
						</h2>
						<?php							
							do_action('wpsc_product_before_description', wpsc_the_product_id(), $wpsc_query->product);
							do_action('wpsc_product_addons', wpsc_the_product_id());
						?>
						
						
						<div class='wpsc_description'><?php echo wpsc_the_product_description(); ?></div>
				
						<?php if(wpsc_the_product_additional_description()) : ?>
						<div class='additional_description_span'>
							<a href='<?php echo wpsc_the_product_permalink(); ?>' class='additional_description_link'>
								<img class='additional_description_button'  src='<?php echo WPSC_URL; ?>/images/icon_window_expand.gif' title='Additional Description' alt='Additional Description' /><?php echo __('More Details', 'wpsc'); ?>
							</a>
							<div class='additional_description'>
								<?php
									$value = '';
									$the_addl_desc = wpsc_the_product_additional_description();
									if( is_serialized($the_addl_desc) ) {
										$addl_descriptions = @unserialize($the_addl_desc);
									} else {
										$addl_descriptions = array('addl_desc'=> $the_addl_desc);
									}
									
									if( isset($addl_descriptions['addl_desc']) ) {
										$value = $addl_descriptions['addl_desc'];
									}
								
									if( function_exists('wpsc_addl_desc_show') ) {
										echo wpsc_addl_desc_show( $addl_descriptions );
									} else {
										echo stripslashes( wpautop($the_addl_desc, $br=1));
									}
								?>
							</div>
							
							<br />
						</div>
						<?php endif; ?>
						
						<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
							<?php	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
						<?php else: ?>
						<?php	$action =  htmlentities(wpsc_this_page_url(),ENT_QUOTES); ?>					
						<?php endif; ?>
						
						<form class='product_form'  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
							<?php do_action('wpsc_product_addon_after_descr', wpsc_the_product_id()); ?>
							
							<?php /** the custom meta HTML and loop */?>
							<div class="custom_meta">
								<?php while (wpsc_have_custom_meta()) : wpsc_the_custom_meta(); 	
										if (stripos(wpsc_custom_meta_name(),'g:') !== FALSE){
											continue;
										}
								?>
									<strong><?php echo wpsc_custom_meta_name(); ?>: </strong><?php echo wpsc_custom_meta_value(); ?><br />
								<?php endwhile; ?>
							</div>
							<?php /** the custom meta HTML and loop ends here */?>
							
							<?php /** add the comment link here */?>
							<?php echo wpsc_product_comment_link();	?>
							
							
							<?php /** the variation group HTML and loop */?>
							<div class="wpsc_variation_forms">
								<?php while (wpsc_have_variation_groups()) : wpsc_the_variation_group(); ?>
									<p>
										<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>
										<?php /** the variation HTML and loop */?>
										<select class='wpsc_select_variation' name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">
										<?php while (wpsc_have_variations()) : wpsc_the_variation(); ?>
											<option value="<?php echo wpsc_the_variation_id(); ?>" <?php echo wpsc_the_variation_out_of_stock(); ?> ><?php echo wpsc_the_variation_name(); ?></option>
										<?php endwhile; ?>
										</select> 
									</p>
								<?php endwhile; ?>
							</div>
							<?php /** the variation group HTML and loop ends here */?>
							
						<!-- THIS IS THE QUANTITY OPTION MUST BE ENABLED FROM ADMIN SETTINGS -->
						<?php if(wpsc_has_multi_adding()): ?>
							<label class='wpsc_quantity_update' for='wpsc_quantity_update[<?php echo wpsc_the_product_id(); ?>]'><?php echo __('Quantity', 'wpsc'); ?>:</label>
							
							<input type="text" id='wpsc_quantity_update' name="wpsc_quantity_update[<?php echo wpsc_the_product_id(); ?>]" size="2" value="1"/>
							<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
							<input type="hidden" name="wpsc_update_quantity" value="true"/>
						<?php endif ;?>
							
							<p class="wpsc_extras_forms"/>
							<div class="wpsc_product_price">
								<?php if(wpsc_product_is_donation()) : ?>
									<label for='donation_price_<?php echo wpsc_the_product_id(); ?>'><?php echo __('Donation', 'wpsc'); ?>:</label>
									<input type='text' id='donation_price_<?php echo wpsc_the_product_id(); ?>' name='donation_price' value='<?php echo $wpsc_query->product['price']; ?>' size='6' />
									<br />
								
								
								<?php else : ?>
									<?php if(wpsc_product_on_special()) : ?>
										<span class='oldprice'><?php echo __('Price', 'wpsc'); ?>: <?php echo wpsc_product_normal_price(get_option('wpsc_hide_decimals')); ?></span><br />
									<?php endif; ?>
									<span id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_the_product_price(get_option('wpsc_hide_decimals')); ?></span><?php echo __('Price', 'wpsc'); ?>:  <br/>
									<?php if(get_option('display_pnp') == 1) : ?>
										<span class="pricedisplay"><?php echo wpsc_product_postage_and_packaging(get_option('wpsc_hide_decimals')); ?></span><?php echo __('P&amp;P', 'wpsc'); ?>:  <br />
									<?php endif; ?>							
								<?php endif; ?>
							</div>
							
							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
					
							<!-- END OF QUANTITY OPTION -->
							<?php if((get_option('hide_addtocart_button') == 0) &&  (get_option('addtocart_or_buynow') !='1')) : ?>
								<?php if(wpsc_product_has_stock()) : ?>
									<div class='wpsc_buy_button_container'>
											<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
											<?php 	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
											<input class="wpsc_buy_button" type='button' value='<?php echo __('Buy Now', 'wpsc'); ?>' onclick='gotoexternallink("<?php echo $action; ?>")'>
											<?php else: ?>
										<input type="submit" value="<?php echo __('Add To Cart', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/>
											<?php endif; ?>
										<div class='wpsc_loading_animation'>
											<img title="Loading" alt="Loading" src="<?php echo WPSC_URL; ?>/images/indicator.gif" class="loadingimage"/>
											<?php echo __('Updating cart...', 'wpsc'); ?>
										</div>
									</div>
								<?php else : ?>
									<p class='soldout'><?php echo __('This product has sold out.', 'wpsc'); ?></p>
								<?php endif ; ?>
							<?php endif ; ?>
						</form>
						
						<?php if((get_option('hide_addtocart_button') == 0) && (get_option('addtocart_or_buynow')=='1')) : ?>
							<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>
						<?php endif ; ?>
						
						<?php echo wpsc_product_rater(); ?>
						<?php
							if(function_exists('gold_shpcrt_display_gallery')) :					
								echo gold_shpcrt_display_gallery(wpsc_the_product_id(), true);
							endif;
							?>
					</div>
			</div>
		</div>

		<?php endwhile; ?>
		<?php /** end the product loop here */?>
		
		
		<?php if(wpsc_product_count() < 1):?>
			<p><?php  echo __('There are no products in this group.', 'wpsc'); ?></p>
		<?php endif ; ?>

	<?php

	if(function_exists('fancy_notifications')) {
		echo fancy_notifications();
	}
	?>
		
		
		<!-- Start Pagination -->
		<?php if ( ( get_option( 'use_pagination' ) == 1 && ( get_option( 'wpsc_page_number_position' ) == 2 || get_option( 'wpsc_page_number_position' ) == 3 ) ) ) : ?>
			<div class="wpsc_page_numbers">
				<?php if ( wpsc_has_pages() ) : ?>
					Pages: <?php echo wpsc_first_products_link( '&laquo; First', true ); ?> <?php echo wpsc_previous_products_link( '&laquo; Previous', true ); ?> <?php echo wpsc_pagination( 10 ); ?> <?php echo wpsc_next_products_link( 'Next &raquo;', true ); ?> <?php echo wpsc_last_products_link( 'Last &raquo;', true ); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<!-- End Pagination -->
		
		
	<?php endif; ?>
</div>