<?php

	
	//define( "DEFAULT_PATH_TO_CONVERT", "/usr/bin/convert" );
	define( "DEFAULT_PATH_TO_CONVERT",substr(PHP_OS,0,3)=='WIN' ? "C:\cygwin\bin\convert" : " /usr/bin/convert");

	/**
	 *
	 * \ingroup Gallery_resizer
	 *
     * Back end class for generating thumbnails with ImageMagick. It requires the tool
	 * 'convert' to be installed somewhere in the filesystem. The exact location is determined
	 * via the config setting "path_to_convert", but it will default to <b>/usr/bin/convert</b>
	 * if the setting does not exist.
     */
	class ImageMagickResizer 
	{
		var $img;
		var $_image;
		var $_keepAspectRatio;
    	/**
         * Constructor.
         */
    	function ImageMagickResizer( $image )
        {
        	$this->_image  = $image;
			$this->_keepAspectRatio = true;
        }

		/**
		 * @see GalleryResizer::generate
		 */
        public function generate( $outFile, $width, $height,$x = 0,$y = 0 )
        {
			if(!$this->thumbnail( $this->_image ))
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
			if($x >= 0 || $y >= 0)  //crop
			{
				$command = DEFAULT_PATH_TO_CONVERT." -crop ".$this->img["lebar_thumb"]."x".$this->img["tinggi_thumb"]."+$x+$y +repage"." \"".$this->_image."\" \"".$outFile."\"";
			}
			else
			{
				$command = DEFAULT_PATH_TO_CONVERT." -thumbnail ".$this->img["lebar_thumb"]."x".$this->img["tinggi_thumb"]." \"".$this->_image."\" \"".$outFile."\"";   // -geometry 有待研究
			}
			// run the command
            $cmdOutput = system($command, $retval);
			if($x >= 0 || $y >= 0)
			{
				//file_put_contents(TMP.'d.txt',$command);
				//file_put_contents(TMP.'a.txt',$retval);
			}
                // check if there was an error creating the thubmnail
            if($cmdOutput === FALSE || $retval == 1)
			{
            	return false;
			}

                // depending on the default file creation settings in some hosts, files created via
                // ImageMagick may not be readable by the web server
            chmod( $outFile, 0644 );
            
            return $outFile;
        }

		/** 
		 * @private
		 */
       private function thumbnail($imgfile)
        {
        	//detect image format
            //$this->img["format"]=ereg_replace(".*\.(.*)$","\\1",$imgfile);
			$info = @getimagesize($imgfile);
            // if no errors, continue
            $this->img["lebar"] = $info[0];
            $this->img["tinggi"] = $info[1];
		    if($this->img["lebar"] <= 0 || $this->img["tinggi"] < 0)
			{
				return false;
			}
            return true;
        }


		/** 
		 * @private
		 */
        private function size_height($size=100)
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
        private function size_width($size=100)
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
        private function size_auto($size=100)
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

		
    }
?>
