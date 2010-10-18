<?php
/** 
This code appears to no longer be used.
*/


$imagetype = @getimagesize($imagepath); //previously exif_imagetype()
if(file_exists($imagepath) && is_numeric($height) && is_numeric($width)) {
  switch($imagetype[2]) {
    case IMAGETYPE_JPEG:
    //$extension = ".jpg";
    $src_img = imagecreatefromjpeg($imagepath);
    $pass_imgtype = true;
    break;

    case IMAGETYPE_GIF:
    //$extension = ".gif";
    $src_img = imagecreatefromgif($imagepath);
    $pass_imgtype = true;
    break;

    case IMAGETYPE_PNG:
    //$extension = ".png";
    $src_img = imagecreatefrompng($imagepath);
    imagesavealpha($src_img,true);
    ImageAlphaBlending($src_img, false);
    $pass_imgtype = true;
    break;

    default:
    $pass_imgtype = false;
    break;
	}

  if($pass_imgtype === true) {
    $source_w = imagesx($src_img);
    $source_h = imagesy($src_img);

 //Temp dimensions to crop image properly
    $temp_w = $width;
    $temp_h = $height;
    if ( $source_w < $source_h ) {
       $temp_h = ($width / $source_w) * $source_h;
    } else {
       $temp_w = ($height / $source_h) * $source_w;
    }

    // Create temp resized image
    $temp_img = ImageCreateTrueColor( $temp_w, $temp_h );
    $bgcolor = ImageColorAllocate( $temp_img, 255, 255, 255 );
    ImageFilledRectangle( $temp_img, 0, 0, $width, $height, $white );
    ImageAlphaBlending( $temp_img, TRUE );

    ImageCopyResampled( $temp_img, $src_img, 0, 0, 0, 0, $temp_w, $temp_h, $source_w, $source_h );

    $dst_img = ImageCreateTrueColor($width,$height);
    $bgcolor = ImageColorAllocate( $dst_img, 255, 255, 255 );
    ImageFilledRectangle( $dst_img, 0, 0, $width, $height, $white );
    ImageAlphaBlending($dst_img, TRUE );

    // X & Y Offset to crop image properly
    $w1 = ($temp_w/2) - ($width/2);
    $h1 = ($temp_h/2) - ($height/2);

    //ImageCopyResampled( $dst_img, $temp_img, 0, 0, $w1, $h1, $width, $height, $width, $height );
		ImageCopy( $dst_img, $temp_img, $w1, $h1, 0, 0, $temp_w, $temp_h );
    
    if($imagetype[2] == IMAGETYPE_PNG) {
      imagesavealpha($dst_img,true);
      ImageAlphaBlending($dst_img, false);
		}
	
	$image_quality = wpsc_image_quality();
	
    //ImageCopyResampled($dst_img,$src_img,0,0,0,0,$width,$height,$source_w,$source_h);
    switch($imagetype[2]) {
      case IMAGETYPE_JPEG:
      ImageJPEG($dst_img, $image_output, $image_quality);
      break;

      case IMAGETYPE_GIF:
      if(function_exists("ImageGIF")) {
        @ ImageGIF($dst_img, $image_output);
			} else {
				ImageAlphaBlending($dst_img, false);
				@ ImagePNG($dst_img, $image_output);
			}
      break;

      case IMAGETYPE_PNG:
      @ ImagePNG($dst_img, $image_output);
      break;
		}
    usleep(50000);  //wait 0.05 of of a second to process and save the new image
    imagedestroy($dst_img);
	}
} else {
	move_uploaded_file($imagepath, ($imagedir.basename($_FILES['image']['name'])));
	$image = $wpdb->escape(basename($_FILES['image']['name']));
}
?>
