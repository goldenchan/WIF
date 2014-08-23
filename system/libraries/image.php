<?php
/**
* 图片处理类
* @file image.php
* @package helper
* @author 陈金(wind.golden@gmail.com)
*/	
include(__DIR__.DS.'baselib'.DS.'class.wi_image.php');
/**
 * 图片处理类
 */
class Image
{
    /**
     * 最大文件大小   k 为单位
     * @var integer
     */
    var $max_upload_size = 2097152;	//2M
    
    /**
     * 最小文件大小
     * @var integer
     */
    var $min_upload_size = 19200;//120x160 avatar min size
    
    /**
     * 缩略图尺寸
     * @var integer
     */
    var $thumbnail_width = 50;
    
    /**
     * 缩略图高度
     * @var integer
     */
    var $thumbnail_height = 50;

    /**
     * 默认图片后缀
     * @var string
     */
    var $image_ext = '.gif';

    /**
     * 图片对象
     * @var object
     */
    var $image_obj = null;
    
    /**
     * 错误信息
     */
    var $errormsg = '';

    /**
     * 支持的格式
     */
    var $support_ext = array ('.jpeg', '.jpg', '.png', '.gif');
    
    /**
     * 图片尺寸
     */
	var $image_size = array ('avatar'=>array(array(110,135)));	//其他目录做特殊处理
    
    /**
     * 构造函数
     * @param string $src_image 图片路径
     */
	function __construct($src_image)
	{
		//$this->image_obj = new WI_Image($src_image,'imagemagick'); //linux /user/bin/convert -crop切图
		$this->image_obj = new WI_Image($src_image,'gd'); //PHP GD库切图
	}
	
	/** 
	* 生成头像或图片缩略图
	*   @param string $file_path 源图片位置
	*   @param boolean  $delete_origin 是否删除原图
	*   @param string $upload_dir 缩略图上传路径
	*   @return boolean true or false
	*/
    public function thumb($source_file_path = null,$dest_img_path=null,$delete_source=true) 
    {
        $srcImage = $file_path;
		$tmpDesImage = $this->get_full_dir($key_id,'disk',$upload_dir).DS.$key_id.($index >0 ? "_{$index}":"").'_%sx%s'.$this->image_ext;
        
		foreach ($this->image_size[$upload_dir] as $k => $v)
		{
			$zoom = $this->scale($srcImage, sprintf($tmpDesImage,$v[0],$v[1]), $v[0], $v[1]);
		}

        if($zoom && $delete_origin == true)
        {
            @unlink($srcImage);
        }

		return $zoom;
    }
	
	/** 
	* 指定宽高和坐标剪切 
	*   @param string  $desImage 目的图片位置
	*   @param int $width  缩略图宽
	*   @param int $height 缩略图高
	*   @param int $x x坐标
	*   @param int $y y坐标
	*   @return boolean true or false
	*/
	public function crop($desImage , $width , $height , $x , $y)
	{
		return $this->image_obj->generate($desImage,$width,$height , $x , $y);
	}

	/** 
	* 指定宽高缩放
	*   @param string  $objImg 目的图片位置
	*   @param int $width  缩略图宽
	*   @param int $height 缩略图高
	*   @return boolean true or false
	*/
	public function scale($objImg,$width,$height)
	{
		return $this->image_obj->generate( $objImg, $width, $height);
	}
}
