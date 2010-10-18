// This is the wp-e-commerce front end javascript "library"

jQuery(document).ready( function () {

	jQuery('.wpsc_prod_thumb_option').livequery(function(){
	  jQuery(this).focus(function(){
	 	jQuery('.wpsc_mass_resize').css('visibility', 'visible');
	  });
	});
	
    jQuery('.wpsc_prod_thumb_option').livequery(function(){
	  jQuery(this).blur(function(){
	  
//	  	if(jQuery('.wpsc_mass_resize').css('visibility') != 'hidden')
//	 	jQuery('.wpsc_mass_resize').css('visibility', 'hidden');
	  });
	});


	//Delete checkout options on settings>checkout page
	jQuery('.wpsc_delete_option').livequery(function(){
		jQuery(this).click(function(event){
			jQuery(this).parent().parent('tr').remove();
			event.preventDefault();
		});
	
	});
	//Changing the checkout fields page
	jQuery('#wpsc_checkout_sets').livequery(function(){
		jQuery(this).change(function(){

		});
	
	});
  //checkboxes on checkout page 
/*
  jQuery('.wpsc_checkout_selectboxes').livequery(function(){
   	jQuery(this).change(function(){  				
			if(jQuery(this).val() == 'checkbox' || jQuery(this).val() == 'radio' ||
			jQuery(this).val() == 'select'){
				id = jQuery(this).attr('name');
				id = id.replace('form_type', '');
				output =  "<tr class='wpsc_grey'><td></td><td colspan='5'>Please save your changes to add options to this "+jQuery(this).val()+" form field.</td></tr>\r\n";
				jQuery(this).parent().parent('tr').after(output);
			}
		
	});
  });
*/
	jQuery('.wpsc_add_new_checkout_option').livequery(function(){
		jQuery(this).click(function(event){
			form_id = jQuery(this).attr('title');	
			id = form_id.replace('form_options', '');
			output = "<tr class='wpsc_grey'><td></td><td><input type='text' value='' name='wpsc_checkout_option_label"+id+"[]' /></td><td colspan='4'><input type='text' value='' name='wpsc_checkout_option_value"+id+"[]' />&nbsp;<a class='wpsc_delete_option' href='' ><img src='"+WPSC_URL+"/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a></td></tr>";
			jQuery(this).parent().parent('tr').after(output);
  			event.preventDefault();
		});
	
	});


	jQuery('.wpsc_edit_checkout_options').livequery(function(){
		jQuery(this).click(function(event){
				if(!jQuery(this).hasClass('triggered')){
					jQuery(this).addClass('triggered');
					id = jQuery(this).attr('rel');
					id = id.replace('form_options[', '');
					id = id.replace(']', '');
					post_values = "form_id="+id;
					jQuery.post('index.php?wpsc_admin_action=check_form_options',post_values, function(returned_data){
						if(returned_data != ''){
							jQuery('#checkout_'+id).after(returned_data);
						}else{
					output =  "<tr class='wpsc_grey'><td></td><td colspan='5'>Please Save your changes before trying to Order your Checkout Forms again.</td></tr>\r\n<tr  class='wpsc_grey'><td></td><th>Label</th><th >Value</th><td colspan='3'><a href=''  class='wpsc_add_new_checkout_option'  title='form_options["+id+"]'>+ New Layer</a></td></tr>";
					output += "<tr class='wpsc_grey'><td></td><td><input type='text' value='' name='wpsc_checkout_option_label["+id+"][]' /></td><td colspan='4'><input type='text' value='' name='wpsc_checkout_option_value["+id+"][]' /><a class='wpsc_delete_option' href='' ><img src='"+WPSC_URL+"/images/trash.gif' alt='Delete' title='delete' /></a></td></tr>";
					jQuery('#checkout_'+id).after(output);
					
					}
				
					});
					jQuery('table#wpsc_checkout_list').sortable('disable');
				}
		event.preventDefault();
		});
	
	
	});
   
   //grid view checkbox ajax to deselect show images only when other checkboxes are selected
  jQuery('#show_images_only').livequery(function(){
	jQuery(this).click(function(){
		imagesonly = jQuery(this).is(':checked');
		if(imagesonly){
			jQuery('#display_variations').attr('checked', false);
			jQuery('#display_description').attr('checked', false);
			jQuery('#display_addtocart').attr('checked', false);
			jQuery('#display_moredetails').attr('checked', false);

		}  
	});
  });
  jQuery('#display_variations, #display_description, #display_addtocart, #display_moredetails').livequery(function(){
	jQuery(this).click(function(){
		imagesonly = jQuery(this).is(':checked');

		if(imagesonly){
			jQuery('#show_images_only').attr('checked', false);

		}  
	});
  });
  //new currency JS in admin product page
  jQuery('tr.new_layer').livequery(function(){
  	jQuery(this).hide();
  
  });
  var firstclick = true
  jQuery('a.wpsc_add_new_currency').livequery(function(){
  	jQuery(this).click(function(event){
  		if(firstclick == true){
		  	jQuery('tr.new_layer').show();
  			html = jQuery('tr.new_layer').html();	  	
		  	firstclick = false;
  		}else{
		  	jQuery('tr.new_layer').after('<tr>'+html+'</tr>');		
  		}
  		event.preventDefault();
  	});
	});
  //delete currency layer in admin product page
  jQuery('a.wpsc_delete_currency_layer').livequery(function(){
  	jQuery(this).click(function(event){
			var currencySymbol = jQuery(this).attr('rel');
			jQuery(this).prev('input').val('');
			jQuery(this).prev('select').val('');
			jQuery(this).parent().parent('tr').hide();

			post_values = "currSymbol="+currencySymbol;
			jQuery.post('index.php?wpsc_admin_action=delete_currency_layer',post_values, function(returned_data){});
			//alert(currencySymbol);
			
			event.preventDefault();
		});  	
	});

  
  //delete currency layer in admin product page
  jQuery('a.wpsc_mass_resize').livequery(function(){
  	jQuery(this).click(function(event){
  		this_href = jQuery(this).attr('href');
  		parent_element = jQuery(this).parent();
  		extra_parameters = jQuery("input[type=text]", parent_element).serialize();
  		window.location = this_href+"&"+extra_parameters;
  		return false;
		});  	
	});
  
  //select all target markets in general settings page
  jQuery('a.wpsc_select_all').livequery(function(){
  	jQuery(this).click(function(event){
  		jQuery('div#resizeable input:checkbox').attr('checked', true);
  		event.preventDefault();
  		
  	});
  
  });
  //select all target markets in general settings page
  jQuery('a.wpsc_select_none').livequery(function(){
  	jQuery(this).click(function(event){
  		jQuery('div#resizeable input:checkbox').attr('checked', false);
  		event.preventDefault();
  		
  	});
  
  });
  // this makes the product list table sortable
  jQuery('table#wpsc_product_list').sortable({
		update: function(event, ui) {
			category_id = jQuery('input#products_page_category_id').val();
			product_order = jQuery('table#wpsc_product_list').sortable( 'serialize');
			post_values = "category_id="+category_id+"&"+product_order;
			jQuery.post( 'index.php?wpsc_admin_action=save_product_order', post_values, function(returned_data) { });
		},
    items: 'tr.product-edit',
    axis: 'y',
    containment: 'table#wpsc_product_list',
    placeholder: 'product-placeholder'
  });
  
  jQuery('table#wpsc_checkout_list').livequery(function(event){
   //this makes the checkout form fields sortable
	  jQuery(this).sortable({
		
	    items: 'tr.checkout_form_field',
	    axis: 'y',
	    containment: 'table#wpsc_checkout_list',
	    placeholder: 'checkout-placeholder',
	    handle: '.drag'
    	
	  }); 
	  jQuery(this).bind('sortupdate', function(event, ui) {
	  	
		//post_values = jQuery(this).sortable();
		//post_values = "category_id="+category_id+"&"+checkout_order;
		post_values = jQuery( 'table#wpsc_checkout_list').sortable( 'serialize');
		jQuery.post( 'index.php?wpsc_admin_action=save_checkout_order', post_values, function(returned_data) { });
	  });

  }); 
  
	  
	// this helps show the links in the product list table, it is partially done using CSS, but that breaks in IE6
	jQuery("tr.product-edit").hover(
		function() {
			jQuery(".wpsc-row-actions", this).css("visibility", "visible");
		},	
		function() {
			jQuery(".wpsc-row-actions", this).css("visibility", "hidden");
		}
	);
	// Used on admin/display-items.page.php - toggles the publish status of a product (dims background for unpublished) - 1bigidea
	// click logic from http://xplus3.net/2008/10/16/jquery-and-ajax-in-wordpress-plugins-administration-pages/
	/*
	jQuery(document).ready(function(){
		jQuery("span.publish_toggle a").click(function() {
			var that = this;
			var theRow = jQuery(this).parents('tr:first');
			jQuery.post(jQuery(this).attr("href"), {
					'cookie': encodeURIComponent(document.cookie)
				}
				, function(newstatus){
					if (newstatus == 'true') {
						jQuery(that).text('Hide');
						jQuery(theRow).removeClass('wpsc_not_published').addClass('wpsc_published')
					} else {
						jQuery(that).text('Show');
						jQuery(theRow).removeClass('wpsc_published').addClass('wpsc_not_published');
					}
				}
			);
			return false; // The click never happened - defeat the a tag
		});
	});*/

//jQuery('.selector :selected').val();

	jQuery('tr.wpsc_trackingid_row').hide();
	//jQuery('tr.wpsc_hastracking').show();

	jQuery('.wpsc_show_trackingid').click(function(event){
		purchlog_id = jQuery(this).attr('title');
		if(jQuery('tr.log'+purchlog_id).hasClass('wpsc_hastracking')){
			jQuery('tr.log'+purchlog_id).removeClass('wpsc_hastracking');
			jQuery('tr.log'+purchlog_id).hide();		
		}else{
			jQuery('tr.log'+purchlog_id).addClass('wpsc_hastracking');
			jQuery('tr.log'+purchlog_id).show();
		}
		event.preventDefault();
	});
  // this changes the purchase log item status
	 jQuery('.selector').change(function(){	
			purchlog_id = jQuery(this).attr('title');
	 		purchlog_status = jQuery(this).val();
	 		post_values = "purchlog_id="+purchlog_id+"&purchlog_status="+purchlog_status;
			jQuery.post( 'index.php?ajax=true&wpsc_admin_action=purchlog_edit_status', post_values, function(returned_data) { });

		 	if(purchlog_status == 3){
				jQuery('tr.log'+purchlog_id).show();
	 
	 		}
	 });

	jQuery('.sendTrackingEmail').click(function(event){
		purchlog_id = jQuery(this).attr('title');
		post_values = "purchlog_id="+purchlog_id;
		jQuery.post( 'index.php?wpsc_admin_action=purchlog_email_trackid', post_values, function(returned_data) { });
		event.preventDefault();
	});
	 
// 	jQuery("#submit_category_select").click(function() {
// 			new_url = jQuery("#category_select option:selected").val();
// 			//console.log(new_url);
// 			window.location = new_url;
// 			return false;
// 	});
	 
	
  // this loads the edit-products page using javascript
	 jQuery('.edit-product').click(function(){	
	 		jQuery(this).next('.loadingImg').removeAttr('style');
			product_id = jQuery(this).attr('href').match(/product_id=(\d{1,})/);
			wpnonce = jQuery(this).attr('href').match(/_wpnonce=(\w{1,})/);
	 		post_values = "product_id="+product_id[1]+"&_wpnonce="+wpnonce[1];
			jQuery.post( 'index.php?wpsc_admin_action=load_product', post_values, function(returned_data) {
				if(typeof(tinyMCE) != "undefined") {
					tinyMCE.execCommand("mceRemoveControl",false,"content");
				}
				jQuery('form#modify-products #content').remove();
				
				
			  jQuery('form#modify-products').html(returned_data);
			  	jQuery('.loadingImg').attr('style', 'display:none;');
				if ( getUserSetting( 'editor' ) != 'html' ) {
					jQuery("#quicktags").css('display', "none");
					if(typeof(tinyMCE) != "undefined") {
						tinyMCE.execCommand("mceAddControl", false, "content");
					}
				}
			});
	 		return false;
	 		// */
 		
	 });
	
	jQuery("a.thickbox").livequery(function(){
	 tb_init(this);
	});
	

	// Code for using AJAX to change thr product price starts here
	ajax_submit_price = function(event) {
		target_element_id= event.data;
		form_data = jQuery("#"+target_element_id+" input").serialize();
		//console.log(form_data);
		jQuery.ajax({
				type: "POST",
				url: "admin.php?wpsc_admin_action=modify_price",
				data: form_data,
				success: function(returned_data) {
					eval(returned_data);
				  if(success == 1) {
						parent_container = jQuery("#"+target_element_id+"").parent('.product-price');
						jQuery(".pricedisplay", parent_container).html(new_price);
				  }
					jQuery('span.pricedisplay').css('display', 'block');
					jQuery('div.price-editing-fields').css('display', 'none');
					jQuery('form#posts-filter').unbind('submit.disable');
				}
			});
		jQuery('form#posts-filter').unbind('submit.disable');
		return false;
	};

	
	jQuery("table#wpsc_product_list .product-price").livequery(function(){
		jQuery("span.pricedisplay", this).click( function(event) {
			jQuery('span.pricedisplay').css('display', 'block');
			jQuery('div.price-editing-fields').css('display', 'none');
			jQuery(this).css('display', 'none');
			jQuery('div.price-editing-fields', jQuery(this).parent('.product-price')).css('display', 'block');

			target_element_id = jQuery('div.price-editing-fields', jQuery(this).parent('.product-price')).attr('id');
			jQuery('form#posts-filter').bind('submit.disable',target_element_id, ajax_submit_price);
			
			jQuery('div.price-editing-fields .the-product-price', jQuery(this).parent('.product-price')).focus();
		});
		
		jQuery('.the-product-price',this).keyup(function(event){
			target_element_id = jQuery(jQuery(this).parent('.price-editing-fields')).attr('id');
			if(event.keyCode == 13) {
				jQuery('form#posts-filter').bind('submit.disable', target_element_id, ajax_submit_price);
			}
		});
		
		target_element_id = jQuery('.price-editing-fields',this).attr('id');
		
 		jQuery('.the-product-price',this).bind('blur', target_element_id, ajax_submit_price);
	});

// Code for using AJAX to change thr product price ends here
	
	jQuery("div.admin_product_name a.shorttag_toggle").livequery(function(){
	  jQuery(this).toggle(
			function () {
				jQuery("div.admin_product_shorttags", jQuery(this).parents("table.product_editform")).css('display', 'block');
				return false;
			},
			function () {
				//jQuery("div#admin_product_name a.shorttag_toggle").toggleClass('toggled');
				jQuery("div.admin_product_shorttags", jQuery(this).parents("table.product_editform")).css('display', 'none');
				return false;
			}
		);
	}); 
	
	jQuery('a.add_variation_item_form').livequery(function(){
	  jQuery(this).click(function() {
			form_field_container = jQuery(this).siblings('#variation_values');
			form_field = jQuery("div.variation_value", form_field_container).eq(0).clone();
			
			jQuery('input.text',form_field).attr('name','new_variation_values[]');
			jQuery('input.text',form_field).val('');
			jQuery('input.variation_values_id',form_field).remove();
			
			jQuery(form_field_container).append(form_field);
		  return false;
		});		
	});
	

	jQuery('div.variation_value a.delete_variation_value').livequery(function(){
	  jQuery(this).click( function() {
			element_count = jQuery("#variation_values div").size();
			
			
			if(element_count > 1) {
			
			
			  parent_element = jQuery(this).parent("div.variation_value");
			  
			  variation_value_id = jQuery("input.variation_values_id", parent_element).val();
			  //console.log(variation_value_id);
			  post_values = "remove_variation_value=true&variation_value_id="+variation_value_id;			
				jQuery.post( 'index.php?admin=true&ajax=true', post_values, function(returned_data) {
			
				});
				jQuery(this).parent("div.variation_value").remove();
			}
		  return false;
		});		
	});
	

	
	jQuery('#poststuff .postbox h3').livequery(function(){
	  jQuery(this).click( function() {
			jQuery(jQuery(this).parent('div.postbox')).toggleClass('closed');
				if(jQuery(jQuery(this).parent('div.postbox')).hasClass('closed')) {
					jQuery('a.togbox',this).html('+');
				} else {
					jQuery('a.togbox',this).html('&ndash;');
				}
				wpsc_save_postboxes_state('store_page_wpsc-edit-products', '#poststuff');
		});		
	});
 	jQuery('#dashboard-widgets .postbox h3').livequery(function(){
	  jQuery(this).click( function() {
			jQuery(jQuery(this).parent('div.postbox')).toggleClass('closed');
				if(jQuery(jQuery(this).parent('div.postbox')).hasClass('closed')) {
					jQuery('a.togbox',this).html('+');
				} else {
					jQuery('a.togbox',this).html('&ndash;');
				}
				wpsc_save_postboxes_state('store_page_wpsc-sale-logs', '#dashboard-widgets-main-content-wpsc');
		});		
	});
 

	jQuery('a.add_new_form_set').livequery(function(){
	  jQuery(this).click( function() {
			jQuery(".add_new_form_set_forms").toggle();
		});		
	});


	jQuery("#add-product-image").click(function(){
		swfu.selectFiles();
	});
	
	jQuery('.hide-postbox-tog').livequery(function(){
		jQuery(this).click( function() {
			var box = jQuery(this).val();
			if ( jQuery(this).attr('checked') ) {
				jQuery('#' + box).show();
				if ( jQuery.isFunction( postboxes.pbshow ) ) {
					postboxes.pbshow( box );
				}
			} else {
				jQuery('#' + box).hide();
				if ( jQuery.isFunction( postboxes.pbhide ) ) {
					postboxes.pbhide( box );
				}
			}
			postboxes.save_state('store_page_wpsc-edit-products');
		});
	});
		
	// postbox sorting
	jQuery('.meta-box-sortables').livequery(function(){
	  jQuery(this).sortable({
			placeholder: 'sortable-placeholder',
			connectWith: [ '.meta-box-sortables' ],
			items: '> .postbox',
			handle: '.hndle',
			distance: 2,
			tolerance: 'pointer',
			sort: function(e,ui) {
				if ( jQuery(document).width() - e.clientX < 300 ) {
					if ( ! jQuery('#post-body').hasClass('has-sidebar') ) {
						var pos = jQuery('#side-sortables').offset();
	
						jQuery('#side-sortables').append(ui.item)
						jQuery(ui.placeholder).css({'top':pos.top,'left':pos.left}).width(jQuery(ui.item).width())
						postboxes.expandSidebar(1);
					}
				}
			},
			stop: function() {
				var postVars = {
					action: 'product-page-order',
					ajax: 'true'
				}
				jQuery(this).each( function() {
					postVars["order[" + this.id.split('-')[0] + "]"] = jQuery(this).sortable( 'toArray' ).join(',');
				} );
				jQuery.post( 'index.php?admin=true&ajax=true', postVars, function() {
					postboxes.expandSidebar();
				} );
			}
		});
	});
	
	
	

	jQuery('img.deleteButton, a.delete_primary_image').livequery(function(){
	  jQuery(this).click( function() {
			var r=confirm("Please confirm deletion");
			if (r==true) {
				img_id = jQuery(this).parents("li.gallery_image").attr('id');
				
			  post_values = "del_img_id="+img_id+"&product_id="+jQuery('#product_id').val();
				jQuery.post( 'index.php?wpsc_admin_action=delete_images', post_values, function(returned_data) {
				  console.log(returned_data);
					eval(returned_data);
					if(typeof(image_id) != "undefined") {
						jQuery('#gallery_image_'+image_id).children('img.deleteButton').remove();
						jQuery('#gallery_image_'+image_id).children('a.editButton').remove();
						jQuery('#gallery_image_'+image_id).children('div.image_settings_box').remove();
					}
					if(typeof(image_menu) != "undefined") {
						jQuery('#gallery_image_'+image_id).append(image_menu);
					}
					jQuery("#"+element_id).remove();
				});
				return false;
			}
	  });
	});

	jQuery('.closeimagesettings').livequery(function(){
	  jQuery(this).click( function() {
				jQuery('.image_settings_box').hide();
	  });
	});
	
	
	jQuery("#gallery_list").livequery(function(){
	  jQuery(this).sortable({
			revert: false,
			placeholder: "ui-selected",
			start: function(e,ui) {
				jQuery('#image_settings_box').hide();
				jQuery('a.editButton').hide();
				jQuery('img.deleteButton').hide();
				jQuery('ul#gallery_list').children('li').removeClass('first');
			},
			stop:function (e,ui) {
				jQuery('ul#gallery_list').children('li:first').addClass('first');
			},
			update: function (e,ui){
						input_set = jQuery.makeArray(jQuery("#gallery_list li:not(.ui-sortable-helper) input.image-id"));
						//console.log(input_set);
						set = new Array();
						for( var i in input_set) {
						  set[i] = jQuery(input_set[i]).val();
						}
						//console.log(set);
												
						img_id = jQuery('#gallery_image_'+set[0]).parent('li').attr('id');
						
						jQuery('#gallery_image_'+set[0]).children('img.deleteButton').remove();
						jQuery('#gallery_image_'+set[0]).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png' alt ='' /></a>");
// 						jQuery('#gallery_image_'+set[0]).parent('li').attr('id',  "product_image_"+img_id);
						//for(i=1;i<set.length;i++) {
						//	jQuery('#gallery_image_'+set[i]).children('a.editButton').remove();
						//	jQuery('#gallery_image_'+set[i]).append("<img alt='-' class='deleteButton' src='"+WPSC_URL+"/images/cross.png'/>");
						//}
						
						for(i=1;i<set.length;i++) {
							jQuery('#gallery_image_'+set[i]).children('a.editButton').remove();
							jQuery('#gallery_image_'+set[i]).append("<img alt='-' class='deleteButton' src='"+WPSC_URL+"/images/cross.png'/>");
							
							element_id = jQuery('#gallery_image_'+set[i]).parent('li').attr('id');
							if(element_id == 0) {
// 								jQuery('#gallery_image_'+set[i]).parent('li').attr('id', "product_image_"+img_id);
							}
						}
						
						order = set.join(',');
						product_id = jQuery('#product_id').val();
						
						
						postVars = "product_id="+product_id+"&order="+order;
						jQuery.post( 'index.php?wpsc_admin_action=rearrange_images', postVars, function(returned_data) {
							  eval(returned_data);
								jQuery('#gallery_image_'+image_id).children('a.editButton').remove();
								jQuery('#gallery_image_'+image_id).children('div.image_settings_box').remove();
								jQuery('#gallery_image_'+image_id).append(image_menu);
						});
						
					},
			'opacity':0.5
		});
	});
	
	
	
	
	// show or hide the stock input forms
	jQuery("input.limited_stock_checkbox").livequery(function(){
	  jQuery(this).click( function ()  {
			parent_form = jQuery(this).parents('form');
			if(jQuery(this).attr('checked') == true) {
				jQuery("div.edit_stock",parent_form).show();
				jQuery("th.stock, td.stock", parent_form).show();
			} else {
				jQuery("div.edit_stock", parent_form).hide();
				jQuery("th.stock, td.stock", parent_form).hide();
			}
		});
	});
	
	
	jQuery("#table_rate_price").livequery(function(){
	  jQuery(this).click( function() {
			if (this.checked) {
				jQuery("#table_rate").show();
			} else {
				jQuery("#table_rate").hide();
			}
		});
	});
	
		jQuery("#custom_tax_checkbox").livequery(function(){
	  jQuery(this).click( function() {
			if (this.checked) {
				jQuery("#custom_tax").show();
			} else {
				jQuery("#custom_tax input").val('');
				jQuery("#custom_tax").hide();
			}
		});
	});
	
	jQuery(".add_level").livequery(function(){
	  jQuery(this).click(function() {
			added = jQuery(this).parent().children('table').append('<tr><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][table_price][]"/></td></tr>');
		});
	});
	
	
	jQuery(".remove_line").livequery(function(){
	  jQuery(this).click(function() {
			jQuery(this).parent().parent('tr').remove();
		});		
	});
/* shipping options start */
	// gets shipping form for admin page
		// show or hide the stock input forms

jQuery(".wpsc-shipping-actions a").livequery(function(){
	jQuery(this).click( function ()  {
		var module = jQuery(this).attr('rel');
		
		jQuery.ajax({
			method: "post",
			url: "index.php",
			data: "wpsc_admin_action=get_shipping_form&shippingname="+module,
			success: function(returned_data){
				eval(returned_data);
				//jQuery(".gateway_settings").children(".form-table").html(html)
				jQuery('.gateway_settings h3.hndle').html(shipping_name_html);
				jQuery("td.gateway_settings table.form-table").html('<tr><td><input type="hidden" name="shippingname" value="'+module+'" /></td></tr>'+shipping_form_html);
				if(has_submit_button != '') {
					jQuery('.gateway_settings div.submit').css('display', 'block');
				} else {
					jQuery('.gateway_settings div.submit').css('display', 'none');
				}			
			}
		});
		return false;

	});
});
	
	jQuery('#addweightlayer').livequery(function(){
		jQuery(this).click(function(){
		jQuery(this).parent().append("<div class='wpsc_newlayer'><tr class='rate_row'><td><i style='color:grey'>"+TXT_WPSC_IF_WEIGHT_IS+"</i><input type='text' name='weight_layer[]' size='10'> <i style='color:grey'>"+TXT_WPSC_AND_ABOVE+"</i></td><td><input type='text' name='weight_shipping[]' size='10'>&nbsp;&nbsp;<a href='' class='delete_button nosubmit' >"+TXT_WPSC_DELETE+"</a></td></tr></div>");
		});
	
	});
	
	jQuery('#addlayer').livequery(function(){
		jQuery(this).click(function(){
		jQuery(this).parent().append("<div class='wpsc_newlayer'><tr class='rate_row'><td><i style='color:grey'>"+TXT_WPSC_IF_PRICE_IS+"</i><input type='text' name='layer[]' size='10'> <i style='color:grey'>"+TXT_WPSC_AND_ABOVE+"</i></td><td><input type='text' name='shipping[]' size='10'>&nbsp;&nbsp;<a href='' class='delete_button nosubmit' >"+TXT_WPSC_DELETE+"</a></td></tr></div>");
		//bind_shipping_rate_deletion();
		return false;
		});
	
	});
	
  jQuery('table#gateway_options a.delete_button').livequery(function(){
  		jQuery(this).click(function () {
    this_row = jQuery(this).parent().parent('tr .rate_row');
   // alert(this_row);
   //jQuery(this_row).hide();
    if(jQuery(this).hasClass('nosubmit')) {
			// if the row was added using JS, just scrap it
			this_row = jQuery(this).parent('div .wpsc_newlayer');
			jQuery(this_row).remove();
    } else {
			// otherwise, empty it and submit it
			jQuery('input', this_row).val('');
			jQuery(this).parents('form').submit();
    }
    return false;
	});
	});

	// hover for gallery view
	jQuery("div.previewimage").livequery(function(){
	  jQuery(this).hover(
			function () {
				jQuery(this).children('img.deleteButton').show();
				if(jQuery('div.image_settings_box').css('display')!='block')
					jQuery(this).children('a.editButton').show();
			},
			function () {
				jQuery(this).children('img.deleteButton').hide();
				jQuery(this).children('a.editButton').hide();
			}
		);		
	});
	
	
	// display image editing menu
	jQuery("a.editButton").livequery(function(){
	  jQuery(this).click( function(){
			jQuery(this).hide();
			jQuery('div.image_settings_box').show('fast');
		});	
	});
	// hide image editing menu
	jQuery("a.closeimagesettings").livequery(function(){
	  jQuery(this).click(function (e) {
			jQuery("div#image_settings_box").hide();
		});
	});
	
	// delete upload
	jQuery(".file_delete_button").livequery(function(){
			jQuery(this).click(function() {
				url = jQuery(this).attr('href');
				jQuery(this).parent().remove();
				post_values = "ajax=true";
				jQuery.post( url, post_values, function(returned_data) { });
				return false;
			});
		});
		
	// Options page ajax tab display 
	jQuery('#sidemenu li').click(function(){
		 	page_title = jQuery(this).attr('id');
		 	
			wpnonce = jQuery('a',this).attr('href').match(/_wpnonce=(\w{1,})/);
		 	post_values = "wpsc_admin_action=settings_page_ajax&page_title="+page_title+"&_wpnonce="+wpnonce[1];
		 	jQuery.post('admin.php?', post_values, function(html){
		 	//console.log(html);
		 	jQuery('a.current').removeClass('current');
		 	jQuery('#'+page_title+' a' ).addClass('current');
		 	jQuery('#wpsc_options_page').html('');
		 	jQuery('#wpsc_options_page').html(html);
		 
		 	});
		 	return false;
		 	
	 });

});



