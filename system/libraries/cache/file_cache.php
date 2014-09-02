<?php
/**
 * 文件缓存处理类文件
 * @file file_cache.php
 * @package cache
 * @author 陈金(wind.golden@gmail.com)
 */
/**
 * 文件缓存处理类
 */
class File_Cache {
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
     *  根据key获取文件缓存值
     * @param string $key 关键字
     * @param string $group key群组
     * @param array $condition 查询条件 空数组
     * @param int $expire 数据有效期
     * @return mixed 失败返回null 成功返回缓存数据
     */
    public function get($key = '', $group = '', $condition = array() , $expire = 0) {
        if ($key !== '' && $group !== '') {
            $file_path = $this->_getFileCacheDir($key);
            $file_name = $group . "_" . $key;
            if ($expire > 0) {
                if (file_exists($file_path . $file_name)) {
                    $filetime = @filemtime($file_path . $file_name);
                    if ($filetime + $expire < time()) {
                        @unlink($filename);
                        return null;
                    }
                }
            }
            $data = unserialize(read_file($file_path . $file_name));
            if ($data !== false) {
                $this->set_debug_info(__FUNCTION__, $file_path . $file_name, $data);
                return $data;
            }
        }
        return null;
    }
    /**
     *  根据key设置文件缓存
     * @param string $key 关键字
     * @param string $group key群组
     * @param array $data 待缓存数据
     * @return boolean true or false
     */
    public function set($key = '', $group = '', $data = array()) {
        if ($key !== '' && $group !== '' && is_array($data)) {
            $file_path = $this->_getFileCacheDir($key);
            $file_name = $group . "_" . $key;
            $t = write_file($file_path . $file_name, serialize($data));
            if ($data !== 0) {
                $this->set_debug_info(__FUNCTION__, $file_path . $file_name, $data);
                return $t;
            }
            else {
                $message = "Fatal error: could not write data to file " . $file_path . $file_name;
                throw (new Exception($message));
                die();
            }
        }
        return false;
    }
    /**
     *  根据key删除缓存文件
     * @param string $key 关键字
     * @param string $group key群组
     * @param array $condition 查询条件 空数组
     * @return boolean true or false
     */
    public function delete($key = '', $group = '', $condition = '') {
        if ($key !== '' && $group !== '') {
            $file_path = $this->_getFileCacheDir($key);
            $file_name = $group . "_" . $key;
            if (file_exists($file_path . $file_name)) {
                @unlink($file_path . $file_name);
                $this->set_debug_info(__FUNCTION__, $file_path . $file_name, '');
                return true;
            }
        }
        return false;
    }
    /**
     *  根据key更新缓存文件 可以优化
     * @param string $key 关键字
     * @param string $group key群组
     * @param array $update_info 要更新的数据 空数组
     * @param array $condition 查询条件 空数组
     * @return boolean true or false
     */
    public function update($key = '', $group = '', $update_info = null, $condition = null) {
        return $this->delete($key, $group);
    }
    /**
     *  根据key获取缓存文件dir
     * @param string $key 关键字
     * @param string $group key群组
     * @return boolean true or false
     */
    protected function _getFileCacheDir($key, $group = '') {
        $hash = md5($key . $group);
        $dir = TMP . "cache" . DS . "file" . DS . $hash{0} . $hash{1} . DS . $hash{2} . $hash{3} . DS;
        if (create_dir($dir)) {
            return $dir;
        }
        else {
            trigger_error('Could not create directory ' . $dir);
            return TMP . "/cache/file/";
        }
    }
    /**
     * 设置debug info
     * @param string $method  方法
     * @param string|integer $key 关键字
     * @param array|integer|string $data 数据
     */
    function set_debug_info($method = 'get', $key = '', $data = '') {
        if ($this->debug) {
            $output = '<p><font face=arial size=2 color=000099><b>Real Cache Key </b> ' . $key . ' <b>==></b></font></p>';
            $output.= '<p><font face=arial size=2 color=000099><b>' . ucfirst($method) . ' Cache Data</b> ' . (!is_string($data) && $data === '' ? '' : var_export($data, true)) . '</font></p>';
            $this->debug_info[0] = '<p><font color=800080 face=arial size=2><b>File Cache</b> <b>Debug..</b></font></p>';
            $this->debug_info[] = $output;
        }
    }
}
