<?php
global $wpsc_query, $wpdb;
$image_width = get_option('single_view_image_width');
$image_height = get_option('single_view_image_height');
?>
<div id='products_page_container' class="wrap wpsc_container">
	
	
	<?php if(wpsc_has_breadcrumbs()) :?>
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
	
	<div class="productdisplay">
	<?php /** start the product loop here, this is single products view, so there should be only one */?>
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
			<div class="single_product_display product_view_<?php echo wpsc_the_product_id(); ?>">
				<div class="textcol">
				<?php if(get_option('show_thumbnails')) :?>
					<div class="imagecol">
						<?php if(wpsc_the_product_thumbnail()) :?> 	   
								<a rel="<?php echo str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), wpsc_the_product_title()); ?>" class="thickbox preview_link" href="<?php echo wpsc_the_product_image(); ?>">
									<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_image($image_width, $image_height); ?>"/>
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
						<h2 class="prodtitles"><span><?php echo wpsc_the_product_title(); ?></span><?php echo wpsc_edit_the_product_link(); ?></h2>
							<?php				
								do_action('wpsc_product_before_description', wpsc_the_product_id(), $wpsc_query->product);
							?>
						
						
						<div class="wpsc_description"><?php echo wpsc_the_product_description(); ?></div>
		
						<?php
							do_action('wpsc_product_addons', wpsc_the_product_id());
						?>
						<?php if(wpsc_the_product_additional_description()) : ?>
						<div class="single_additional_description">
            <?php
							$value = '';
							$the_addl_desc = wpsc_the_product_additional_description();
							if( is_serialized($the_addl_desc) ) {
								$addl_descriptions = @unserialize($the_addl_desc);
							} else {
								$addl_descriptions = array('addl_desc', $the_addl_desc);
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
					<?php endif; ?>

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
					
					
					<form class='product_form' enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="1" id="product_<?php echo wpsc_the_product_id(); ?>">
					<?php if(wpsc_product_has_personal_text()) : ?>
						<div class='custom_text'>
							<h4><?php echo __('Personalize your product', 'wpsc'); ?></h4>
							<?php echo __('Complete this form to include a personalized message with your purchase.', 'wpsc'); ?><br />
							<input type='text' name='custom_text' value=''  />
						</div>
					<?php endif; ?>
					
					<?php if(wpsc_product_has_supplied_file()) : ?>
						<div class='custom_file'>
							<h4><?php echo __('Upload a File', 'wpsc'); ?></h4>
							<?php echo __('Select a file from your computer to include with this purchase.  ', 'wpsc'); ?><br />
							<input type='file' name='custom_file' value=''  />
						</div>
					<?php endif; ?>
					
					
					<?php /** the variation group HTML and loop */?>
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
									
					
					<!-- THIS IS THE QUANTITY OPTION MUST BE ENABLED FROM ADMIN SETTINGS -->
					<?php if(wpsc_has_multi_adding()): ?>
					<fieldset>
						<label class='wpsc_quantity_update' for='wpsc_quantity_update[<?php echo wpsc_the_product_id(); ?>]'><?php echo __('Quantity', 'wpsc'); ?>:</label>
						
						<input type="text" id='wpsc_quantity_update[<?php echo wpsc_the_product_id(); ?>]' name="wpsc_quantity_update" size="2" value="1"/>
						<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
						<input type="hidden" name="wpsc_update_quantity" value="true"/>
					</fieldset>
					<?php endif ;?>
					
						<div class="wpsc_product_price">
							<?php if(wpsc_product_is_donation()) : ?>
								<label for='donation_price_<?php echo wpsc_the_product_id(); ?>'><?php echo __('Donation', 'wpsc'); ?>:</label>
								<input type='text' id='donation_price_<?php echo wpsc_the_product_id(); ?>' name='donation_price' value='<?php echo $wpsc_query->product['price']; ?>' size='6' />
								<br />
							
							
							<?php else : ?>
								<?php if(wpsc_product_on_special()) : ?>
									<span class='oldprice'><?php echo __('Price', 'wpsc'); ?>: <?php echo wpsc_product_normal_price(); ?></span><br />
								<?php endif; ?>
								  <span id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_the_product_price(); ?></span><?php echo __('Price', 'wpsc'); ?>:<br/>
								<?php if(wpsc_product_has_multicurrency()) : ?>
									<?php echo wpsc_display_product_multicurrency(); ?>
								<?php endif; ?>
								<?php if(get_option('display_pnp') == 1) : ?>
									<span class="pricedisplay"><?php echo wpsc_product_postage_and_packaging(); ?></span><?php echo __('P&amp;P', 'wpsc'); ?>:  <br />
								<?php endif; ?>						
							<?php endif; ?>
						</div>
					<?php if(function_exists('wpsc_akst_share_link') && (get_option('wpsc_share_this') == 1)) {
						echo wpsc_akst_share_link('return');
					} ?>
						
					<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
					<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
							
					<?php if(wpsc_product_is_customisable()) : ?>				
						<input type="hidden" value="true" name="is_customisable"/>
					<?php endif; ?>
					
					
					<!-- END OF QUANTITY OPTION -->
					<?php if((get_option('hide_addtocart_button') == 0) && (get_option('addtocart_or_buynow') !='1')) : ?>
						<?php if(wpsc_product_has_stock()) : ?>
							<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
										<?php	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
										<input class="wpsc_buy_button" type='button' value='<?php echo __('Buy Now', 'wpsc'); ?>' onclick='gotoexternallink("<?php echo $action; ?>")'>
										<?php else: ?>
									<input type='image' src='<?php echo WPSC_URL; ?>/themes/iShop/images/buy_button.gif' id='product_<?php echo wpsc_the_product_id(); ?>_submit_button' class='wpsc_buy_button' name='Buy'  value="<?php echo __('Add To Cart', 'wpsc'); ?>" />

										<?php endif; ?>
														<div class='wpsc_loading_animation'>
								<img title="Loading" alt="Loading" src="<?php echo WPSC_URL; ?>/images/indicator.gif" class="loadingimage"/>
								<?php echo __('Updating cart...', 'wpsc'); ?>
							</div>
							
						<?php else : ?>
							<p class='soldout'><?php echo __('This product has sold out.', 'wpsc'); ?></p>
						<?php endif ; ?>
					<?php endif ; ?>
					</form>
					
					<?php if((get_option('hide_addtocart_button') == 0) && (get_option('addtocart_or_buynow') =='1')) : ?>
						<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>
					<?php endif ; ?>
					
					<?php echo wpsc_product_rater(); ?>
						
						
					<?php
						if(function_exists('gold_shpcrt_display_gallery')) :
					
							echo gold_shpcrt_display_gallery(wpsc_the_product_id());
						endif;
					?>
					</div>
		
					<form onsubmit="submitform(this);return false;" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_extra_<?php echo wpsc_the_product_id(); ?>">
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="prodid"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="item"/>
					</form>
				</div>
			</div>
		</div>
		
		<?php echo wpsc_product_comments(); ?>
<?php endwhile; ?>
<?php /** end the product loop here */?>

		<?php
		if(function_exists('fancy_notifications')) {
			echo fancy_notifications();
		}
		?>
		<div class="clear"></div>
</div>