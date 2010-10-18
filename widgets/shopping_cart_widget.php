<?php
function widget_wp_shopping_cart($args) {
    global $wpsc_theme_path, $cache_enabled;
    extract($args);
    $options = get_option('widget_wp_shopping_cart');
  //  exit('count: '.wpsc_cart_item_count().' hideonempty<pre>'.print_r($options, true).'</pre>');
    
	if(($options['hideonempty']== 1) && (wpsc_cart_item_count() < 1))
		return;
	
    
      
		if(get_option('show_sliding_cart') == 1)	{
			if(is_numeric($_SESSION['slider_state']))	{
			if($_SESSION['slider_state'] == 0) { $collapser_image = 'plus.png'; } else { $collapser_image = 'minus.png'; }
				$fancy_collapser = "<a href='#' onclick='return shopping_cart_collapser()' id='fancy_collapser_link'><img src='".WPSC_URL."/images/$collapser_image' title='' alt='' id='fancy_collapser' /></a>";
			} else {
				if($_SESSION['nzshpcrt_cart'] == null) { $collapser_image = 'plus.png'; } else { $collapser_image = 'minus.png'; }
				$fancy_collapser = "<a href='#' onclick='return shopping_cart_collapser()' id='fancy_collapser_link'><img src='".WPSC_URL."/images/$collapser_image' title='' alt='' id='fancy_collapser' /></a>";
			}
		} else {
			$fancy_collapser = "";
		}
      
      
    
    $title = empty($options['title']) ? __('Shopping Cart') : $options['title'];
    echo $before_widget;
    $full_title = $before_title . $title . $fancy_collapser . $after_title;    
    echo $full_title;
    
		$display_state = "";
		if((($_SESSION['slider_state'] == 0) || (wpsc_cart_item_count() < 1)) && (get_option('show_sliding_cart') == 1)) {
			$display_state = "style='display: none;'";
		}

		$use_object_frame = false;
		if(($cache_enabled == true) && (!defined('DONOTCACHEPAGE') || (constant('DONOTCACHEPAGE') !== true))) {
			echo "    <div id='sliding_cart' class='shopping-cart-wrapper'>";
			if((strstr($_SERVER['HTTP_USER_AGENT'], "MSIE") == false) && ($use_object_frame == true)) {
				?>
				<object codetype="text/html" type="text/html" data="index.php?wpsc_action=cart_html_page"  border='0px'>
					<p><?php _e('Loading...', 'wpsc'); ?></p>
				</object>
				<?php
			} else {
			?>
			<div class='wpsc_cart_loading'><p><?php _e('Loading...', 'wpsc'); ?></p>
			<?php
			}
			echo "    </div>";
		} else {
			echo "    <div id='sliding_cart' class='shopping-cart-wrapper' $display_state>";
			include(wpsc_get_theme_file_path("cart_widget.php"));
			echo "    </div>";
		}
    echo $after_widget;
    }

function widget_wp_shopping_cart_control() {
	$options = $newoptions = get_option('widget_wp_shopping_cart');
	if ( $_POST["wp_shopping_cart-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["wp_shopping_cart-title"]));
		if ($_POST['wp_shopping_cart-hideonempty'])  {
			$newoptions['hideonempty'] = TRUE;
		} else {
			$newoptions['hideonempty'] = FALSE;
		}

	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_wp_shopping_cart', $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	
	echo "<p>\n\r";
	echo "  <label for='wp_shopping_cart-title'>"._e('Title:', 'wpsc')."<input class='widefat' id='wp_shopping_cart-title' name='wp_shopping_cart-title' type='text' value='{$title}' /></label><br/><br/>\n\r";
	echo "  <label for='wp_shopping_cart-hideonempty'>"._e('Hide Cart When Empty:')."<input id='wp_shopping_cart-hideonempty' name='wp_shopping_cart-hideonempty' type='checkbox' ";
	if ($options['hideonempty']) {
		echo " checked='checked' ";
	}
	echo "/></label><br/>\n\r";

	echo "    <input type='hidden' id='wp_shopping_cart-submit' name='wp_shopping_cart-submit' value='1' />\n\r";
	echo "  </label>\n\r";
	echo "</p>\n\r";
}

 function widget_wp_shopping_cart_init() {
   if(function_exists('register_sidebar_widget')) {
		$widget_ops['description'] = "Your most used tags in cloud format";
    register_sidebar_widget('Shopping Cart', 'widget_wp_shopping_cart', $widget_ops);
    register_widget_control('Shopping Cart', 'widget_wp_shopping_cart_control');
    $GLOBALS['wpsc_cart_widget'] = true;
    if(get_option('cart_location') == 1) {
      update_option('cart_location', 4);
      remove_action('wp_list_pages','nzshpcrt_shopping_basket');
		}
    #register_widget_control('Shopping Cart', 'widget_wp_shopping_cart_control', 300, 90);
	}
	return;
}
?>