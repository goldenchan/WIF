<?php
/**
 * memcached操作封装类文件
 * @file memcached.php
 * @package cache
 * @author 陈金(wind.golden@gmail.com)
 */
include dirname(__DIR__) . DS . 'baselib/Memcache/MemcacheOpt.php';
/**
 * memcached操作封装类
 */
class Mem_Cache {
    /**
     * debug开关
     * @var boolean
     */
    var $debug = false;
    /**
     * debug 信息
     * @var array
     */
    var $debug_info = array();
    /**
     * 构造函数
     */
    function __construct() {
        $this->cache = new WI_Memcache();
    }
    /**
     *  获取memcache缓存中的值
     * @param string $key 可key id
     * @param string $group key组
     * @param array $condition 查询条件 空数组
     * @return retval array|string|boolean|int value值,缓存开关关闭时 或者值不存在时 返回false
     * @access public
     */
    public function get($key, $group = '', $condition = array()) {
        $real_key = self::getRealKey($key, $group);
        $data = $this->cache->get($real_key);
        $this->set_debug_info(__FUNCTION__, $real_key, $data);
        return $data;
    }
    /**
     *  获取memcache缓存中的值
     * @param string $key 关键字
     * @param string $group key群组
     * @param string $value 关键字对应的值
     * @param int $expire_date 数据有效期
     * @return retval false 缓存开关关闭时 或者值不存在时
     * @return retval mixed value值
     * @access public
     */
    public function set($key, $group = '', $value, $expire_date = 0) {
        $real_key = self::getRealKey($key, $group);
        $t = $this->cache->set($real_key, $value, $expire_date);
        $this->set_debug_info(__FUNCTION__, $real_key, $value);
        return $t;
    }
    /**
     *  更新memcache缓存中的值
     * @param string $key 关键字
     * @param string $group key群组
     * @param string $value 关键字对应的值
     * @param array $condition 查询条件 空数组
     * @param int $expire_date 数据有效期
     * @return retval false 缓存开关关闭时 或者值不存在时
     * @return retval mixed value值
     * @access public
     */
    public function update($key, $group = '', $value = '', $condition = '', $expire_date = 0) {
        $real_key = self::getRealKey($key, $group);
        $t = $this->cache->replace($real_key, $value, $expire_date);
        $this->set_debug_info(__FUNCTION__, $real_key, $value);
        return $t;
    }
    /**
     *  删除memcache缓存中的值
     * @param string $key 关键字
     * @param string $group key群组
     * @param array $condition 查询条件 空数组
     * @return boolean true or false
     * @access public
     */
    public function delete($key, $group = '', $condition = '') {
        $real_key = self::getRealKey($key, $group);
        $t = $this->cache->delete($real_key);
        $this->set_debug_info(__FUNCTION__, $real_key, '');
        return $t;
    }
    /**
     *  组建memcache缓存中的key值
     * @param string $key 关键字
     * @param string $group key群组
     */
    private function getRealKey($key, $group) {
        return ($group === '' ? '' : $group . '_') . $key;
    }
    /**
     * 设置debug info
     * @param string $method  方法
     * @param string|integer $key 关键字
     * @param array|integer|string $data 数据
     */
    function set_debug_info($method = 'get', $key = '', $data = '') {
        if ($this->debug) {
            $output.= '<p><font face=arial size=2 color=000099><b>Real Cache Key </b> ' . $key . ' <b>==></b></font></p>';
            $output.= '<p><font face=arial size=2 color=000099><b>' . ucfirst($method) . ' Cache Data</b> ' . (!is_string($data) && $data === '' ? '' : var_export($data, true)) . '</font></p>';
            $this->debug_info[0] = '<p><font color=800080 face=arial size=2><b>Mem Cache</b> <b>Debug..</b></font></p>';
            $this->debug_info[] = $output;
        }
    }
}

