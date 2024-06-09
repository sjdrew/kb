<script language="php">

/**
 * Image Routines
 * 
 * File: subs_image.php
 * Version: 1.0
 *
 * Author: softperfection.com
 *
 * SofPerfection grants unlimited, unrestricted use of this source code to modify
 * redistribute in any fashion. It is completely in the public domain.
 * No warranties are implied or provided.
 */

function isPicture($Fname)
{
	$ext = file_ext($Fname);
	if (strstr("jpg,jpeg,gif,png",strtolower($ext))) {
		return 1;
	}
	return 0;
}

function GenerateThumb($Filename,$Contents,$thumbSize = 160)
{
	if (isPicture($Filename)) {
		// create image
		if (!($im = @ImageCreateFromString($Contents)))
		{
			return;
		}

		// create thumbnail if needs be
		if ((@ImageSX($im) <= $thumbSize) && (@ImageSY($im) <= $thumbSize))
		{
			// image is smaller than the thumbnails -- don't resize
			$new_h = @ImageSY($im);
			$new_w = @ImageSX($im);
		}
		else if (ImageSX($im) > ImageSY($im))
		{
			// width is bigger than height -- height / (width / size)
			$new_w = $thumbSize;
			$new_h = ImageSY($im) / (ImageSX($im) / $thumbSize);
		}
		else
		{
			// height is bigger than width -- (size / height) * width
			$new_h = $thumbSize;
			$new_w = ($thumbSize / ImageSY($im)) * ImageSX($im);
		}

		$newim = ImageCreateTrueColor($new_w,$new_h);
		ImageCopyResampled($newim,$im,0,0,0,0,$new_w,$new_h,@ImageSX($im),@ImageSY($im));

		$tmpfile = tempnam("/tmp","thumb");
		ImageJPEG($newim,$tmpfile);
		$fp = fopen($tmpfile,"r");
		if (!$fp) {
			exit;
		}
		$ImageData = fread($fp,filesize($tmpfile));
		fclose($fp);	
		unlink($tmpfile);

		//echo ImagePNG($newim);
		
		ImageDestroy($im);
		ImageDestroy($newim);

		return $ImageData;

	}

}


</script>