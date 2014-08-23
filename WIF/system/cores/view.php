<?php
/** 
* View基础类
* @Copyright, Chenjin, 2008, All Rights Reserved.
* @package view
* 
* @author: Chenjin (wind.golden@gmail.com) 
*
*
*/

/** 
 * 
 * Base class with basic methods related to views. This class should not be used directly. 
 *
 * This base View class does not know anything about cached views either.
 */
	class View
    {
        /**
         * 所有模版变量数组
         * @var array
         */
        var $_params;
        
        /**
         * 内容格式 
         * @var string
         * @access private
         */
		var $_contentType;
        
        /**
         * 头部信息
         * @var string
         * @access private
         */
        var $_headers;

        /**
         * 字符集
         * @var string
         * @access private
         */
        var $_charset;

        /**
         * 模版文件名
         * @var string
         *  @access private
         */  
		var $_templateName;

        /**
         * layout 目录 一般控制器名
         * @var string
         * @access private
         */ 
        var $_layout;
        
        /**
         * 主题名
         * @var string
        * @access private
         */ 
        var $_theme;
        
        /**
         * 缓存开关
         * @var boolean
         * @access private
         */
        var $_cachingEnabled;
        
        /**
         * 缓存数据
         * @var array
         * @access private
         */
        var $_cacheData;
        
        /**
         * view 对象
         * @var object
         * @access private
         */
        var $_viewObject;
        
        /**
         * 模版对象
         * @var object
         * @access private
         */
        var $_template;
        
       /**
        * 默认模版名
        * @const string 
        * @access private
        */ 
        const _default_template = 'smarty';

        /**
        * 可支持模版名
        * @var array 
        * @access private
        */ 
        var $_templates_support = array('smarty'); 

        /**
        * 跨域
        * @var boolean 
        * @access private
        */ 
		var $_crossDomain=false;

        /**
         * Constructor. Initializes the view with a default content type, character set, etc.
         *
         * @param string $contentType 输出内容类型
         * @param string $templateName 模版名
         * @param string $layout 模版所在目录
         * @param string $theme 主题目录（目录结构: $theme/$layout/$templateName）
         * @param boolean $cachingEnabled 是否启用缓存
         * @param array $cacheData  缓存数据
         */
		function __construct($contentType = "text/html", $templateName = '', $layout = '', $theme = '', $cachingEnabled = false, $cacheData = Array())
        {	
			// set a default content type and character set for responses
            $this->_contentType = $contentType;
            $this->_theme = $theme;
			$this->_templateName = $templateName;
			$this->_layout = $layout;
			$this->_cachingEnabled = $cachingEnabled;
			$this->_cacheData = $cacheData;
			$this->_charset = 'utf-8';
			$this->_headers = Array();
			
			// let's send an HTTP 200 header response... If somebody wants to overwrite it later
			// on, php should keep in mind that the valid one will be the last one so it is
			// fine to do this more than once and twice
			$this->addHeaderResponse( "HTTP/1.0 200 OK" );
        }

        /**
         *
         * 获取模板对象
         *
         *
         */
		public function getTemplate()
		{
			if(!is_object($this->_template))
			{
				if(self::_default_template === 'smarty')
				{
					$this->_template  = new Smarty_View($this->_templateName,APP_ROOT_PATH."templates".DS.$this->_theme.DS.$this->_layout,$this->_cachingEnabled,$this->_cacheData);
				}
			}
			return $this->_template;
		}


        /**
         *
         * debug开关
         *
         * @param boolean $debug 调试开关 
         *
         */
		public function setDebugMode($debug)
		{
			$this->getTemplate()->setDebugMode($debug);
		}
		

        /**
         *
         * 设置模版目录
         *
         * @param string $dir 模版目录
         */ 
		public function setTemplateDir($dir)
		{
			$this->getTemplate()->setTemplateDir(APP_ROOT_PATH."templates".DS.$this->_theme.DS.$dir );
		}


        /**
         *
         * 设置模版文件
         *
         * @param string $file 文件路径
         *
         */
		public function setTemplateFile($file)
		{
			$this->getTemplate()->setTemplateFile($file);
		}

        /**
         *   模版变量赋值   
         *       
         * @param string $name 模版变量名
         * @param array $value 模版变量值
         */
         public function setValue( $name, $value )
         {
            $this->_params[$name] = $value;
         }

         /**
          *  取模版变量的值
          *
          * @param string $name 模版变量名
          * @return array 
          */
         public function getValue( $name )
         {
         	return $this->_params[$name];
         }
		 
		 /**
          * 设置输出内容类型 
          *
		  * @param string $contentType The new content type
		  */
		public function setContentType( $contentType )
		{
			$this->_contentType = $contentType;
		}
		
		/**
         * 设置输出字符集
         *	
		 * @param string $charset the character set
		 */
		public function setCharset( $charset )
		{
			$this->_charset = $charset;
		}
		
		/**
         * 添加一个新的头部信息
         * 	
         * @param string $headerString 头部信息串
		 * @return true
		 */
		public function addHeaderResponse( $headerString )
		{
			array_push( $this->_headers, $headerString );
			return true;
		}
		
		/**
         * 用一组新的头部信息来替代旧的头部信息
		 *
		 * @param array $headers 头部信息串数组
		 * @return  boolean true
		 * @see addHeaderResponse
		 */
		public function setHeaders( $headers = Array())
		{
			$this->_headers = $headers;
		}
		
		/**
         * 根据头部信息来设置http头
		 *
		 * @return boolean true
		 */
		public function sendContentType()
		{
			// build up the header and send it
			$header = "Content-Type: ".$this->_contentType.";charset=".$this->_charset;
			header( $header );
			return true;
		}
		
        /**
         *
         * 设置错误提示信息
         *@deprecated
         *
         * @param string $message 错误提示
		 * @return boolean true
		 */
		public function setErrorMessage( $message )
		{
			$this->setValue( "viewErrorMessage", $message );
			$this->setError( true );
			
			return true;
		}
		
		/**
		 * Whether the view has to show some error message or not. Views can
		 * show success messages as well as error messages at the same time.
         *
         * @deprecated
		 * @param boolean $error true or false
		 * @return boolean true 
		 */
		public function setError( $error = true )
		{
			$this->setValue( "viewIsError", $error );
			return true;
		}
		
		/**
		 * Whether the view has to show some success message or not. Views can show
		 * sucess messages as well as error messages as the same time!
		 * @deprecated
		 * @param boolean $success true or false
		 * @return boolean true
		 */
		public function setSuccess( $success = true )
		{
			$this->setValue( "viewIsSuccess", $success );
			return true;
		}
		
		/**
		 * sets an success message for the whole form, should that be needed
		 * @deprecated
		 * @param string $message true or false
		 * @return boolean true
		 */
		public function setSuccessMessage( $message )
		{
			$this->setValue( "viewSuccessMessage", $message );
			$this->setSuccess( true );			
			
			return true;
		}
		
		/**
		 * stores a value in the session, associated to one key, in case
		 * the view wants to keep some value for later use such as filter settings
		 * for persisten listings, etc.
		 * @deprecated
		 * @param string $param session变量名
		 * @param string $value session变量值
		 * @return boolean true
		 */
		public function setSessionValue( $param, $value )
		{
            // if there is no session data, there's nothing for us to set
            if( !is_array( $_SESSION )) 
                return false;		
		
			$viewName = get_class( $this );
			$keyName = "{$viewName}_{$param}";
			$_SESSION["$keyName"] = $value;
			
			return true;
		}
		
		/** 
		 * retrieves a parameter from the session
		 * @deprecated
		 * @param string $param session变量名
		 * @param string $defaultValue session变量值
		 * @return array  The value associated to the parameter or empty if not
		 * found
		 */
        public function getSessionValue( $param, $defaultValue = "" )
        {
            // if there is no session data, there's nothing for us to look for
            if( !is_array( $_SESSION )) 
                return false;
            
			$viewName = get_class( $this );
            $keyName = "{$viewName}_{$param}";

            if(isset($_SESSION[$keyName]) && !empty($_SESSION[$keyName]) ){
                return $_SESSION[$keyName];
            } else{
                return $defaultValue;
            }
        }
		

        /**
         *
         * fetch模版所有变量.
         * 
         * @param string $template_file 模版文件
         *
         * @return string 模版输出代码
         */
		public function fetch($template_file = '')
		{
			if($this->_contentType === "text/html"){
				//添加对自定义模板文件的支持 added by chenjin 20130613
				if($template_file !== '' && strpos($template_file,'/') !== false)
				{
					$tmp = explode('/',$template_file);
					$this->setTemplateDir($tmp[0]);
					$this->setTemplateFile($tmp[1]);
				}

				// pass all the values to the template object
				$this->getTemplate()->assignAll( $this->_params);			
		
				// and finally send them after calling the pre-processing method
				return  $this->getTemplate()->fetch( $this->getTemplate()->getSmartyViewId());
			}
			else {
				$p = array_values($this->_params);
				return $this->specialFetch($p[0]);
			}

			
		}
        	

        /**
         * Renders the view. 默认不需要传递模版文件相对路径（$dir/$tpl_file格式）参数，会根据控制器和Action名去找文件
         *
         * @param string $template_file 指定的模版文件(controller/action)
         *
         */
        public function render($template_file = '')
        {
            // send the headers we've been assigned if any, alognside the conten-type header
            foreach( $this->_headers as $header )
			{
				header( $header );
			}

            $this->sendContentType();  
			if($this->_contentType === "text/html"){
				//添加对自定义模板文件的支持 added by chenjin 20130613
				if($template_file !== '' && strpos($template_file,'/') !== false)
				{
					$tmp = explode('/',$template_file);
					$this->setTemplateDir($tmp[0]);
					$this->setTemplateFile($tmp[1]);
				}

				$this->getTemplate()->assignAll($this->_params);
				$this->getTemplate()->render();
			}
            elseif(isset($this->_params)) {
				$p = array_values($this->_params);
				echo $this->specialFetch($p[0]);
			}
        }

		/**
		* 输出其他格式请求的响应  text/plainformat string 输出的格式 json or xml or plain

		*@param array $content 要输出的内容，(json内容格式可为(string,array); xml内容格式为(xml结构的string));plain,html,javascript内容格式为(string) 
		
        *@return boolean 条件不符合时返回false,无输出 否则输出响应
		*/
		private function specialFetch($content){
			if(count($content) === 0){
				return '';
			}

			if($this->_contentType === 'text/plain'){
				$content_for_json = json_encode($content);
				$output = ($this->_crossDomain && isset($_GET['jsoncallback'])) ? $_GET['jsoncallback']. "($content_for_json)" : $content_for_json;
			}
			else if($this->_contentType === 'text/xml'){
				$content_for_xml = array2xml($content);
				$output = '<'.'?xml version="1.0" encoding="'.$this->_charset.'" ?'.'>'."\n";
				$output .= '<response>'.$content_for_xml.'</response>';
			}
			return $output;
		}

		/**
        * GET中增加 etag 和 lastModified 信息
		* @param string $etag eTag信息
        * @param string $lastModified lastModified信息
		*/
		public function do_conditional_get($etag, $lastModified)
		{
			header("Last-Modified: $lastModified");
			header("ETag: \"{$etag}\"");
				
			$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
				stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : 
				false;
			
			$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
				stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
				false;
			
			if (!$if_modified_since && !$if_none_match)
				return;
			
			if ($if_none_match && $if_none_match != $etag && $if_none_match != '"' . $etag . '"')
				return; // etag is there but doesn't match
			
			if ($if_modified_since && $if_modified_since != $lastModified)
				return; // if-modified-since is there but doesn't match
			
			// Nothing has changed since their last request - serve a 304 and exit
			header('HTTP/1.1 304 Not Modified');
			exit();
        }
    }