// function for adding more custom meta
function add_more_meta(e) {
  current_meta_forms = jQuery(e).parent().children("div.product_custom_meta:last");  // grab the form container
  new_meta_forms = current_meta_forms.clone(true); // clone the form container
  jQuery("label input", new_meta_forms).val(''); // reset all contained forms to empty
  current_meta_forms.after(new_meta_forms);  // append it after the container of the clicked element
  return false;
}

// function for removing custom meta
function remove_meta(e, meta_id) {
  current_meta_form = jQuery(e).parent("div.product_custom_meta");  // grab the form container
  //meta_name = jQuery("input#custom_meta_name_"+meta_id, current_meta_form).val();
  //meta_value = jQuery("input#custom_meta_value_"+meta_id, current_meta_form).val();
	returned_value = jQuery.ajax({
		type: "POST",
		url: "admin.php?ajax=true",
		data: "admin=true&remove_meta=true&meta_id="+meta_id+"",
		success: function(results) {
			if(results > 0) {
			  jQuery("div#custom_meta_"+meta_id).remove();
			}
		}
	}); 
  return false;
}


// function for switching the state of the image upload forms
function wpsc_upload_switcher(target_state) {
  switch(target_state) {
    case 'flash':
    jQuery("div.browser-image-uploader").css("display","none");
    jQuery("div.flash-image-uploader").css("display","block");
    jQuery.post( 'index.php?admin=true', "admin=true&ajax=true&save_image_upload_state=true&image_upload_state=1", function(returned_data) { });
    break;
    
    case 'browser':
    jQuery("div.flash-image-uploader").css("display","none");
    jQuery("div.browser-image-uploader").css("display","block");
    jQuery.post( 'index.php?admin=true', "admin=true&ajax=true&save_image_upload_state=true&image_upload_state=0", function(returned_data) { });
    break;
  }
}

