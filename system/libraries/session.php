<?php
/**
 * session处理类
 * @file session.php
 * @author 陈金(wind.golden@gmail.com)
 * @SQL：
 CREATE TABLE `sessions` (
 `id` CHAR(32) NOT NULL,
 `data` longtext NOT NULL,
 `last_accessed` int(10) NOT NULL,
 PRIMARY KEY  (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */
/**
 * session class
 */
class Session {
    /**
     * session model
     * @var object
     */
    private $session_model = NULL;
    /**
     * session 存储方式
     * @var string
     */
    private $storage = 'file'; // file 或者 db    
    /**
     * 构造函数
     */
    function __construct() {
        if(isset($_SESSION['write']) && $_SESSION['write']){//是否有用户写入session
            $currentCookieParams = session_get_cookie_params();
            $time = time();
            if (isset($_SESSION['LAST_ACTIVITY']) && ($time - $_SESSION['LAST_ACTIVITY'] > $currentCookieParams["lifetime"])) {
                // last request was more than expired seconds
                session_unset(); // unset $_SESSION variable for the run-time
                session_destroy(); // destroy session data in storage
            }
            $_SESSION['LAST_ACTIVITY'] = $time; // update last activity time stamp
        }
    }
    /**
     * 根据ID读取sesion
     * @param $sid session key
     * @param $default 默认值
     * @return string|array|boolean|integer 返回值
     */
    function read($sid, $default = '') {
        if (is_object($this->session_model)) {
            $result = $this->session_model->findById($sid);
            if (!empty($result)) {
                return empty($result['data']) ? $default : $result['data'];
            }
            return $default;
        }
        else {
            return (isset($_SESSION[$sid]) ? $_SESSION[$sid] : $default);
        }
    }
    /**
     * 获取整个session数据, 目前不支持数据库型session
     */
    function getAllData() {
        return $_SESSION;
    }
    /**
     * 根据ID和数据写sesion
     * @param $sid session key
     * @param $data session value
     * @return boolean true or false
     */
    function write($sid, $data) {
        !isset($_SESSION['write']) && $_SESSION['write'] = true;
        if (is_object($this->session_model)) {
            return $this->session_model->save(array(
                'id' => $sid,
                'data' => $data
            ));
        }
        else {
            $_SESSION[$sid] = $data;
            return true;
        }
    }
    /**
     * 根据ID删除sesion
     * @param $sid sessin key
     * @return boolean true or false
     */
    function delete($sid) {
        if (is_object($this->session_model)) {
            return $this->session_model->delete(array(
                'id' => $sid,
                'data' => $data
            ));
        }
        else {
            $_SESSION[$sid] = null;
            return true;
        }
    }
}
