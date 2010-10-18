<?php
/**
 * Product Categories widget class
 *
 * @since 3.7.1
 */
class WP_Widget_Product_Categories extends WP_Widget {

	function WP_Widget_Product_Categories() {

		$widget_ops = array('classname' => 'widget_wpsc_categorisation', 'description' => __('Product Grouping Widget', 'wpsc'));
		$this->WP_Widget('wpsc_categorisation', __('Product Categories','wpsc'), $widget_ops);
	}

	function widget( $args, $instance ) {
	  global $wpdb, $wpsc_theme_path;
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Product Categories' ) : $instance['title']);
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		$selected_categorisations = array_keys((array)$instance['categorisation'], true);	
		if($selected_categorisations != null) {
			foreach($selected_categorisations as $key => $selected_categorisation) {
				$selected_categorisations[$key] = (int)$selected_categorisation;
			}
			$selected_values = implode(',',$selected_categorisations);
		} else {
			$selected_values = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1') AND `default` IN ('1') LIMIT 1 ");
		}


		$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `id` IN ({$selected_values}) AND `active` IN ('1')", ARRAY_A);
		foreach($categorisation_groups as $categorisation_group) {
			$category_settings = array();
			$category_settings['category_group'] = $categorisation_group['id'];
			$category_settings['show_thumbnails'] = $instance['image'];
			$category_settings['order_by'] =  array("column" => 'name', "direction" =>'asc');
			$provided_classes = array();
			if($category_settings['show_thumbnails'] == 1) {
				$provided_classes[] = "category_images";
			}
			//echo wpsc_get_theme_file_path("category_widget.php");
			include(wpsc_get_theme_file_path("category_widget.php"));
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['categorisation'] = $new_instance['categorisation'];
		$instance['image'] = $new_instance['image'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
	  global $wpdb;
		//Defaults
		$instance = wp_parse_args((array) $instance, array( 'title' => ''));
		$title = esc_attr( $instance['title'] );
		$image = (bool) $instance['image'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
		<?php
		$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')", ARRAY_A);
			foreach($categorisation_groups as $cat_group) {
				$category_state = false;
				$category_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('{$cat_group['id']}')");
				$category_state = (bool)$instance['categorisation'][$cat_group['id']];
				?>
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('categorisation')."-{$cat_group['id']}"; ?>" name="<?php echo $this->get_field_name('categorisation')."[{$cat_group['id']}]"; ?>"<?php checked($category_state); ?> />
					<label for="<?php echo $this->get_field_id('categorisation')."-{$cat_group['id']}"; ?>"><?php echo str_replace(":category:",$cat_group['name'],__('Display the :category: Group', 'wpsc')); ?></label><br />
				<?php
			}
			?>
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>"<?php checked( $image ); ?> />
			<label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Display the Group thumbnails in the sidebar', 'wpsc'); ?></label><br />
		</p>
<?php
	}

}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Product_Categories");'));
?>