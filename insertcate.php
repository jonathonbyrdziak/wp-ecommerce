<?php
$filedir = dirname(__FILE__); 
$blogroot = substr($filedir,0,strpos($filedir,'wp-content/'));
require_once($blogroot.'wp-blog-header.php');

$wwwURI = $_SERVER['REQUEST_URI'];
$x = strpos($wwwURI,'wp-admin/');
$tinyURI = substr($wwwURI,0,strpos($wwwURI,'wp-admin/')) . '/wp-includes/js/tinymce';
$tinyURI = get_option('siteurl').$tinyURI;
//exit($wwwURI);

$a = substr($filedir, strpos($filedir,'/wp-content/plugins/'));
$plugindir = substr($a , 0, strpos($a,'/js') );
$pluginURL = get_settings('siteurl') . $plugindir;
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$lang_insert_ecom_title}</title>
	<link type="text/css" rel="stylesheet" href="<?php echo get_option('siteurl'); ?>/wp-content/plugins/admin.css"></link>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
	<!--
	<?php
	global $wpdb;
	
	$fns = ''; $options = '';
	$no = ($i==0)?'':($i+1);
	$cate_sql = "SELECT * FROM ".WPSC_TABLE_PRODUCT_CATEGORIES."";
	$result = $wpdb->get_results($cate_sql,ARRAY_A);
	foreach($result as $category){
		$options .= '<option value="'.$category['id'].'">'.$category['name'].'</option>';
	}
	//echo 'var formnames=new Array('.$fns.');';
	?>

	function init() {
		mcTabs.displayTab('tab', 'panel');
		tinyMCE.setWindowArg('mce_windowresize', false);
	}
	
	function insertSomething() {
		var inst = tinyMCE.selectedInstance;
		var elm = inst.getFocusElement();
	
		no  = document.forms[0].nodename.value;
		full = document.forms[0].full.checked;
		
		html = '[wpsc_category='+no+']';
		if (full == true){
			html = '[wpsc_category='+no+', full]'
		}
		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, '<p>'+html+'</p>');
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	   	tinyMCEPopup.close();
	}
	//-->
	</script>
	<base target="_self" />
</head>
<body id="cforms" onLoad="tinyMCEPopup.executeOnLoad('init();');" style="display: none"> 
	<form onSubmit="insertSomething();" action="#">
	<div class="tabs">
		<ul>
			<li id="tab"><span><a href="javascript:mcTabs.displayTab('tab','panel');"><?php  _e('Pick a Category','cforms'); ?></a></span></li>
		</ul>
	</div>
	<div class="panel_wrapper" style="height:120px;">
		<div id="panel" class="panel current">
			<table border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="cflabel"><label for="nodename"><?php  _e('Your Categories:','cforms'); ?></label></td>
					<td class="cfinput"><select name="nodename"/><?php  echo $options; ?></select>
				</tr>

				<tr>
					<td class="cfinput"><input type="checkbox" name="full"/> Full Display?
				</tr>
			</table>
		</div>

	</div>
	<div class="mceActionPanel">
		<div style="float: left">
				<input type="button" id="insert" name="insert" value="<?php  _e('Insert','cforms'); ?>" onClick="insertSomething();" />
		</div>
		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="<?php  _e('Cancel','cforms'); ?>" onClick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>
</body> 
</html> 