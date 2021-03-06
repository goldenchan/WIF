<?php
/**
 * 应用控制器
 * @file app_controller.php
 * @package controller
 * @category  controller
 */
/**
 * 所有的控制器都要继承这个类
 * @note 在../cli/index.php , 可以用newCtrler 会自动创建控制器  php ../cli/index.php newCtrl test   则会在控制器目录下创建一个test_controller的文件，里面申明了Test_Controller的类
 */
abstract class App_Controller extends Controller {
    /**
     * app view对象
     * @var object
     */
    private $_appview = null;
    /**
     * rop debug开关
     * @var boolean
     */
    var $rop_api_debug = false;
    /**
     * 是否加载预定义view变量
     * @var boolean
     */
    var $pre_load_view_params = false;
    /**
     * 是否一次性自动加载model
     * @var boolean
     */
    var $auto_load_models = false;
    /**
     * 是否一次性自动加载helper
     * @var boolean
     */
    var $auto_load_helpers = false;
    /**
     * view cache 开关
     * @var boolean
     */
    var $view_cache = false;
    /**
     * model 缓存方式 支持 file ,mem(memcache缓存),sqlite（用的话需要在configs目录下配置sqlite表），redis
     * @var string file or mem or sqlite or reids
     */
    var $model_cache_type = "file";
    /**
     * 是否自动render 在父类中默认 关闭
     * @var boolean
     */
    var $auto_render = false;
    /**
     * 输出内容类型（text/html，text/plain，text/xml），在父类中默认 text/html
     * @var  string
     */
    var $content_type = "text/html";
    /**
     * 检查是否登录
     * @return  boolean true已登录  or false 未登录
     */
    function checkLogin() {
        return is_array($this->session->read('user_info'));
    }
    /**
     * 获取当前用户资料
     * @param $prop 用户属性
     * @return string|aray
     string 如果用户属性存在，返回对应属性值
     array 属性未赋值 返回当前用户的信息数组
     */
    function me($prop = '') {
        $me = $this->session->read('user_info');
        return $prop !== '' && isset($me[$prop]) ? $me[$prop] : $me;
    }
    /**
     * view对象 重载
     * @param $reload 是否重载view对象属性
     * @see Controller:view();
     */
    public function view($reload = false) {
        if (!isset($this->_appview)) {
            if ($this->content_type === 'text/html') {
                //$this->disableHttpCache();
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: Sun, 17 Mar 2013 05:00:00 GMT');
                if ($this->pre_load_view_params) {
                    $is_login = $this->checkLogin();
                    parent::view()->setValue('is_login', $is_login); //是否登录
                    $is_login && parent::view()->setValue('me', $this->me()); //当前用户信息
                    parent::view()->setValue('c_props', array(
                        'action' => $this->action,
                        'name' => $this->name
                    ));                    
                }
            }
            $this->_appview = parent::view();
        }
        return $this->_appview;
    }
    /**
     * 404跳转
     */
    public function page404() {
        $this->view()->render('default/page404');
        exit;
    }
}
