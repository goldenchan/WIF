<?php 
/**
 * 抽象控制器类
 * @package controller
 * @author		wind.golden@gmail.com(陈金)
 */

/** 
 *
 * 基础控制器 Base class with basic methods related to controllers. This class should not be used directly.
 *
 */
abstract class Controller {
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
     * api方法名列表 （js调用）
     * @var array 
     */
	protected $api_actions = array();

    /**
     * 不检查模板是否存在的方法名
     * @var array
     */
	protected $actions_without_template = array();
	
    /**
     * 无后缀model类名
     * @var array
     */
	protected $models = array();
	
    /**
     * 实例化后的model对象
     * @var array
     */
	protected $model_objects = array();

    /**
     * 无后缀helper类名
     * @var array
     */
	protected $helpers = array();

    /**
     * 实例化后的helper对象
     * @var array
     */
	protected $helper_objects = array();
	
    /**
     * 模版主题
     * @var string
     */
    protected $tpl_theme= '';
    
    /**
     * 模板目录
     * @var string
     */
	protected $tpl_path= '';

    /**
     * 模板文件名
     * @var string
     */
	protected $tpl_file= '';

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
     * view debug时候是否强制编译
     * @var boolean
     */
    protected $force_complie_in_debug = true;
    
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
    public function __construct($reset_properties=null){
 
	//没有controller 数据则从Router中获取
	if ($this->name  === null){
		$this->name = Router::$controller;
	}

	//action数据从Router中获取
	$this->action = Router::$action;

        //router_params数据从Router中取
        $this->router_params = Router::$params;

        ini_set('magic_quotes_runtime', 0);
        if( isset($_POST)){
            ini_get('magic_quotes_gpc') && $_POST = stripslashes_deep($_POST);//$_POST处理
            $this->post_data = $_POST;
        }

        if( isset($_GET) ){
            ini_get('magic_quotes_gpc') && $_GET = stripslashes_deep($_GET);//$_GET处理
            $this->params_data = $_GET;
        }

        if( isset($_FILES) ){
            $this->post_files = $_FILES; //$_FILES处理
        }

        //初始化模版主题
        $this->tpl_theme = WI_CONFIG::$default_template_theme;

        //可以重置任意属性 目前只给cli模式和api方式调用
		if(is_array($reset_properties)){
			foreach($reset_properties as $property => $value){
				$this->$property = $value;
			}
		}

        //初始化Session类    
        $this->session = new Session(WI_CONFIG::$session_storage);
        
        //是否自动render
        //$this->auto_render && register_shutdown_function(array($this->view(), 'render'));
 
        /* 初始化控制器时,自动执行控制器中以init开头的所有方法(注意init开头的方法均无参数),
        ** 控制器action结束时,自动执行所有以shutdown开头的所有方法(注意shutdown开头的方法均无参数).
        */
        //当把控制器的action作为api 在另一个控制器中调用时禁止init 和 shutdown
        /*foreach(get_class_methods($this) as $method){
                if( $this->exe_init === true)
                    substr($method,0,4)==='init' && $this->$method();
                if($this->exe_shutdown === true)
                    substr($method,0,8) === 'shutdown' && register_shutdown_function(array($this, $method));
        }*/
      
	}

    /**
	 *  加载model类
	 * @param string $model_name 要加载的无后缀model类名
	 * @return object
	 * @access protected 
	 */
    public function model($model_name)
    {
        if(!isset($this->model_objects[$model_name]))
        {
            //include_once APP_ROOT_PATH."models".DS.word_underscore($model_name).'_model.php';
            $model_class_name = ucfirst($model_name).'_Model';
            $model_obj = new $model_class_name();
            $model_obj->setModelCache($this->model_cache_type);
            $model_obj->setDebugMode($this->model_debug);
            $this->model_objects[$model_name] = $model_obj;	
            is_object($model_obj) && register_shutdown_function(array($model_obj, 'showDbDebug'));
        }
        return $this->model_objects[$model_name];
    }
    

