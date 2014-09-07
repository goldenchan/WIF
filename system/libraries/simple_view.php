<?php
/**
 *  simple view类文件
 * @file simple_view.php
 * @package view
 * @author 陈金(wind.golden@gmail.com)
 */
/** 
 * simple view类
 */
class Simple_View {
    /**
     * 是否启用缓存
     * @var boolean
     */
    var $_cachingEnabled;
    /**
     * 模版文件名
     * @var string
     */
    var $_templateName;
    /**
     * 模版文件名
     * @var string
     */
    var $_templateLayout;
    /**
     * 模版对象
     * @var object
     */
    var $_templateObject;
    /**
     * 用户缓存的数据
     * @var array
     */
    var $_data;
    /**
     * 模板变量
     * @var array
     */
    var $_params = array();
    /**
     * view id
     * @var string
     */
    var $_viewId;
    /**
     * 缓存周期
     * @var integer
     */
    var $_httpCacheLifetime = 1800;
    /**
     * debug 开关
     * @var boolean
     */
    var $_debug = false;
    /**
     * module
     * @var string
     */
    var $_module ;
    /**
     * Constructor of the class
     *
     * @param $templateName A template name
     * @param $cachingEnabled default false.
     * @param $layout the template layout ('admin' or 'front')
     * @param $data Data that will be used to generate a unique id for the cached view (it will be ignored
     * if caching is not enabled)
     */
    function __construct($templateName, $layout, $module = 'default',$cachingEnabled = false, $data = array()) {
        // name of the tepmlate
        $this->_templateName = $templateName;
        $this->_templateLayout = $layout;
        $this->_module = $module;
    }
    /**
     * set to debug mode for smarty
     * @param boolean $debug true or false
     */
    function setDebugMode($debug) {
        return true;
    }
    /**
     * 设置模版目录
     * @param string $dir 模版目录
     */
    function setTemplateDir($dir) {
        $this->_templateLayout = $dir;
    }
    /**
     * 设置模版文件
     * @param string $file 模版文件
     */
    function setTemplateFile($file) {
         $this->_templateName = $file;
    }
    /**
     *
     * 赋值 add by chenjin 20130424
     * @param array $_params 模版变量
     */
    function assignAll($_params) {
        $this->_params = $_params;
        return $this;
    }
    /**
     * check cache is enabled
     */
    function isCached() {
        return $this->_cachingEnabled;
    }
    /**
     * generates a unique identifier for this view. The cache identifier is generated
     * based on the last parameter passed to the view constructor
     *
     * @param returns a unique id for this view
     * @return string view id
     */
    function generateCacheId() {
        $viewId = "";
        foreach ($this->_data as $key => $value) $viewId.= "$key=$value";
        $viewId = md5($viewId);
        return $viewId;
    }
    /**
     * returns this view's id
     *
     * @return this view's id
     */
    function getViewId() {
        return true;
    }
    /**
     * incude其他模板 只能在当前module下
     * @param string $tpl_relative_path 同一module下面的不带后缀的路径 比如 $this->includeTpl('layout/menu');
     */
    public function includeTpl($tpl_relative_path){
        extract($this->_params, EXTR_PREFIX_SAME, "wddx");
        include APP_ROOT_PATH . "templates" . DS .$this->_module.DS.$tpl_relative_path.WI_CONFIG::$tpl_ext;
    }

    /**
     * Renders the view using the Smarty template object that we created in the constructor. This method
     * sends data to the client so it should be called as the last bit of code in our custom classes
     * extending SmartyView.
     *
     * It has no paramaters and returns nothing.
     */
    function render() {
        extract($this->_params, EXTR_PREFIX_SAME, "wddx");
        include APP_ROOT_PATH . "templates" . DS .$this->_module.DS.$this->_templateLayout.DS.$this->_templateName.WI_CONFIG::$tpl_ext;
    }
    /**
     * Renders the view using the Smarty template object that we created in the constructor. This method
     * sends data to the client so it should be called as the last bit of code in our custom classes
     * extending SmartyView.
     * @return boolean
     *
     */
    function fetch() {
       return true; 
    }
}
