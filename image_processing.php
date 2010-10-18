<?php
function image_processing($image_input, $image_output, $width = null, $height = null,$imagefield='') {
global $wpdb;
	/*
	* this handles all resizing of images that results in a file being saved, if no width and height is supplied, then it just copies the image
	*/
	$imagetype = getimagesize($image_input);
	if(file_exists($image_input) && is_numeric($height) && is_numeric($width) && function_exists('imagecreatefrompng') && (($height != $imagetype[1]) && ($width != $imagetype[0]))) {
		switch($imagetype[2]) {
			case IMAGETYPE_JPEG:
			//$extension = ".jpg";
			$src_img = imagecreatefromjpeg($image_input);
			$pass_imgtype = true;
			break;
	
			case IMAGETYPE_GIF:
			//$extension = ".gif";
			$src_img = imagecreatefromgif($image_input);
			$pass_imgtype = true;
			break;
	
			case IMAGETYPE_PNG:
			//$extension = ".png";
			$src_img = imagecreatefrompng($image_input);
			//  imagesavealpha($src_img,true);
			//  ImageAlphaBlending($src_img, false);
			$pass_imgtype = true;
			break;
	
			default:
			move_uploaded_file($image_input, ($imagedir.basename($_FILES[$imagefield]['name'])));
			$image = $wpdb->escape(basename($_FILES[$imagefield]['name']));
			return true;
			exit();
			break;
		}
	
		if($pass_imgtype === true) {
			$source_w = imagesx($src_img);
			$source_h = imagesy($src_img);
			
			//Temp dimensions to crop image properly
			$temp_w = $width;
			$temp_h = $height;
			// if the image is wider than it is high and at least as wide as the target width. 
				if (($source_h <= $source_w)) {
					if ($height < $width ) {
						$temp_h = ($width / $source_w) * $source_h;
					} else {
						$temp_w = ($height / $source_h) * $source_w;
					}
					//$temp_w = ($height / $source_h) * $source_w;
				} else {
					$temp_h = ($width / $source_w) * $source_h;
				}
	
			// Create temp resized image
//			exit(get_option('product_image_height'));
			$temp_img = ImageCreateTrueColor( $temp_w, $temp_h );
			$bgcolor = ImageColorAllocate( $temp_img, 255, 255, 255 );
			ImageFilledRectangle( $temp_img, 0, 0, $width, $height, $bgcolor );
			ImageAlphaBlending( $temp_img, TRUE );
			if($imagetype[2] == IMAGETYPE_PNG) {
				imagesavealpha($temp_img,true);
				ImageAlphaBlending($temp_img, false);
			}
	
			// resize keeping the perspective
			Imagecopyresampled( $temp_img, $src_img, 0, 0, 0, 0, $temp_w, $temp_h, $source_w, $source_h );
			
			
			if($imagetype[2] == IMAGETYPE_PNG) {
				imagesavealpha($temp_img,true);
				ImageAlphaBlending($temp_img, false);
			}
			
			
			$dst_img = ImageCreateTrueColor($width,$height);
			$white = ImageColorAllocate( $dst_img, 255, 255, 255 );
			ImageFilledRectangle( $dst_img, 0, 0, $width, $height, $white );
			ImageAlphaBlending($dst_img, TRUE );
			imagecolortransparent($dst_img, $white);

	
			// X & Y Offset to crop image properly
			if($temp_w < $width) {
				$w1 = ($width/2) - ($temp_w/2);
			} else if($temp_w == $width) {
				$w1 = 0;
			} else {
				$w1 = ($width/2) - ($temp_w/2);
			}
			
			if($imagetype[2] == IMAGETYPE_PNG) {
				imagesavealpha($dst_img,true);
				ImageAlphaBlending($dst_img, false);
			}
				
				
			// Final thumbnail cropped from the center out.
			//ImageCopyResampled( $dst_img, $temp_img, 0, 0, $w1, $h1, $width, $height, $width, $height );
			ImageCopy( $dst_img, $temp_img, $w1, $h1, 0, 0, $temp_w, $temp_h );
			
			$image_quality = wpsc_image_quality();
			
			switch($imagetype[2]) {
				case IMAGETYPE_JPEG:
				if(@ ImageJPEG($dst_img, $image_output, $image_quality) == false) { return false; }
				break;
	
				case IMAGETYPE_GIF:
				if(function_exists("ImageGIF")) {
					if(@ ImageGIF($dst_img, $image_output) == false) { return false; }
				} else {
					ImageAlphaBlending($dst_img, false);
					if(@ ImagePNG($dst_img, $image_output) == false) { return false; }
				}
				break;
	
				case IMAGETYPE_PNG:
				imagesavealpha($dst_img,true);
				ImageAlphaBlending($dst_img, false);
				if(@ ImagePNG($dst_img, $image_output) == false) { return false; }
				break;
			}
			usleep(50000);  //wait 0.05 of of a second to process and save the new image
			imagedestroy($dst_img);
			//$image_output
			
			$stat = stat( dirname( $image_output ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $image_output, $perms );
			return true;
		}
	} else {
		copy($image_input, $image_output);
		$image = $wpdb->escape(basename($_FILES[$imagefield]['name']));
		return $image;
	}
	return false;
}



/**
 * WPSC Image Quality
 *
 * Returns the value to use for image quality when creating jpeg images.
 * By default the quality is set to 75%. It is then run through the main jpeg_quality WordPress filter
 * to add compatibility with other plugins that customise image quality.
 *
 * It is then run through the wpsc_jpeg_quality filter so that it is possible to override
 * the quality setting just for WPSC images.
 *
 * @since 3.7.6
 *
 * @param (int) $quality Optional. Image quality when creating jpeg images.
 * @return (int) The image quality.
 */
function wpsc_image_quality( $quality = 75 ) {
	
	$quality = apply_filters( 'jpeg_quality', $quality );
	return apply_filters( 'wpsc_jpeg_quality', $quality );
	
}



?>
