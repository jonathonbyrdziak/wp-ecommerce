/* 
* this is the variations javascript file, it is in here because there is a lot of it, and it should be kept separate to make it easy to find and edit.
*/


function open_variation_settings(element_id) {
  jQuery("tr#"+element_id+" td div.variation_settings").toggle();
  return false;
}



   
function add_variation_value(value_type) {
  container_id = value_type+"_variation_values";
  //alert(container_id);
  last_element_id = document.getElementById(container_id).lastChild.id;
//   last_element_id = last_element_id.split("_");
//   last_element_id = last_element_id.reverse();
  date = new Date;
  new_element_id = "variation_value_"+date.getTime();
  
  
  old_elements = document.getElementById(container_id).innerHTML;
  new_element_contents = "";
  if(value_type == "edit") {
    new_element_contents += "<input type='text' class='text' name='new_variation_values[]' value='' />";
	} else {
		new_element_contents += "<input type='text' class='text' name='variation_values[]' value='' />";
	}
  new_element_contents += " <a class='image_link' href='#' onclick='remove_variation_value_field(\""+new_element_id+"\")'><img src='"+WPSC_URL+"/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a><br />";
  //new_element_contents += "</span>";
  
  new_element = document.createElement('span');
  new_element.id = new_element_id;
   
  document.getElementById(container_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
  return false;
}
  
function remove_variation_value(element,variation_value) {
  var delete_variation_value=function(results)
    {
    }
    
  element_count = jQuery("div#edit_variation_values span").size();
  if(element_count > 1) {
    ajax.post("index.php",delete_variation_value,"admin=true&ajax=true&remove_variation_value=true&variation_value_id="+variation_value);
    jQuery(element).parent("span.variation_value").remove();
	}
  return false;
}
 
function remove_variation_value_field(id)
  {
  element_count = document.getElementById("add_variation_values").childNodes.length;
  if(element_count > 1)
    {
    target_element = document.getElementById(id);
    document.getElementById("add_variation_values").removeChild(target_element);
    }
  }
  
function variation_value_list(id, parent_element) {
  if(id == null) {
    id = '';
  }
		selected_value = jQuery("input.variation_checkbox",parent_element).attr('checked');
 	if(selected_value == true) {
 		jQuery("div.variation_values_box",parent_element).css('display','block');
		active_checkboxes = jQuery.makeArray(jQuery("div.variation_values_box input[checked]",parent_element));
		if(active_checkboxes.length < 1) {
			jQuery("div.variation_values_box input[type='checkbox']",parent_element).attr('checked', 'true');
		}
 	} else {
 		jQuery("div.variation_values_box",parent_element).css('display','none');
		jQuery("div.variation_values_box input[type='checkbox']",parent_element).removeAttr('checked');
 	}
	
	selected_price = jQuery("input[name='price']",jQuery(parent_element).parents('form')).val();
	limited_stock = jQuery("input.limited_stock_checkbox",jQuery(parent_element).parents('form')).attr('checked');
	
	//current_variations = jQuery("label.variation_checkbox"+id+" input[type='hidden'], label.variation_checkbox"+id+" input[type='checkbox']").serialize();
	
	
	
	unselected_variations = jQuery("label.variation_checkbox"+id+" input[type='checkbox']").serialize();
	
	selected_variations = jQuery("label.variation_checkbox"+id+" input[type='checkbox']").serialize();

	
	//current_variations = jQuery("label.variation_checkbox"+id+" input[type='checkbox']").serialize();
	jQuery("label.variation_checkbox"+id+" input[type='checkbox']").attr("disabled", "true");
	
	
	
	post_values = "list_variation_values=true&product_id="+id+"&selected_price="+selected_price+"&limited_stock="+limited_stock+"&"+selected_variations+"";
	
	jQuery.post( 'index.php?admin=true&ajax=true', post_values, function(returned_data) {
		jQuery("div.variation_box label input[type='checkbox']").removeAttr("disabled", "true"); 
		eval(returned_data);
		jQuery("#edit_variations_container").html(edit_variation_combinations_html );
	});
}  


 
  
  
  
var display_list_ajaxx=function(results) {
	jQuery("div#edit_variations_container").html(results);
	//alert(results);
}
  
function add_variation_value_list(id) {
	var display_list=function(results) {
		eval(results);
    if(variation_subvalue_html != '') {
        new_element_id = "add_product_variations";
        if(document.getElementById(new_element_id) === null) {
          new_element = document.createElement('span');
          new_element.id = new_element_id;
          document.getElementById("add_product_variations").appendChild(new_element);
          //document.getElementById(new_element_id).innerHTML = variation_value_html;
        }
      jQuery("#add_product_variation_details").html(variation_subvalue_html);
    }
		jQuery("#edit_product_variations input[type='checkbox']").each(function() {
// 		  alert(this.id);
    });
		//ajax.post("index.php",display_list_ajaxx,"ajax=true&list_variation_values_ajaxx=true");
	}
	current_variations = jQuery("input.variation_ids").serialize();
	ajax.post("index.php",display_list,"admin=true&ajax=true&list_variation_values=true&new_variation_id="+id+"&prefix=add_product_variations&"+current_variations+"");
}
  
  
function edit_variation_value_list(id) {
  // haah, the javascript end does essentially nothing of interest, just sends a request, and dumps the output in a div tag
	var display_variation_forms=function(results) {
		if(results !== "false") { // do nothing if just the word false is returned
	  //alert(jQuery("div#edit_variations_container").html(results));
		
			//alert(jQuery("div#edit_variations_container"));
			jQuery("div#edit_variations_container").html(results);	
		}
	}	
	product_id= jQuery("#prodid").val();
	ajax.post("index.php",display_variation_forms,"admin=true&ajax=true&edit_variation_value_list=true&variation_id="+id+"&product_id="+product_id);
 }

function remove_variation_value_list(prefix,id){
	var redisplay_list=function(results) {
		jQuery("#add_product_variation_details").html(results);
	}
  if(prefix == "edit_product_variations") {
    target_element_id = "product_variations_"+id;
	} else {
		target_element_id = prefix+"_"+id;
	}
  target_element = document.getElementById(target_element_id);
  document.getElementById(prefix).removeChild(target_element);
  if(prefix == "add_product_variations") {
		current_variations = jQuery("input.variation_ids").serialize();
		ajax.post("index.php",redisplay_list,"admin=true&ajax=true&redisplay_variation_values=true&"+current_variations+"");
  }  
  return false;
}
  
function tick_active(target_id,input_value) {
  if(input_value != '') {
    document.getElementById(target_id).checked = true;
  }
}