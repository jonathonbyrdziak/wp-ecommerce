
<div class='wpsc_categorisation_group' id='categorisation_group_<?php echo $categorisation_group['id']; ?>'>
	<?php if(count($categorisation_groups) > 1) :  // no title unless multiple category groups ?>
	<h4 class='wpsc_category_title'><?php echo $categorisation_group['name']; ?></h4>
	<?php endif; ?>
	
	<?php if(get_option('wpsc_category_grid_view') != 1) :  // if category grid view is off?>
		<ul class='wpsc_categories wpsc_top_level_categories <?php echo implode(" ", (array)$provided_classes); ?>'>
			<?php wpsc_start_category_query($category_settings); ?>
					<li class='wpsc_category_<?php wpsc_print_category_id();?>'>
						<a href="<?php wpsc_print_category_url();?>" class='wpsc_category_image_link'>
							<?php wpsc_print_category_image(45, 25); ?>
						</a>

						<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link <?php wpsc_print_category_classes(); ?>">
							<?php wpsc_print_category_name();?>
							<?php if (get_option('show_category_count') == 1) : ?>
								<?php wpsc_print_category_products_count("(",")"); ?>
							<?php endif;?>
						</a>
						<?php wpsc_print_product_list(); ?>
						<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
					</li>
			<?php wpsc_end_category_query(); ?>
			
		</ul>
	<?php else:  // if category grid view is on?>
			<div class='wpsc_categories wpsc_category_grid'>
				<?php wpsc_start_category_query($category_settings); ?>
					<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_grid_item <?php wpsc_print_category_classes(); ?>" title='<?php wpsc_print_category_name();?>'>
						<?php wpsc_print_category_image(45, 45); ?>
					</a>
					<?php wpsc_print_subcategory("", ""); ?>
				<?php wpsc_end_category_query(); ?>
				<div class='clear_category_group'></div>
			</div>
	
	<?php endif;?>
	<div class='clear_category_group'></div>
</div>