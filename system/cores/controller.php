<?php
/**
 * 抽象控制器类
 * @package controller
 * @author wind.golden@gmail.com(陈金)
 */
/** 
 *
 * 基础控制器 Base class with basic methods related to controllers. This class should not be used directly.
 *
 */
abstract class Controller {
    /**
     * 模块名
     * @var string
     */
    public $module = null;
    /**
     * 类名
     * @var string
     */
    public $name = null;
    /**
     * 调用方法名
     * @var string
     */
    public $action = null;
    /**
     * 实例化后的model对象
     * @var array
     */
    protected $model_objects = array();
    /**
     * 实例化后的helper对象
     * @var array
     */
    protected $helper_objects = array();
    /**
     * View 类对象
     * @var object
     */
    private $_view = null;
    /**
     * 输出内容类型 //text/html or text/plain or text/xml
     * @var string
     */
    protected $content_type = 'text/html';
    /**
     * view 字符集
     * @var string
     */
    protected $charset = 'utf-8';
    /**
     * 是否跨域
     * @var boolean
     */
    protected $cross_domain = true; //js是否跨域
    /**
     * View cache开关
     * @var boolean
     */
    protected $view_cache = false;
    /**
     * View cache 需要的数据
     * @var array
     */
    protected $data_for_view_cache = null;
    /**
     * 自动render 如果设置为true 则一个controller只能有一种content_type的view对象
     * @var boolean
     */
    protected $auto_render = false;
    /**
     * model 缓存类型
     * @var string
     */
    protected $model_cache_type = 'file';
    /**
     * post 数据 不包含 file
     * @var array
     */
    protected $post_data = null;
    /**
     * post file 数据
     * @var array
     */
    protected $post_files = null;
    /**
     * GET 数据
     * @var array
     */
    protected $params_data = null;
    /**
     * 验证得到的错误信息
     * @var array
     */
    protected $validate_error_mesages = array();
    /**
     * Session类
     * @var object
     */
    protected $session = null;
    /**
     * 在执行action 前 是否执行以init前缀的函数
     * @var boolean
     */
    protected $exe_init = true;
    /**
     * 在执行action 后 是否执行以shutdown前缀的函数
     * @var boolean
     */
    protected $exe_shutdown = true;
    /**
     * model debug 开关
     * @var boolean
     */
    protected $model_debug = false;
    /**
     *view debug 开关
     * @var boolean
     */
    protected $view_debug = false;
    /**
     * router 过来的变量
     * @var array
     */
    public $router_params = null;
    /**
     * Constructor
     *
     * @param array $reset_properties 需要重置的属性（cli用）
     */
    public function __construct($reset_properties = null) {
        default_value($this->name,Router::$controller);//没有controller 数据则从Router中获取
        $this->action = Router::$action;//action数据从Router中获取
        $this->router_params = Router::$params;//router_params数据从Router中取
        $this->module = Router::$module;//module数据从Router中取
        $this->post_data = $_POST;
        $this->params_data = $_GET;
        $this->post_files = $_FILES; //$_FILES处理
        //可以重置任意属性 目前只给cli模式和api方式调用
        if (is_array($reset_properties)) {
            foreach ($reset_properties as $property => $value) {
                $this->$property = $value;
            }
        }
        //初始化Session类
        $this->session = new Session(WI_CONFIG::$session_storage);
        //是否自动render
        $this->auto_render && register_shutdown_function(array(
            $this->view() ,
            'render'
        ));
    }
    /**
     *  加载model类
     * @param string $model_name 要加载的无后缀model类名
     * @return object
     * @access protected
     */
    protected function model($model_name) {
        if (!isset($this->model_objects[$model_name])) {
            //include_once APP_ROOT_PATH."models".DS.word_underscore($model_name).'_model.php';
            $model_class_name = ucfirst($model_name) . '_Model';
            $model_obj = new $model_class_name();
            $model_obj->setModelCache($this->model_cache_type);
            $model_obj->setDebugMode($this->model_debug);
            $this->model_objects[$model_name] = $model_obj;
            is_object($model_obj) && register_shutdown_function(array(
                $model_obj,
                'showDbDebug'
            ));
        }
        return $this->model_objects[$model_name];
    }
    /**
     *  加载helper类
     * @param string $helper_name 要加载的无后缀helper类
     * @return object
     * @access public
     */
    protected function helper($helper_name) {
        $this->setIncludePath();
        if (!isset($this->help_objects[$helper_name])) {
            $helper_class_name = ucfirst($helper1) . '_Helper';
            $helper_obj = new $helper_class_name();
            $helper = $helper_obj->alias;
            $this->helper_objects[$helper1] = $this->$helper = $helper_obj;
        }
        return $this->helper_objects[$model_name];
    }
    /**
     *  获取view对象
     * @param boolean $reload 是否强制重载
     * @return object View对象
     *
     * @access public
     */
    protected function view($reload = false) {
        if (!isset($this->_view) || $reload) {
            if ($this->content_type === 'text/html') {
                $tpl_path = $this->name;
                $tpl_filename =  $this->action;
                $view = new View('text/html', $tpl_filename, $tpl_path, $this->module, $this->view_cache, $this->data_for_view_cache);
            }
            elseif (in_array($this->content_type, array(
                'text/plain',
                'text/xml'
            ))) {
                $view = new View($this->content_type);
                $view->crossDomain = $this->cross_domain;
            }
            else {
                throw new Exception('Error view content type');
            }
            $this->_view = $view;
        }
        return $this->_view;
    }
    /**
     *  设置view对象输出内容类型
     * @param string $type  输出类型 "text/html" or "text/plain" or "text/xml"
     *
     * @access protected
     */
    protected function setContentType($type) {
        $this->content_type = $type;
    }
    /**
     * 用api 的方式 执行action 忽略掉GET POST FILE 的参数
     * @param string  $controller  当前模块下的控制器名 不可以跨模块调用
     * @param string $action 要执行的函数 action名
     * @param array $args  给action传递的参数
     * @param array $extras 是否执行api action 对应控制器的init和shutdown类函数 比如array('exe_init'=>false,'exe_shutdown'=>false) 为init和shutdown类函数都不执行
     * @return mixed the function result, or FALSE on error
     * @access protected
     *
     */
    protected function executeAction($controller = '', $action = '', $args = array() , $extras = array(
        'exe_init' => false,
        'exe_shutdown' => false
    )) {
        if ($this->name === $controller) {
            $controller_obj = $this;
        }
        else {
            $name = ucfirst($controller) . '_Controller';
            $controller_obj = new $name(array(
                'name' => $controller,
                'auto_render' => false,
                'model_debug' => false,
                'view_debug' => false,
                'post_data' => null,
                'post_file' => null,
                'params_data' => null,
                'action' => $action,
                'exe_init' => $extras['exe_init'],
                'exe_shutdown' => $extras['exe_shutdown']
            ));
        }
        return call_user_func(array(&$controller_obj,
            $action
        ) , $args);
    }
    /**
     * 重定向函数
     *
     * @param string $path 要转向的url
     *
     */
    protected function redirect($path = '/') {
        ob_clean();
        header('Location:' . $path);
    }
    /**
     * 处理跳转提示
     @param	string $message  中转页显示的信息
     @param string $url   跳转到的url
     @param string $title  中转页标题
     @param int $pause  停顿几秒
     *
     * @access protected
     */
    protected function flash($message = '', $url = '/', $title = '', $pause = 3) {
        ob_clean();
        $this->view()->setTemplateDir('layout');
        $this->view()->setTemplateFile('flash');
        $this->view()->setValue('message', $message);
        $this->view()->setValue('title', $title);
        $this->view()->setValue('url', $url);
        $this->view()->setValue('pause', $pause);
        $this->view()->render();
        exit();
    }
    /**
     * 处理验证
     *	@param array $data 要验证的数据
     *
     * @return boolean 验证成功返回 true ，失败返回false
     * @access protected
     */
    protected function validate($data) {
        foreach ($this->model_objects as $model_obj) {
            $model_obj->runValidatation($data);
            $this->validate_error_mesages = array_merge($this->validate_error_mesages, $model_obj->getValidateMsg());
            $model_obj->resetValidatation();
        }
        if (count($this->validate_error_mesages) > 0) return false;
        return true;
    }
   /**
     * 魔术方法 考虑不存在的actions
     * @param string $method 方法
     * @param array $args 变量
     */
    function __call($method, $args) {
        throw new Exception('Controller::__call(): method ' . $method . ' of  ' . get_class($this) . ' dose not exists, exiting.');
    }
}
