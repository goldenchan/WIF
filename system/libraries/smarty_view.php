<?php
/**
 *  smarty view类文件
 * @file smarty_view.php
 * @package view
 * @author 陈金(wind.golden@gmail.com)
 */
/** 
 * smarty view类
 *
 */
class Smarty_View {
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
     * Constructor of the class
     *
     * @param templateName A template name
     * @param cachingEnabled default false.
     * If left as SMARTY_VIEW_CACHE_CHECK, the blog settings will checked to determine whether caching is enabled
     * or not.
     * @param layout the template layout ('admin' or 'front')
     * @param data Data that will be used to generate a unique id for the cached view (it will be ignored
     * if caching is not enabled)
     */
    function __construct($templateName, $layout, $cachingEnabled = false, $data = array()) {
        // whether caching is enabled or not
        $this->_cachingEnabled = $cachingEnabled;
        // name of the tepmlate
        $this->_templateName = $templateName;
        if ($this->_cachingEnabled) {
            // get a CachedTemplate object
            $this->_templateObject = new CachedTemplate($this->_templateName, $layout);
            // data used to calculate the view id
            $this->_data = !empty($data) ? $data : $_GET;
            $this->_viewId = $this->generateCacheId();
        }
        else {
            //require  dirname(__FILE__).DS.'template'.DS."/template.php" ;
            $this->_templateObject = new Template($this->_templateName, $layout);
        }
    }
    /**
     * set to debug mode for smarty
     * @param boolean $debug true or false
     */
    function setDebugMode($debug) {
        $this->_templateObject->setDebugMode($debug);
    }
    /**
     * 设置模版目录
     * @param string $dir 模版目录
     */
    function setTemplateDir($dir) {
        $this->_templateObject->setTemplateDir($dir);
    }
    /**
     * 设置模版文件
     * @param string $file 模版文件
     */
    function setTemplateFile($file) {
        $this->_templateObject->setTemplateFile($file);
    }
    /**
     *
     * 赋值 add by chenjin 20130424
     * @param array $_params 模版变量
     */
    function assignAll($_params) {
        $this->_params = $_params;
    }
    /**
     * returns true if the current view is cached or false if it is not or caching is disabled
     *
     * @return true if view enabled or false otherwise
     */
    function isCached() {
        if ($this->_cachingEnabled) {
            $isCached = $this->_templateObject->isCached($this->_viewId);
        }
        else $isCached = false;
        return $isCached;
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
    function getSmartyViewId() {
        return $this->_viewId;
    }
    /**
     * 获取缓存秒数
     */
    function getCacheTimeSeconds() {
        $cacheTime = $this->_httpCacheLifetime;
        if ($cacheTime == - 1) $cacheTime = 788400000; // a veeery long time!
        if ($cacheTime > 788400000) $cacheTime = 788400000;
        return ($cacheTime);
    }
    /**
     * Renders the view using the Smarty template object that we created in the constructor. This method
     * sends data to the client so it should be called as the last bit of code in our custom classes
     * extending SmartyView.
     *
     * It has no paramaters and returns nothing.
     */
    function render() {
        $sendOutput = true;
        // check if plog is configured to use conditional http headers and stuff like
        // that... Also, use the HttpCache class to determine whether we should send the
        // content or not
        if ($this->isCached()) {
            // some debug information
            $timestamp = $this->_templateObject->getCreationTimestamp();
            // and now send the correct headers
            if (Http_Cache::httpConditional($timestamp, $this->getCacheTimeSeconds())) $sendOutput = false;
            $header = "Last-Modified: " . gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
            header($header);
        }
        else {
            // send the results if needed
            $sendOutput = true;
        }
        if ($sendOutput) {
            // pass all the values to the template object
            $this->_templateObject->assign($this->_params);
            // and finally send them after calling the pre-processing method
            $content = $this->_templateObject->fetch($this->getSmartyViewId());
            print ($content);
        }
    }
    /**
     * Renders the view using the Smarty template object that we created in the constructor. This method
     * sends data to the client so it should be called as the last bit of code in our custom classes
     * extending SmartyView.
     * @return string fetched content
     *
     */
    function fetch() {
        // pass all the values to the template object
        $this->_templateObject->assign($this->_params);
        // and finally send them after calling the pre-processing method
        return $this->_templateObject->fetch($this->getSmartyViewId());
    }
}
