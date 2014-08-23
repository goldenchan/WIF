<?php
	define( "GD_RESIZER_NO_SMOOTHING_MODE", 0 );
	define( "GD_RESIZER_PHP_IMAGECOPYRESAMPLED", 1 );
	define( "GD_RESIZER_BILINEAR_MODE", 2 );
	define( "GD_RESIZER_BICUBIC_MODE", 3 );	

	class GDResizer 
	{

    	var $img;
		var $_image;
		var $_keepAspectRatio;
		var $_x;
		var $_y;

    	function GDResizer( $image )
        {
        	$this->_image  = $image;
			$this->_keepAspectRatio = true;
        }

		/**
		 * @see GDResizer::generate
		 */
        public function generate( $outFile, $width = 'auto', $height = 'auto',$x = 0,$y = 0 )
        {
        	//
        	// generate the thubmanil but check for errors every time
            //
			
			// also, check if GD is available because otherwise we would get
			// all sorts of nasty errors...
			if( !function_exists( "imagecreate" ))
				return false;
			
            if( !$this->thumbnail( $this->_image ))
			{
            	return false;
			}

			if($width == 'auto' && $height > 0)	//判断是否有自动
			{
				$this->size_height($height);
			}
			else if($height == 'auto' && $width > 0)
			{
				$this->size_width($width);
			}
			else if($height == 'auto' && $width = 'auto')
			{
				$this->size_auto(100);//都是auto 默认100
			}
			else
			{
				if( ($this->img["lebar"] < $width) && ($this->img["tinggi"] < $height) ) {
					$this->img["lebar_thumb"] = $this->img["lebar"];
					$this->img["tinggi_thumb"] = $this->img["tinggi"];
				}
				else {
					$this->img["lebar_thumb"] = $width;
					$this->img["tinggi_thumb"] = $height;
					//$this->calcThumbFormat($width, $height );
				}
			}
			$this->img['x'] = $x;
			$this->img['y'] = $y;

			$type =  $this->img['x'] > 0 || $this->img['y'] > 0 ? 'crop' : 'resize';

            if( !$this->save( $outFile , $type ))
			{
            	return false;
			}

                // depending on the default file creation settings in some hosts,
                // files created may not be readable by the web server
            @chmod( $outFile, 0644 );

            return $outFile;
        }
        
		/** 
		 * @private
		 */
        function thumbnail($imgfile)
        {
        	//detect image format
            //$this->img["format"]=ereg_replace(".*\.(.*)$","\\1",$imgfile);
			$info = getimagesize($imgfile);
			$this->img["format"]=str_replace('IMAGE/','',strtoupper($info['mime']));

            if ($this->img["format"]=="JPG" || $this->img["format"]=="JPEG") {
                $this->img["format"]="JPEG";
                $this->img["src"] = @ImageCreateFromJPEG ($imgfile);
            }
            elseif ($this->img["format"]=="PNG") {
                $this->img["format"]="PNG";
                $this->img["src"] = @ImageCreateFromPNG ($imgfile);
            }
            elseif ($this->img["format"]=="GIF") {
                $this->img["format"]="GIF";
				if( function_exists("imagecreatefromgif")) {
                    $this->img["src"] = @ImageCreateFromGIF ($imgfile);
				}
                else {
					return false;
				}
            }
            else {
            	// not a recognized format
                throw( new Exception( "Trying to generate a thumbnail of an unsupported format!"));
                //die();
            }
			
            // check for errors
            if( !$this->img["src"] )
            	return false;

            // if no errors, continue
            @$this->img["lebar"] = imagesx($this->img["src"]);
            @$this->img["tinggi"] = imagesy($this->img["src"]);
            //default quality jpeg
            $this->img["quality"]=85;

            return true;
        }

		/** 
		 * @private
		 */
        function size_height($size=100)
        {
        	//height
			if( $this->img["lebar"] > $size ) {
				$this->img["tinggi_thumb"]=$size;
				@$this->img["lebar_thumb"] = ($this->img["tinggi_thumb"]/$this->img["tinggi"])*$this->img["lebar"];
			}
			else {
				//$this->img["tinggi_thumb"]=$size;
				$this->img["tinggi_thumb"]=$this->img["tinggi"];
				$this->img["lebar_thumb"]=$this->img["lebar"]; 
			}

            return true;
        }

		/** 
		 * @private
		 */
        function size_width($size=100)
        {
        	//width
			if( $this->img["tinggi"] > $size ) {
				$this->img["lebar_thumb"]=$size;
				@$this->img["tinggi_thumb"] = ($this->img["lebar_thumb"]/$this->img["lebar"])*$this->img["tinggi"];
			}
			else {
				//$this->img["lebar_thumb"] = $size;
				$this->img["lebar_thumb"]=$this->img["lebar"];
				$this->img["tinggi_thumb"] = $this->img["tinggi"];
			}

            return true;
        }
		
		/** 
		 * @private
		 */
        function size_auto($size=100)
        {
        	//size
            if ($this->img["lebar"]>=$this->img["tinggi"]) {
            	$this->img["lebar_thumb"]=$size;
                @$this->img["tinggi_thumb"] = ($this->img["lebar_thumb"]/$this->img["lebar"])*$this->img["tinggi"];
            }
            else {
            	$this->img["tinggi_thumb"]=$size;
                @$this->img["lebar_thumb"] = ($this->img["tinggi_thumb"]/$this->img["tinggi"])*$this->img["lebar"];
            }

            return true;
        }

		/** 
		 * @private
		 */
        function jpeg_quality($quality=75)
        {
        	//jpeg quality
            $this->img["quality"]=$quality;

            return true;
        }
		
        /**
         * returns true if gd2 is available or false otherwise.
         * Based on a comment found in http://fi2.php.net/imagecreatetruecolor by aaron at aaron-wright dot com
         * (credit is due where it is due :)
		 * 
		 * @return true if GD2 is available or false otherwise
         */
        function isGD2Available()
        {            
            // if not, we still check in case the user made a mistake...
            $testGD = get_extension_funcs("gd"); // Grab function list
            if ( !$testGD ) { 
                return false;
            }
            if (in_array ("imagegd2",$testGD)) {
                return true;
            }
            else { 
                return false; 
            }
        }

		/**
		 * resizes an image using several different techniques:
		 *
		 * PHP's own ImageCopyResamplated
		 * Bi-linear filter (slower, but better quality than ImageCopyResampled)
		 * Bi-Cubic filter (slowest, but offers the best quality)
		 * PHP's own ImageCopyResized (fastest one, but offers no antialising or filter)
		 *
		 */
        function ImageResize($dst_img, &$src_img, $dst_x, $dst_y, $src_x, 
                                   $src_y, $dst_w, $dst_h, $src_w, $src_h, 
                                   $resample = GD_RESIZER_NO_SMOOTHING_MODE ) {
           $pxls = intval($src_w / $dst_w)-1;
		   /*if( $dst_w == $dst_h ) {
		   		$length = min($src_w, $src_h);
		   		$src_x = intval( $src_w / 2 ) - intval( $length / 2 );
		   		$src_y = intval( $src_h / 2 ) - intval( $length / 2 );
		   		$src_w = $length;
		   		$src_h = $length;
		   }*/
		   		
		   if( $resample == GD_RESIZER_PHP_IMAGECOPYRESAMPLED ) {
				imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y,$src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
				//if($src_x > 0 && $src_y > 0)
				//	imagecopy($dst_img,$dst_img,0,0,$src_x, $src_y,$dst_w, $dst_h);
		   }
           elseif( $resample == GD_RESIZER_BILINEAR_MODE  ) { //slow but better quality
                ImageTrueColorToPalette( $src_img, false, 256 );
                ImagePaletteCopy ($dst_img, $src_img);
                $rX = $src_w / $dst_w;
                $rY = $src_h / $dst_h;
                $nY = 0;
                for ($y=$src_y; $y<$dst_h; $y++) {
                    $oY = $nY;
                    $nY = intval(($y + 1) * $rY+.5);
                    $nX = 0;
                    for ($x=$src_x; $x<$dst_w; $x++) {
                         $r = $g = $b = $a = 0;
                         $oX = $nX;
                         $nX = intval(($x + 1) * $rX+.5);
                         $c = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $nX, $nY));
                         $r += $c['red']; $g += $c['green']; $b += $c['blue']; $a++;
                         $c = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $nX-$pxls, $nY-$pxls));
                         $r += $c['red']; $g += $c['green']; $b += $c['blue']; $a++;
                         //you can add more pixels here! eg "$nX, $nY-$pxls" or "$nX-$pxls, $nY"
                         ImageSetPixel ($dst_img, ($x+$dst_x-$src_x), ($y+$dst_y-$src_y), ImageColorClosest ($dst_img, $r/$a, $g/$a, $b/$a));
                    }
                }
           } 
           elseif ( $resample == GD_RESIZER_BICUBIC_MODE ) { // veeeeeery slow but better quality
                     ImagePaletteCopy ($dst_img, $src_img);
                     $rX = $src_w / $dst_w;
                     $rY = $src_h / $dst_h;
                     $nY = 0;
                     for ($y=$src_y; $y<$dst_h; $y++) {
                       $oY = $nY;
                       $nY = intval(($y + 1) * $rY+.5);
                       $nX = 0;
                       for ($x=$src_x; $x<$dst_w; $x++) {
                         $r = $g = $b = $a = 0;
                         $oX = $nX;
                         $nX = intval(($x + 1) * $rX+.5);
                         for ($i=$nY; --$i>=$oY;) {
                           for ($j=$nX; --$j>=$oX;) {
                             $c = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $j, $i));
                             $r += $c['red'];
                             $g += $c['green'];
                             $b += $c['blue'];
                             $a++;
                           }
                         }
                         ImageSetPixel ($dst_img, ($x+$dst_x-$src_x), ($y+$dst_y-$src_y), ImageColorClosest ($dst_img, $r/$a, $g/$a, $b/$a));
                       }
                     }
           } 
           else {
             $dst_w++; $dst_h++; //->no black border 
             imagecopyresized($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
           }
			
			
        }

		/** 
		 * @private
		 */
        function save( $save = "" ,$type='resize')
        {
			$fileParts = explode( ".", $save );
			$fileNoExt = implode( ".", $fileParts );
			$fileExt = strtolower($fileParts[count($fileParts)-1]);
		
            //if( function_exists("imagecreatetruecolor")) {
            if( $this->isGD2Available()) {
            	$this->img["des"] = @ImageCreateTrueColor($this->img["lebar_thumb"],$this->img["tinggi_thumb"]);
            }
            else {
            	$this->img["des"] = @ImageCreate($this->img["lebar_thumb"],$this->img["tinggi_thumb"]);
            }

            // check for errors and stop if any, or continue otherwise
            if( $this->img["des"] == "" )
            	return false;
			
			if($type == 'resize')
			{
				$resizeMode = GD_RESIZER_PHP_IMAGECOPYRESAMPLED;
				// resize the image using the mode chosen above
				$this->ImageResize($this->img["des"], $this->img["src"], 0,0,0,0, $this->img["lebar_thumb"], $this->img["tinggi_thumb"], $this->img["lebar"], $this->img["tinggi"], $resizeMode );
			}
			elseif($type == 'crop')
			{
				$this->ImageCrop($this->img["des"],$this->img["src"],$this->img["lebar_thumb"], $this->img["tinggi_thumb"],$this->img['x'],$this->img['y']);
			}
			else
			{
				return false;
			}
			
			// format for thumbnails is the same as the image
				if ($fileExt=="jpg" || $fileExt=="jpeg") {
					$result = @imageJPEG($this->img["des"],"$save",$this->img["quality"]);
				}
				elseif ($fileExt=="png") {
					$result = @imagePNG($this->img["des"],"$save");
				}
				elseif ($fileExt=="gif") {
					if( function_exists("imagegif")) {
						$result = @imageGIF($this->img["des"],"$save");
					}
					else {
						$result = false;
					}
				}

            return $result;
        }
		
		/**
		//crop a image via weight height,$x,AND $y
		* @private
		*/
		function ImageCrop($des_img,&$src_img , $dst_w , $dst_h , $x , $y)
		{
			$x = $x < 0 ? 0 : $x;
			$y = $y < 0 ? 0 : $y;
			imagecopy($des_img, $src_img, 0, 0, $x, $y, $dst_w, $dst_h);
		}

    
		/** 
		 * @private
		 */
        function calcThumbFormat($targetWidth, $targetHeight) {
            
            // lebar = width
            // tinggi = height
            // in malay language
			
            
    	    $ratioimg = $this->img["tinggi"] / $this->img["lebar"];
    	
        	if ($ratioimg < $targetHeight / $targetWidth) {
        	    $this->img["lebar_thumb"] = (int)$targetWidth;
        	    $this->img["tinggi_thumb"] = (int)round($targetWidth * $ratioimg);
        	} else {
        	    $this->img["lebar_thumb"] = (int)round($targetHeight / $ratioimg);
        	    $this->img["tinggi_thumb"] = (int)$targetHeight;
        	}
        	return true;
        }
           
    }
?>