// function for switching the state of the extra resize forms
function image_resize_extra_forms(option) {
	container = jQuery(option).parent();
	jQuery("div.image_resize_extra_forms").css('display', 'none');
	jQuery("div.image_resize_extra_forms",container).css('display', 'block');
}


var prevElement = null;
var prevOption = null;

function hideOptionElement(id, option) {
	if (prevOption == option) {
		return;
	}
	if (prevElement != null) {
		prevElement.style.display = "none";
	}
  
	if (id == null) {
		prevElement = null;
	} else {
		prevElement = document.getElementById(id);
		jQuery('#'+id).css( 'display','block');
	}
	prevOption = option;
}


function wpsc_save_postboxes_state(page, container) {
//  console.log(container);
	var closed = jQuery(container+' .postbox').filter('.closed').map(function() { return this.id; }).get().join(',');
	jQuery.post(ajaxurl, {
		action: 'closed-postboxes',
		closed: closed,
		closedpostboxesnonce: jQuery('#closedpostboxesnonce').val(),
		page: page
	});
}

  
function hideelement(id) {
  state = document.getElementById(id).style.display;
  //alert(document.getElementById(id).style.display);
  if(state != 'block') {
    document.getElementById(id).style.display = 'block';
	} else {
		document.getElementById(id).style.display = 'none';
	}
}

