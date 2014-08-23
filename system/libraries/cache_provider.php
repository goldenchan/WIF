<?php
/**
* 数据缓存提供类文件
* @file cache_provider.php
* @package cache
* @author 陈金(wind.golden@gmail.com)
 */		
/**
 * 数据缓存提供类
 */
class Cache_Provider {
    /**
    * 实例化后的对象
    * @var object
    */
    private $instance = null;
    
    /**
     * 当前缓存类型
     * @var string
     */
    private $cache_type =null;
    
    /**
     * 支持的缓存类型
     * @var array 
     */
    var $supported_cache_types = array('file','mem','sqlite','redis');// add redis cache 2014.07.22
    
    /**
     * 构造函数
     * @param string $cache_type 缓存类型
     * @param boolean $debug debug 开关
     */
	function __construct($cache_type  = '',$debug = false)
	{
		if(in_array($cache_type ,$this->supported_cache_types))
		{
			$this->cache_type = $cache_type;
			
			$this->getInstance()->debug = $debug;
		}
		else
		{
			throw new Exception('CacheProvider::__construct, '.$cache_type.' is not the supported cache ');
		}
	}
    
    /**
     * 获取实例化对象
     */
	private function getInstance()
	{
		if(!is_object($this->instance))
		{
			$cache_class  = ucfirst($this->cache_type).'_Cache';
			$this->instance = new $cache_class;
		}
		return $this->instance;
	}
    
    /**
     * get value
     * @param string|integer $key 缓存key 一般是数字
     * @param string $group 缓存组
     * @param array $condition 查询条件
     * @return array|string|integer|boolean 缓存值
     */
	function get($key,$group='',$condition=array())
	{
		return $this->getInstance()->get($key,$group,$condition);
	}
    
    /**
     * set value
     * @param string|array $key 缓存key
     * @param string $group 组
     * @param string|array|boolean|integer $value 缓存值
     * @param integer $expired_date 数据有效期
     * @return boolean true or false
     */
	function set($key ,$group='', $value, $expire_date=0)
	{
		return $this->getInstance()->set($key ,$group, $value, $expire_date);
	}
    
    /**
     * 删除缓存
     * @param string|integer $key 缓存key 一般是数字
     * @param string $group 缓存组
     * @param array $condition 查询条件
     * @return boolean true or false
     */
	function delete($key ,$group='',$condition='')
	{
		return $this->getInstance()->delete($key ,$group,$condition);
	}
    
    /**
     * 更新缓存
     * @param string|integer $key 缓存key 一般是数字
     * @param string $group 缓存组
     * @param string|array|boolean|integer $value 缓存值
     * @param array $condition 查询条件
     * @return boolean true or false
     */
	function update($key ,$group='',$value ='', $condition= '' )
	{
		return $this->getInstance()->update($key ,$group,$value, $condition );
	}
    
    /**
     * trace log
     */
	function trace_log()
	{
		return $this->getInstance()->debug_info;
	}
}
