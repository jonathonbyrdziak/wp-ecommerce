<?php
 //echo "<pre>".print_r($GLOBALS['wpsc_cart']->cart_items[0], true)."</pre>";
?>
<?php if(count($cart_messages) > 0) { ?>
  <p>
	<?php foreach((array)$cart_messages as $cart_message) { ?>
	  <span><?php echo $cart_message; ?></span><br />
	<?php } ?>
	</p>
<?php } ?>

<?php if(wpsc_cart_item_count() > 0): ?>
  <span class='items'>
		<span class='numberitems'>
			<?php echo __('Number of items', 'wpsc'); ?>:
		</span>
		<span class='cartcount'>
			<?php echo wpsc_cart_item_count(); ?>
		</span>
	</span>
	<table class='shoppingcart'>
		<tr>
			<th id='product'><?php echo __('Product', 'wpsc'); ?></th>
			<th id='quantity'><?php echo __('Qty', 'wpsc'); ?></th>
			<th id='price'><?php echo __('Price', 'wpsc'); ?></th>
		</tr>
		<?php while(wpsc_have_cart_items()): wpsc_the_cart_item(); ?>
			<tr>
					<td><?php echo wpsc_cart_item_name(); ?></td>
					<td><?php echo wpsc_cart_item_quantity(); ?></td>
					<td><?php echo wpsc_cart_item_price(); ?></td>
			</tr>	
		<?php endwhile; ?>
	</table>

<?php if(wpsc_cart_has_shipping() && !wpsc_cart_show_plus_postage()) : ?>
		<span class='total'>
		  <span class="pricedisplay checkout-shipping"><?php echo wpsc_cart_shipping(); ?></span>
		<span class='totalhead'>
			<?php echo __('Shipping', 'wpsc'); ?>:
	  </span>
	
	</span>
	<?php endif; ?>
<?php if( (wpsc_cart_tax(false) >0) && !wpsc_cart_show_plus_postage()) : ?>
		<span class='total'>
		  <span class="pricedisplay checkout-tax"><?php echo wpsc_cart_tax(); ?></span>
		<span class='totalhead'>
			<?php echo wpsc_display_tax_label(true); ?>:
	  </span>
	
	</span>
	<?php endif; ?>
		
	<span class='total'>
		<span class="pricedisplay checkout-total">
			<?php echo wpsc_cart_total_widget(); ?>
			<?php if(wpsc_cart_show_plus_postage()) : ?>
				<span class='pluspostagetax'> + <?php echo __('Postage &amp; Tax ', 'wpsc'); ?></span>
			<?php endif; ?>
		</span>
		<span class='totalhead'>
			<?php echo __('Total', 'wpsc'); ?>:
	  </span>
	</span>
	

	
	<form action='' method='post' class='wpsc_empty_the_cart'>
		<input type='hidden' name='wpsc_ajax_action' value='empty_cart' />
		<span class='emptycart'>
			<a href='<?php echo htmlentities(add_query_arg('wpsc_ajax_action', 'empty_cart', remove_query_arg('ajax')), ENT_QUOTES); ?>'><?php echo __('Empty your cart', 'wpsc'); ?></a>
		</span>                                                                                             
	</form>
	
	<span class='gocheckout'><a href='<?php echo get_option('shopping_cart_url'); ?>'><?php echo __('Go to Checkout', 'wpsc'); ?></a></span>
<?php else: ?>
	<p class="empty"><?php echo __('Your shopping cart is empty', 'wpsc'); ?></p>
	<p class="visitshop">
	  <a href="<?php echo get_option('product_list_url'); ?>"><?php echo __('Visit the shop', 'wpsc'); ?></a>
	</p>
<?php endif; ?>

<?php
wpsc_google_checkout();


?>