/*
 * Modified copy of the wordpress edToolbar function that does the same job, it uses document.write, we cannot.
*/
function wpsc_edToolbar() {
	//document.write('<div id="ed_toolbar">');
	output = '';
	for (i = 0; i < edButtons.length; i++) {
		output += 	wpsc_edShowButton(edButtons[i], i);
	}
	output += '<input type="button" id="ed_spell" class="ed_button" onclick="edSpell(edCanvas);" title="' + quicktagsL10n.dictionaryLookup + '" value="' + quicktagsL10n.lookup + '" />';
	output += '<input type="button" id="ed_close" class="ed_button" onclick="edCloseAllTags();" title="' + quicktagsL10n.closeAllOpenTags + '" value="' + quicktagsL10n.closeTags + '" />';
//	edShowLinks(); // disabled by default
	//document.write('</div>');
	jQuery('div#ed_toolbar').html(output);
}


/*
 * Modified copy of the wordpress edShowButton function that does the same job, it uses document.write, we cannot.
*/

function wpsc_edShowButton(button, i) {
	if (button.id == 'ed_img') {
		output = '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertImage(edCanvas);" value="' + button.display + '" />';
	}
	else if (button.id == 'ed_link') {
		output = '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertLink(edCanvas, ' + i + ');" value="' + button.display + '" />';
	}
	else {
		output = '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertTag(edCanvas, ' + i + ');" value="' + button.display + '"  />';
	}
	return output;
}



