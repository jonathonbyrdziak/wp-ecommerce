<?php
function widget_wpsc_categorisation( $args, $widget_args = 1 ) {
	global $wpdb;
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$option_name = 'widget_wpsc_categorisation';
	$options = get_option($option_name);
	if ( !isset($options[$number]) )
		return;
		
	$my_options = $options[$number];
	
	
  $title = empty($my_options['title']) ? __('Categories', 'wpsc') : $my_options['title'];
	
	echo $before_widget;
  $full_title = $before_title . $title . $after_title;
  echo $full_title;
  $selected_categorisations = array_keys($my_options['categorisation'], true);
  if($selected_categorisations != null) {
		foreach($selected_categorisations as $key => $selected_categorisation) {
			$selected_categorisations[$key] = (int)$selected_categorisation;
		}
		$selected_values = implode(',',$selected_categorisations);
		
	  $categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `id` IN ({$selected_values}) AND `active` IN ('1')", ARRAY_A);
	  foreach($categorisation_groups as $categorisation_group) {
	    echo "<div id='categorisation_group_".$categorisation_group['id']."'>\n\r";
	    if(count($categorisation_groups) > 1) {  // no title unless multiple category groups
			  echo "<h2 class='categorytitle'>{$categorisation_group['name']}</h2>\n\r";
			}
			show_cats_brands($categorisation_group['id'], 'sidebar', 'name', $my_options['image']);
		  echo "\n\r";
			echo "</div>\n\r";
	  }
		//echo("<pre>".print_r($selected_categorisations,true)."</pre>");
  } else {
    show_cats_brands(null, 'sidebar');
  }

	echo $after_widget;
}

// Displays form for a particular instance of the widget.  Also updates the data after a POST submit
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function widget_wpsc_categorisation_control( $widget_args = 1 ) {
	global $wp_registered_widgets, $wpdb;
	static $updated = false; // Whether or not we have already updated the data after a POST submit
	$option_name = 'widget_wpsc_categorisation';
	
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option($option_name);
	if ( !is_array($options) )
		$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( $option_name == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "categorisation-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "categorisation-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST[$option_name] as $widget_number => $widget_wpsc_categorisation_instance ) {
			// compile data from $widget_wpsc_categorisation_instance
			if ((!isset($widget_wpsc_categorisation_instance['title']) && isset($options[$widget_number])) || ($options[$widget_number]['check'] == 1))  {// user clicked cancel or no changes made
				continue;
			}
			$options[$widget_number]['title'] = wp_specialchars($widget_wpsc_categorisation_instance['title']);
			$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')", ARRAY_A);
			
			
			foreach($categorisation_groups as $cat_group){
				if($widget_wpsc_categorisation_instance['categorisation'][$cat_group['id']] == "true") {
					$options[$widget_number]['categorisation'][$cat_group['id']] = true;
				} else {
					$options[$widget_number]['categorisation'][$cat_group['id']] = false;
				}
			}
			
			if ($widget_wpsc_categorisation_instance['image'] == "true") {
				$options[$widget_number]['image'] = true;
			} else {
				$options[$widget_number]['image']  = false;
			}
		}

		update_option($option_name, $options);
		$updated = true; // So that we don't go through this more than once
	}





	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$something = '';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
	}
  
  
  //echo "<pre>".print_r($_POST,true)."</pre>";
  //echo "<pre>".print_r($options,true)."</pre>";
  
  
	echo "<p>\n\r";
	echo "  <label for='{$option_name}-{$number}-title'>".__('Title:')."<input class='widefat' id='{$option_name}-{$number}-title' name='{$option_name}[{$number}][title]' type='text' value='{$title}' /></label>\n\r";
	echo "  <input type='hidden' id='widget-categorisation-submit-$number' name='{$option_name}[$number][submit]' value='1' />\n\r";
	echo "</p>\n\r";
	
	echo "<p>\n\r";
         
	$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')", ARRAY_A);
	
	foreach($categorisation_groups as $cat_group){
	  $checked = '';
	  //$checked = "checked='checked'";
		$category_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('{$cat_group['id']}')");
		//$category_group_name = str_replace("[categorisation]", , __('Display &quot;[categorisation]&quot;', 'wpsc'));
		
		if($options[$number]['categorisation'][$cat_group['id']] == true) {
			$checked = "checked='checked'";
		}
		
		if($category_count <1) {
		  // if count of items is less than 1, disable it, but do it later, not a vital feture
		  //$checked = "disabled='true' ";
		}
		$form_id = "{$option_name}-{$number}-group{$cat_group['id']}";
		echo "	<label for='{$form_id}'>\n\r";
		echo "		<input type='checkbox' name='{$option_name}[$number][categorisation][{$cat_group['id']}]' id='{$form_id}' value='true' class='checkbox' {$checked} />\n\r";
		echo "		".str_replace(":category:",$cat_group['name'],__('Display the :category: Group', 'wpsc'))."</label>\n\r";
		echo "	<br/>\n\r";
	}
	if ($options[$number]['image'] == true) {
		$checked = "checked='checked'";
	}
	echo "<br />\n\r";
	echo "	<label for='sidebar_category_image'>\n\r";
	echo "		<input type='checkbox' name='{$option_name}[$number][image]' id='sidebar_category_image' value='true' class='checkbox' {$checked} />\n\r";
	echo "		".__('Display the Group thumbnails in the sidebar', 'wpsc')."</label>\n\r";
	echo "	<br/>\n\r";
	echo "		<input type='hidden' name='{$option_name}[$number][check]' value='1' />\n\r";
}
	

// Registers each instance of our widget on startup
function widget_wpsc_categorisation_register() {
	$option_name = 'widget_wpsc_categorisation';
	if ( !$options = get_option($option_name))
		$options = array();
	$widget_ops = array('classname' => 'widget_wpsc_categorisation', 'description' => __(__('Product Grouping Widget', 'wpsc')));
	$control_ops = array('width' => 232, 'height' => 350, 'id_base' => 'wpsc_categorisation');
	$name = __("Product Categories", 'wpsc');

	$registered = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) ) // we used 'something' above in our example.  Replace with with whatever your real data are.
			continue;
		
		// $id should look like {$id_base}-{$o}
		$id = "wpsc_categorisation-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget( $id, $name, 'widget_wpsc_categorisation', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'widget_wpsc_categorisation_control', $control_ops, array( 'number' => $o ) );
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'wpsc_categorisation-1', $name, 'widget_wpsc_categorisation', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'wpsc_categorisation-1', $name, 'widget_wpsc_categorisation_control', $control_ops, array( 'number' => -1 ) );
	}
}

// This is important
add_action( 'widgets_init', 'widget_wpsc_categorisation_register' );
?>