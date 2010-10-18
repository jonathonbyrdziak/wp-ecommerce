<?php

function wpsc_save_theme_options_default() {
	if(is_numeric($_POST['wpsc_page_number_position'])) {
		update_option('wpsc_page_number_position', (int)$_POST['wpsc_page_number_position']);
	} else {
		update_option('wpsc_page_number_position', 1);
	}
}

function wpsc_theme_options_default() {
	?>
		<tr>
			<th scope="row">
				<?php echo __('Page Number position', 'wpsc');?>:
			</th>
			<td>
				<input type='radio' value='1' name='wpsc_page_number_position' id='wpsc_page_number_position1' <?php if (get_option('wpsc_page_number_position') == 1) { echo "checked='checked'"; } ?> /><label for='wpsc_page_number_position1'>Top</label>&nbsp;
				<input type='radio' value='2' name='wpsc_page_number_position' id='wpsc_page_number_position2' <?php if (get_option('wpsc_page_number_position') == 2) { echo "checked='checked'"; } ?> /><label for='wpsc_page_number_position2'>Bottom</label>&nbsp;
				<input type='radio' value='3' name='wpsc_page_number_position' id='wpsc_page_number_position3' <?php if (get_option('wpsc_page_number_position') == 3) { echo "checked='checked'"; } ?> /><label for='wpsc_page_number_position3'>Both</label>
				<br />
			</td>
		</tr>   
  <?php
}



add_action('wpsc_save_theme_options','wpsc_save_theme_options_default');
		
		
add_action('wpsc_theme_options','wpsc_theme_options_default');

?>