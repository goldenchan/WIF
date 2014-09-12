<?php
/**
 * 主配置文件
 * @file config.php
 * @author 陈金(wind.golden@gmail.com)
 */
/**
 * 网站域名
 */
define('SITE_DOMAIN', '127.0.0.1');
/**
 * 全站基本配置信息 所有的基本配置都应放在这个WI_CONFIG 类的静态属性中
 */
class WI_CONFIG {
    /**
     * 数据库配置 默认选default 
     * @var array
     */
    public static $dbs = array(
        'default' => array(
            'dsn' => 'mysql:host=localhost;dbname=db_wy;charset=utf8',
            'user' => 'root',
            'password' => '123456',
            'table_prefix' => 'wy_'
        ) ,
        'remote' => array(
            'dsn' => 'mysql:host=localhost;dbname=db_ch;charset=utf8',
            'user' => 'root',
            'password' => '123456',
            'table_prefix' => 'ch_'
        ) ,
    );
    /**
     * smarty 配置
     * @var array
     */
    public static $smarty = array(
        'compile_dir' => 'templates_c',
        'config_dir' => LANG_PATH,
        'cache_dir' => 'view',
        'compile_check' => true,
        'trim_white_space' => true
    );
    /**
     * 默认模板类名 Simple_View or Smarty_View
     */
    public static $default_template_class = 'Simple_View';
    /**
     * 模板后缀
     * @var string
     */
    public static $tpl_ext = '.tpl';
    /**
     * session 存储方式 目前只支持文件
     * @var string
     */
    public static $session_storage = 'file';
    /**
     * 路由映射
     * @var array
     * @example1  '/user/view/(?P<username>[A-Za-z0-9\-\_]+)'=> array('user','view_username',)//匹配字母,数字,下划线,中划线
     * @example2  '/user/view/(?P<userid>[0-9]+)'=> array( 'UserClass', 'view_user' )//匹配数字
     * @example3  '/browse/(?P<category>[^\/]+)' => array('browse','browse_category') //除了"/"之外的所有匹配
     * @example4  '/api/(?P<method>[A-Za-z.]+)' => array('browse','<method>') 动态方法绑定，方法名即为 匹配到的 $method
     * @example5  '/browse/(?P<category>[^\/]+)' => array('admin/browse','browse_category') //除了"/"之外的所有匹配 其中admin是module名
     */
    public static $routes = array(
        '/' => array(
            'default',
            'index'
        )
    );
}
