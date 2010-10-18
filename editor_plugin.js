tinyMCE.importPluginLanguagePack('ecom');

var TinyMCE_ecom_Plugin = {

	getInfo : function() {
		return {
			longname : 'e-Commerce',
			author : 'Allen Han',
			authorurl : 'http://www.instinct.co.nz',
			infourl : 'http://www.instinct.co.nz',
			version : "1.0"
		};
	},
	
// 	initInstance : function(inst) {
// 		inst.addShortcut('ctrl', 'p', 'lang_dd_code_desc', 'mcedd_code');
// 	},

	getControlHTML : function(cn) {
		switch (cn) {
			case "ecom":
				return tinyMCE.getButtonHTML(cn, 'lang_dd_code_desc', '{$pluginurl}/images/cart.png', 'mceecom_code', true);
		}

		return "";
	},

	execCommand : function(editor_id, element, command, user_interface, value) {

		switch (command) {
				
			case "mceecom_code":
				var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
				var title = "", onmouseover = "", onmouseout = "", action = "insert";
				var img = tinyMCE.imgElement;
				var inst = tinyMCE.getInstanceById(editor_id);

				if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img") {
					img = tinyMCE.selectedElement;
					tinyMCE.imgElement = img;
				}

				if (img) {
					// Is it a internal MCE visual aid image, then skip this one.
					if (tinyMCE.getAttrib(img, 'name').indexOf('mce_') == 0)
						return true;

					src = tinyMCE.getAttrib(img, 'src');
					alt = tinyMCE.getAttrib(img, 'alt');

					// Try polling out the title
					if (alt == "")
						alt = tinyMCE.getAttrib(img, 'title');

					// Fix width/height attributes if the styles is specified
					if (tinyMCE.isGecko) {
						var w = img.style.width;
						if (w != null && w != "")
							img.setAttribute("width", w);

						var h = img.style.height;
						if (h != null && h != "")
							img.setAttribute("height", h);
					}alert(src);

					border = tinyMCE.getAttrib(img, 'border');
					hspace = tinyMCE.getAttrib(img, 'hspace');
					vspace = tinyMCE.getAttrib(img, 'vspace');
					width = tinyMCE.getAttrib(img, 'width');
					height = tinyMCE.getAttrib(img, 'height');
					align = tinyMCE.getAttrib(img, 'align');
					onmouseover = tinyMCE.getAttrib(img, 'onmouseover');
					onmouseout = tinyMCE.getAttrib(img, 'onmouseout');
					title = tinyMCE.getAttrib(img, 'title');

					// Is realy specified?
					if (tinyMCE.isMSIE) {
						width = img.attributes['width'].specified ? width : "";
						height = img.attributes['height'].specified ? height : "";
					}

					//onmouseover = tinyMCE.getImageSrc(tinyMCE.cleanupEventStr(onmouseover));
					//onmouseout = tinyMCE.getImageSrc(tinyMCE.cleanupEventStr(onmouseout));

					src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, img, true);");
					// Use mce_src if defined
					mceRealSrc = tinyMCE.getAttrib(img, 'mce_src');
					if (mceRealSrc != "") {
						src = mceRealSrc;

						if (tinyMCE.getParam('convert_urls'))
							src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, img, true);");
					}

					//if (onmouseover != "")
					//	onmouseover = eval(tinyMCE.settings['urlconverter_callback'] + "(onmouseover, img, true);");

					//if (onmouseout != "")
					//	onmouseout = eval(tinyMCE.settings['urlconverter_callback'] + "(onmouseout, img, true);");

					action = "update";
				}

				var template = new Array();

				template['file'] = purl+'insertcate.php';
				template['width'] = 300;
				template['height'] = 200 + (tinyMCE.isMSIE ? 25 : 0);

				// Language specific width and height addons
				template['width'] += tinyMCE.getLang('lang_insert_image_delta_width', 0);
				template['height'] += tinyMCE.getLang('lang_insert_image_delta_height', 0);

				if (inst.settings['insertimage_callback']) {
					var returnVal = eval(inst.settings['insertimage_callback'] + "(src, alt, border, hspace, vspace, width, height, align, title, onmouseover, onmouseout, action);");
					if (returnVal && returnVal['src'])
						TinyMCE_AdvancedTheme._insertImage(returnVal['src'], returnVal['alt'], returnVal['border'], returnVal['hspace'], returnVal['vspace'], returnVal['width'], returnVal['height'], returnVal['align'], returnVal['title'], returnVal['onmouseover'], returnVal['onmouseout']);
				} else {
					tinyMCE.openWindow(template, {src : src, alt : "asdfasdf", border : border, hspace : hspace, vspace : vspace, width : width, height : height, align : align, title : title, onmouseover : onmouseover, onmouseout : onmouseout, action : action, inline : "yes"});
				}
				return true;
		}

		return false;
	},

	handleNodeChange : function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {

		if ( (node.nodeName == "SPAN" || node.nodeName == "DIV") && tinyMCE.getAttrib(node, 'class') == "sfcode" ) {
			tinyMCE.switchClass(editor_id + '_dd_code', 'mceButtonSelected');
			return true;
		}
		else if ( any_selection == "" ) {
			tinyMCE.switchClass(editor_id + '_dd_code', 'mceButtonDisabled');
			return true;
		}
		else
			tinyMCE.switchClass(editor_id + '_dd_code', 'mceButtonNormal');
	},

	cleanup : function(type, content, inst) { 
	
		switch (type) {
			case "insert_to_editor_dom":
            break;
			case "insert_to_editor":
			break;
			case "get_from_editor":
			break;
		}
	
	return content; 
	},

	// Private plugin internal methods
	_someInternalFunction : function(a, b) {
		return 1;
	}
};

// Add the plugin class to the list of available TinyMCE plugins
tinyMCE.addPlugin("ecom", TinyMCE_ecom_Plugin);