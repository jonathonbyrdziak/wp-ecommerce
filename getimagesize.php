<?php
if(is_file($imagepath))
  {
  $imagetype = @getimagesize($imagepath); //previously exif_imagetype()
  }
?>
