<?php
/**
 * Name: class.wi_image.php
 * Description: 图片缩放 文字水印
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2010-6-3
 * Version: 1.0
 */

require( __DIR__.'/Image/Resizer/gd_resizer.php');
require( __DIR__.'/Image/Resizer/imagemagick_resizer.php');

class WI_Image
{
	var $resizer;

	function WI_Image($image , $resize_method = 'gd')
	{
		if($resize_method == 'gd')
		{
			$this->resizer = new GDResizer($image);
		}
		else if($resize_method == 'imagemagick')
		{
			$this->resizer = new ImageMagickResizer($image);
		}
		else
		{
			trigger_error('Image: missing resizer Class', E_USER_ERROR);
			exit(1);
		}
	}

	function generate($outFile, $width, $height,$x = 0,$y = 0)
	{
		return $this->resizer->generate($outFile, $width, $height,$x,$y);
	}
} 