    /**
	 *  加载helper类
	 * @param string $helpers 要加载的无后缀helper类
	 * @return object
	 * @access public 
     */
    public function helper($helper_name)
    {
        $this->setIncludePath();
        if(!isset($this->help_objects[$helper_name]))
        {
            $helper_class_name = ucfirst($helper1).'_Helper';
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
	public function view($reload = false){
		if(!is_object($this->_view) || $reload){	
            if($this->content_type === 'text/html' ){
				if(empty($this->tpl_path) && $this->name !== ''){
					$this->tpl_path = $this->name;
				}
				if(empty($this->tpl_file) && $this->action !== ''){
					$this->tpl_file  = $this->action;
                } 

				$view = new View('text/html',$this->tpl_file,$this->tpl_path,$this->tpl_theme,$this->view_cache,$this->data_for_view_cache);
                //$view->setDebugMode($this->view_debug,$this->force_complie_in_debug);            
			}
			else if(in_array($this->content_type,array('text/plain','text/xml'))){	
				$view = new View($this->content_type);
				$view->addHeaderResponse("Expires: Sun, 17 Mar 2013 05:00:00 GMT");
				$view->addHeaderResponse("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				$view->addHeaderResponse("Cache-Control: no-store, no-cache, must-revalidate");
				$view->addHeaderResponse("Cache-Control: post-check=0, pre-check=0");
				$view->addHeaderResponse("Pragma: no-cache");
				$view->crossDomain = $this->cross_domain;
			}	
			else{
				throw new Exception('Error content type');
			}
			$this->_view = $view;
		}
		return $this->_view;
	}

	
	/**
	 *  设置view对象输出内容类型
	 * @param $message string  输出类型 "text/html" or "text/plain" or "text/xml"
	 *
	 * @access protected 
	 */
	protected function setContentType($type){
		$this->content_type = $type;
	}
	
	/**
     * 请求输出另外一个控制器的action的模版
     *
     * @deprecated 
	 * @param string  $controller  The new Controller name
	 * @param string $action The new action name to be redirected to
	 * @param array  Any parameters passed to this method will be passed as
	 *               parameters to the new action.
	 * @return mixed the function result, or FALSE on error
	 * @access protected
	 */
	 protected function requestAction($controller='' , $action='',$args = array()) {
			if($this->name===$controller){
                $controller_obj = $this;
                $controller_obj->action = $action;
			}
			else{
				$name = $controller.'Controller';
				$controller_obj = new $name(array('name'=>$controller,'action'=>$action));
			}
            $controller_obj->post_data = null;
            $controller_obj->params_data = null;
            $controller_obj->router_params = $args;

			ob_clean();
			call_user_func(array(&$controller_obj, $action),$args);
			exit(0);
	}

	
    /**
     * 用api 的方式 执行action 忽略掉GET POST FILE 的参数
     * @param string  $controller  控制器名
     * @param string $action 要执行的函数 action名
     * @param array $args  给action传递的参数
     * @param array $extras 是否执行api action 对应控制器的init和shutdown类函数 比如array('exe_init'=>false,'exe_shutdown'=>false) 为init和shutdown类函数都不执行
	 * @return mixed the function result, or FALSE on error
	 * @access protected
     *
     */
	protected function executeAction($controller='' , $action='',$args = array(),$extras=array('exe_init'=>false,'exe_shutdown'=>false)) {
			if($this->name===$controller){
                $controller_obj = $this;
			}
			else{
				$name = ucfirst($controller).'_Controller';
				$controller_obj = new $name(array('name'=>$controller,
				                                  'auto_render'=>false,
												  'model_debug'=>false,
												  'view_debug'=>false,
                                                  'post_data'=>null,
                                                  'post_file'=>null,
												  'params_data'=>null,
                                                  'action'=>$action,
                                                  'exe_init'=>$extras['exe_init'],
                                                  'exe_shutdown'=>$extras['exe_shutdown']));
            }
            return  call_user_func(array(&$controller_obj, $action),$args);
    }
    
    /**
     * 重定向函数
     *
     * @param string $path 要转向的url
     * 
	 */
    protected function redirect($path='/')
	{
	    ob_clean();
		header('Location:'.$path);
    }

	/**
	 * Tells the browser not to cache the results of the current request by sending headers
	 *
	 * @access protected
	 */
	protected function disableHttpCache() {
		$this->view()->addHeaderResponse("Expires: Sun, 17 Mar 2013 05:00:00 GMT");
		$this->view()->addHeaderResponse("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		$this->view()->addHeaderResponse("Cache-Control: no-store, no-cache, must-revalidate");
		$this->view()->addHeaderResponse("Cache-Control: post-check=0, pre-check=0");
        $this->view()->addHeaderResponse("Pragma: no-cache");
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
	protected function flash( $message= '',$url= '/', $title='', $pause = 3)
	{
		ob_clean();
		$this->view()->setTemplateDir('layout');
		$this->view()->setTemplateFile('flash');
		$this->view()->setValue('message',$message);
		$this->view()->setValue('title',$title);
		$this->view()->setValue('url',$url);
		$this->view()->setValue('pause',$pause);
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
	protected function validate($data)
    {
		foreach($this->model_objects as $model_obj)
		{
			$model_obj->runValidatation($data);
            $this->validate_error_mesages = array_merge($this->validate_error_mesages,$model_obj->getValidateMsg());
            $model_obj->resetValidatation();
        }
		if(count($this->validate_error_mesages) > 0)
			return false;

		return true;
	}

	
    /**
     * 魔术方法 考虑不存在的actions
     */
	function __call($method, $args){
		throw new Exception('Controller::__call(): method '.$method.' of  '.get_class($this).' dose not exists, exiting.');
	}
}
