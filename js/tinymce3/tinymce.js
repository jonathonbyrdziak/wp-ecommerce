function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function insertWPSCLink() {

	var tagtext;
	var select_category=document.getElementById('wpsc_category_panel');
	var category = document.getElementById('wpsc_category');
	var slider = document.getElementById('product_slider_panel');
	var add_product = document.getElementById('add_product_panel');
	
	// who is active ?
	if (select_category.className.indexOf('current') != -1) {
		var categoryid = category.value;
		var items_per_page = 0;
		items_per_page = jQuery('#wpsc_perpage').val();
		
		
		if (categoryid > 0 ) {
			if (items_per_page > 0)
				tagtext = "[wpsc_products category_id='"+categoryid+"' number_per_page='"+items_per_page+"']";
			else
				tagtext = "[wpsc_products category_id='"+categoryid+"' ]";
		} else {
			tinyMCEPopup.close();
		}
	}
	
	if (slider.className.indexOf('current') != -1) {
		category = document.getElementById('wpsc_slider_category');
		visi = document.getElementById('wpsc_slider_visibles');
		var categoryid = category.value;
		var visibles = visi.value;
		if (categoryid > 0 ) {
			if (visibles != '') {
				tagtext = "[wpsc_product_slider category_id='"+categoryid+"' visible_items='"+visibles+"']";
			} else {
				tagtext = "[wpsc_product_slider category_id='"+categoryid+"']";
			}
		} else {
			tinyMCEPopup.close();
		}
	}
	
	if (add_product.className.indexOf('current') != -1) {
		category = document.getElementById('add_product_category');
		prod_name = document.getElementById('add_product_name');
		prod_price = document.getElementById('add_product_price');
		prod_desc = document.getElementById('add_product_description');
		var categoryid = category.value;
		var desc = prod_desc.value;
		var product_name = prod_name.value;
		var price = prod_price.value;
		
		if (product_name != '') {
			ajax.post("index.php",noresults,"ajax=true&addfromtinymce=true&value=1");
			tagtext='1';
		} else {
			tinyMCEPopup.close();
		}
	}
	
	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		//Peforms a clean up of the current editor HTML. 
		//tinyMCEPopup.editor.execCommand('mceCleanup');
		//Repaints the editor. Sometimes the browser has graphic glitches. 
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
