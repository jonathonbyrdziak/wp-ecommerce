<?php
global $wpsc_query, $wpdb;
$image_width = get_option('product_image_width');
$image_height = get_option('product_image_height');
?>
<div id='products_page_container' class="wpsc_container productdisplay example-category">

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
				<?php wpsc_start_category_query(array('category_group'=> get_option('wpsc_default_category'), 'show_thumbnails'=> get_option('show_category_thumbnails'))); ?>
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
					<div class="pagination-products-showing">Showing <?php echo wpsc_showing_products(); ?> of <?php echo wpsc_total_product_count(); ?> products</div>
					<div class="pagination-pages"><?php echo wpsc_first_products_link( '&laquo; First', true ); ?> <?php echo wpsc_previous_products_link( '&laquo; Previous', true ); ?> <?php echo wpsc_pagination( 10 ); ?> <?php echo wpsc_next_products_link( 'Next &raquo;', true ); ?> <?php echo wpsc_last_products_link( 'Last &raquo;', true ); ?></div>
				<?php endif; ?> 
			</div>
		<?php endif; ?>
		<!-- End Pagination -->
		
		
	<div class="product_grid_display">
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
			<div class="product_grid_item product_view_<?php echo wpsc_the_product_id(); ?>">
				  
				<?php if(wpsc_the_product_thumbnail()) :?> 	   
					<div class="item_image">
						<a href="<?php echo wpsc_the_product_permalink(); ?>">
							<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>" />
						</a>
					</div>
				<?php else: ?> 
					<div class="item_no_image">
						<a href="<?php echo wpsc_the_product_permalink(); ?>">
						<span>No Image Available</span>
						</a>
					</div>
				<?php endif; ?> 
				
					
				<?php if(get_option('show_images_only') != 1): ?>
					<div class="grid_product_info">
						<div class="product_text">
							<strong><?php echo wpsc_the_product_title(); ?></strong>
							
						<?php if((wpsc_the_product_description() != '') && (get_option('display_description') == 1)): ?>
							<p class='griddescription'><?php echo wpsc_the_product_description(); ?></p>
						<?php endif; ?>
							
							<br/>
							<span class="pricedisplay"><?php echo wpsc_the_product_price(get_option('wpsc_hide_decimals')); ?></span>Price: 
							
						<?php if(get_option('display_moredetails') == 1) : ?>
							<br />
							<a href='<?php echo wpsc_the_product_permalink(); ?>'>More Details</a>
						<?php endif; ?> 
						</div>
					</div>
					<div class="grid_more_info">
						<form class='product_form'  enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
							
							
							<?php if(get_option('display_variations') == 1) : ?>
								<?php /** the variation group HTML and loop */ ?>
								<div class="wpsc_variation_forms">
									<?php while (wpsc_have_variation_groups()) : wpsc_the_variation_group(); ?>
										<p>
											<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>
											<?php /** the variation HTML and loop */?>
											<select class='wpsc_select_variation' name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">
											<?php while (wpsc_have_variations()) : wpsc_the_variation(); ?>
												<option value="<?php echo wpsc_the_variation_id(); ?>"  <?php echo wpsc_the_variation_out_of_stock(); ?> ><?php echo wpsc_the_variation_name(); ?></option>
											<?php endwhile; ?>
											</select> 
										</p>
									<?php endwhile; ?>
								</div>
								<?php /** the variation group HTML and loop ends here */?>
							<?php endif; ?>
							
							<?php if((get_option('display_addtocart') == 1) && (get_option('addtocart_or_buynow') !='1')) :?> 	   
								<?php if(wpsc_product_has_stock()) : ?>
									<input type='image' src='<?php echo WPSC_URL; ?>/themes/iShop/images/buy_button.gif' id='product_<?php echo wpsc_the_product_id(); ?>_submit_button' class='wpsc_buy_button' name='Buy'  value="<?php echo __('Add To Cart', 'wpsc'); ?>" />
								<?php else : ?>
									<p class='soldout'><?php echo __('This product has sold out.', 'wpsc'); ?></p>
								<?php endif ; ?>
							<?php endif; ?>
							
						</form>		
					</div>
					
					<?php if((get_option('display_addtocart') == 1) && (get_option('addtocart_or_buynow') == '1')) :?> 	  
						<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>
					<?php endif ; ?>
					
				<?php endif; ?> 				
			</div>
			
			
			<?php if((get_option('grid_number_per_row') > 0) && ((($wpsc_query->current_product +1) % get_option('grid_number_per_row')) == 0)) :?>
			  <div class='grid_view_newline'></div>
			<?php endif ; ?>
		
			
			
		<?php endwhile; ?>
		
		<?php if(wpsc_product_count() < 1):?>
			<p><?php  echo __('There are no products in this group.', 'wpsc'); ?></p>
		<?php endif ; ?>
		
		
	</div>
		
		
		<!-- Start Pagination -->
		<?php if ( ( get_option( 'use_pagination' ) == 1 && ( get_option( 'wpsc_page_number_position' ) == 2 || get_option( 'wpsc_page_number_position' ) == 3 ) ) ) : ?>
			<div class="wpsc_page_numbers">
				<?php if ( wpsc_has_pages() ) : ?>
					<div class="pagination-pages"><?php echo wpsc_first_products_link( '&laquo; First', true ); ?> <?php echo wpsc_previous_products_link( '&laquo; Previous', true ); ?> <?php echo wpsc_pagination( 10 ); ?> <?php echo wpsc_next_products_link( 'Next &raquo;', true ); ?> <?php echo wpsc_last_products_link( 'Last &raquo;', true ); ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<!-- End Pagination -->
		
		
	<?php endif; ?>
	
	
	<?php

	if(function_exists('fancy_notifications')) {
		echo fancy_notifications();
	}
	?>
</div>