function fillcategoryform(catid) {
  post_values = 'ajax=true&admin=true&catid='+catid;
	jQuery.post( 'index.php', post_values, function(returned_data) {
		jQuery('#formcontent').html( returned_data );
		jQuery('form.edititem').css('display', 'block');
		jQuery('#additem').css('display', 'none');
		jQuery('#blank_item').css('display', 'none');
		jQuery('#productform').css('display', 'block');
		jQuery("#loadingindicator_span").css('visibility','hidden');
	});
}
  
function submit_status_form(id) {
  document.getElementById(id).submit();
} 
function showaddform() {
   jQuery('#blank_item').css('display', 'none');
   jQuery('#productform').css('display', 'none');
   jQuery('#additem').css('display', 'block');
   return false;
}
//used to add new form fields in the checkout setting page
function add_form_field() {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "form_id_"+new_element_number;
  
  new_element_contents = "";
  //new_element_contents += "<tr class='checkout_form_field' >\n\r";
  new_element_contents += "<td class='drag'></td>";
  new_element_contents += "<td class='namecol'><input type='text' name='new_form_name["+new_element_number+"]' value='' /></td>\n\r";
  new_element_contents += "<td class='typecol'><select class='wpsc_checkout_selectboxes' name='new_form_type["+new_element_number+"]'>"+HTML_FORM_FIELD_TYPES+"</select></td>\n\r"; 
   new_element_contents += "<td class='typecol'><select name='new_form_unique_name["+new_element_number+"]'>"+HTML_FORM_FIELD_UNIQUE_NAMES+"</select></td>\n\r"; 
  new_element_contents += "<td class='mandatorycol' style='text-align: center;'><input type='checkbox' name='new_form_mandatory["+new_element_number+"]' value='1' /></td>\n\r";
   new_element_contents += "<td><a class='image_link' href='#' onclick='return remove_new_form_field(\""+new_element_id+"\");'><img src='"+WPSC_URL+"/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a></td>\n\r";
 // new_element_contents += "</tr>";
  
  new_element = document.createElement('tr');
  new_element.id = new_element_id;
  document.getElementById("wpsc_checkout_list_body").appendChild(new_element);
  //document.getElementById(new_element_id).innerHTML = new_element_contents;
  jQuery('#'+new_element_id).append(new_element_contents);
  jQuery('#'+new_element_id).addClass('checkout_form_field');
  return false;
}


  
function remove_new_form_field(id) {
  element_count = document.getElementById("wpsc_checkout_list_body").childNodes.length;
  if(element_count > 1) {
    target_element = document.getElementById(id);
    document.getElementById("wpsc_checkout_list_body").removeChild(target_element);
  }
  return false;
}
  

