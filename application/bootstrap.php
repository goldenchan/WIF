<?php
/**
 * bootstrap.php
 * @description : app启动文件
 * @copyright, Chenjin, 2012, All Rights Reserved.
 * @author: Chenjin (wind.golden@gmail.com)
 */
/**
 * 语言文件目录
 */
define('LANG_PATH', APP_ROOT_PATH . "langs" . DS);
/**
 * 临时文件目录
 */
define('TMP', APP_ROOT_PATH . "tmp" . DS); //Path to the TMP directory

/**
 * 全局函数文件
 */
require SYS_ROOT_PATH . "libraries" . DS . 'functions.php';
/**
 * bootstrap class
 */
class WI_Bootstrap {
    /**
     * init function
     */
    static function init() {
        /**
         * 设置include path
         */
        set_include_path(SYS_ROOT_PATH . "libraries" . PATH_SEPARATOR . SYS_ROOT_PATH . "cores" . PATH_SEPARATOR . APP_ROOT_PATH . "controllers" . PATH_SEPARATOR . APP_ROOT_PATH . "models" . PATH_SEPARATOR . APP_ROOT_PATH . "helpers" . PATH_SEPARATOR . SYS_ROOT_PATH . "libraries" . DS . 'cache');
        /**
         *
         */
        //ob_start();
        /**
         * session 初始化
         */
        init_session();
        /**
         * 时区设置
         */
        date_default_timezone_set('Asia/Shanghai');
        /**
         * 注册autoload函数
         */
        spl_autoload_register('auto_load');
        set_error_handler(array(
            new Error_Handler() ,
            'runtimeError'
        ));
        set_exception_handler(array(
            new Exception_Handler() ,
            'runtimeException'
        ));
    }
}
WI_Bootstrap::init();
