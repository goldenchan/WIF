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
 * cookie域名
 */
define('COOKIE_DOMAIN', '127.0.0.1');
/**
 * 全站基本配置信息 所有的基本配置都应放在这个WI_CONFIG 类的静态属性中
 */
class WI_CONFIG {
    /**
     * 数据库配置 host
     * @var string
     */
    public static $mysql_dbhost = 'your host';
    /**
     * 数据库配置 db user
     * @var string
     */
    public static $mysql_dbuser = 'db user';
    /**
     * 数据库配置 db password
     * @var string
     */
    public static $mysql_dbpassword = 'dp password';
    /**
     * 数据库配置 db name
     * @var string
     */
    public static $mysql_dbname = 'db name';
    /**
     * 数据库配置 db charset
     * @var string
     */
    public static $mysql_dbcharset = 'utf8';
    /**
     * 数据库配置 table name prefix
     * @var string
     */
    public static $mysql_tbprefix = 'table prefix';
    /**
     * memcache master
     */
    //public static $memcache_master = array("memcache server master");
    
    /**
     * memcache slave
     */
    //public static $memcache_slave = array("memcache server slave");
    
    /**
     * redis hosts
     */
    //public static $redis_hosts =array('tcp://10.0.0.1:6379','server host2','...');
    
    /**
     * shpnix host
     */
    //public static $shpnix_host = 'shpnix host';
    
    /**
     * shpnix port
     */
    //public static $shpnix_port = 9312;
    
    /**
     * beanstalk host
     */
    //public static $pheanstalk_host = 'beanstalk host';
    
    /**
     * smarty template compile dir
     * @var string
     */
    public static $smarty_compile_dir = 'templates_c';
    /**
     * smarty template config dir
     * @var string
     */
    public static $smarty_config_dir = LANG_PATH;
    /**
     * smarty template cache dir
     * @var string
     */
    public static $smarty_cache_dir = 'view';
    /**
     * smarty template file extension
     * @var string
     */
    public static $smarty_tpl_ext = '.tpl';
    /**
     * smarty template 编译是是否检查文件有改动
     * @var string
     */
    public static $smarty_compile_check = true; 
    /**
     * smarty template html内容去掉空白部分
     * @var string
     */
    public static $trim_white_space = FALSE;
    /**
     * session 存储方式
     * @var string
     */
    public static $session_storage = 'file'; //session 存储方式，目前只支持文件
    
    /**
     * email config
     */
    /*public static $email = array('from'=>'{from email}',
                    'from_name'=>'{from name}',
                    'smtp_username'=>'{smtp username}',
                    'smtp_pwd'=>'{smtp password}',
                    'smtpHostNames'=>'{smtp host}',
                    'port'=>465,
                    'smtp_secure'=>'ssl');*/
    /**
     * 应用 config
     */
    //public static $find_pwd_expire_time = 172800;//找回密码链接两天过期
    //public static $default_salt = 'Va193s1^d(E4ddw3';//默认hash_key
    
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
        ) ,
    );
}