function submit_change_country() {
  document.cart_options.submit();
  //document.cart_options.submit();
}

function getcurrency(id) {
	//ajax.post("index.php",gercurrency,"wpsc_admin_action=change_currency&currencyid="+id);
}
//delete checkout fields from checkout settings page
function remove_form_field(id,form_id) {
  var delete_variation_value=function(results) { }
  element_count = document.getElementById("wpsc_checkout_list_body").childNodes.length;
  if(element_count > 1) {
    ajax.post("index.php",delete_variation_value,"admin=true&ajax=true&remove_form_field=true&form_id="+form_id);
    target_element = document.getElementById(id);
    document.getElementById("wpsc_checkout_list_body").removeChild(target_element);
  }
  return false;
} 

function showadd_categorisation_form() {
	if(jQuery('div#add_categorisation').css('display') != 'block') {
		jQuery('div#add_categorisation').css('display', 'block');
		jQuery('div#edit_categorisation').css('display', 'none');
	} else {
		jQuery('div#add_categorisation').css('display', 'none');
	}
	return false;
}


function showedit_categorisation_form() {
	if(jQuery('div#edit_categorisation').css('display') != 'block') {
		jQuery('div#edit_categorisation').css('display', 'block');
		jQuery('div#add_categorisation').css('display', 'none');
	} else {
		jQuery('div#edit_categorisation').css('display', 'none');
	}
	return false;
}

function hideelement1(id, item_value) {
  //alert(value);  
	if(item_value == 5) {
		jQuery(document.getElementById(id)).css('display', 'block');
	} else {
		jQuery(document.getElementById(id)).css('display', 'none');
	}
}

function toggle_display_options(state) {
  switch(state) {
    case 'list':
    document.getElementById('grid_view_options').style.display = 'none';
    document.getElementById('list_view_options').style.display = 'block';    
    break;
    
    case 'grid':
    document.getElementById('list_view_options').style.display = 'none';
    document.getElementById('grid_view_options').style.display = 'block';
    break;
    
    default:
    document.getElementById('list_view_options').style.display = 'none';
    document.getElementById('grid_view_options').style.display = 'none';
    break;
  }
}


