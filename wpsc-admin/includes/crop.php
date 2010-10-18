<?php

/**
 * Jcrop image cropping plugin for jQuery
 * Example cropping script
 * @copyright 2008 Kelly Hallman
 * More info: http://deepliquid.com/content/Jcrop_Implementation_Theory.html
 */
if(isset($_GET['imagename'])){
	$imagename = $_GET['imagename'];
}

$directory = WPSC_IMAGE_URL;//set directory
$path = WPSC_IMAGE_DIR;
$image_data = getimagesize($path.$imagename);
//exit('<pre>'.print_r($image_data, true).'</pre>');
$width = $image_data[0]; //set image dimensions
$height = $image_data[1];
$product_id = $_GET['product_id'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Wp-e-Commerce jCrop</title>
		<script type="text/javascript">

			jQuery(function(){

				jQuery('#cropbox').Jcrop({
					aspectRatio: 1,
					keySupport: false,
					onSelect: showPreview,
					onChange: updateCoords,
					boxWidth:350,
					boxHeight:350
				});
	
			});

			function updateCoords(c)
			{
				jQuery('#x').val(c.x);
				jQuery('#y').val(c.y);
				jQuery('#w').val(c.w);
				jQuery('#h').val(c.h);
			};

			function checkCoords()
			{
				if (parseInt(jQuery('#w').val())) return true;
				alert('Please select a crop region then press submit.');
				return false;
			};
			function showPreview(coords)
			{
				var rx = 100 / coords.w ;
				var ry = 100 / coords.h;
				jQuery('#preview').css({
					width: Math.round(rx * <?php echo $width; ?>) + 'px',
					height: Math.round(ry * <?php echo $height; ?>) + 'px',
					marginLeft: '-' + Math.round(rx * coords.x) + 'px',
					marginTop: '-' + Math.round(ry * coords.y) + 'px'
				});
			};

		</script>

	</head>

	<body>

	<div id="outer">
		<div id='wpsc_thumbnail_preview'>	
			<div id='wpsc_crop_preview'>
				<img src="<?php echo $directory.$imagename; ?>" id="preview" alt="" />
			</div>

			<br style='clear:both' />
			<!-- This is the form that our event handler fills -->
			<form action="" method="post" onsubmit="return checkCoords();">
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="h" name="h" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="imagename" name="imagename" value="<?php echo $imagename; ?>" />		
				<input type="hidden" name="wpsc_admin_action" value="crop_thumb" />
				<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
				<p><input size='2' type="hidden" id="thumbsize" name="thumbsize" value='<?php echo get_option('product_image_height'); ?>' /> </p>
				<p><label for="jpegquality">Jpeg Quality :</label><input size='2' type="text" id="jpegquality" name="jpegquality" value='70' /> %<br /></p>
				<p><input class="button-secondary action"  type="submit" value="Crop Image" /></p>
			</form>
		</div>

		<!-- This is the image we're attaching Jcrop to -->
		<img src="<?php echo $directory.$imagename; ?>" id="cropbox" alt="" />

	</div>
	</body>

</html>
