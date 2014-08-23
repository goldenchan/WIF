<?php
/**
 *  template类文件
 * @file template.php
 * @package template
 * @author 陈金(wind.golden@gmail.com)
 */
	
    /**
     *  Smarty dynamic block function
     *  @param string $param 模版变量
     *  @param string $content 模版变量值
     *  @param object $smarty smarty对象
     */
    
	function smarty_block_dynamic($param, $content, &$smarty) {
			return $content;
	}

    /**
     * template类
     * 
     * Wrapper around the Smarty class, inspired by the article
     * http://zend.com/zend/tut/tutorial-stump.php
     *
     * This class provides additional methods and initial values for the original Smarty
     * class, and reimplements the methods Smarty::fetch() and Smarty::display() so that they do 
     * not need an extra parameter.
     *
     * It is not recommended to create instances of this class directly but instead, use the factory
     * TemplateService which is able to generate different types of Template objects with some pre-set
     * values. The TemplateService class can also deal with cached and non-cached templates.
     *
     * @see TemplateService
     * @see CachedTemplate
     */

    class Template 
    {
        /**
         * 模版文件
         * @var string 
         */
        var $_templateFile;
        /**
         * smarty 对象
         * @var object
         */
        var $_smarty ;
        /**
         * 是否去掉空格
         * @var boolean
         */
		var $_trimwhitespace;

        /**
         * Constructor. 
         *
         * @param templateFile Complete path to the template file we are going to render
         */
        function __construct( $templateFile ,$templateDir='' )
        {
            // create the Smarty object and set the security values
           
            $this->_templateFile =  $templateFile;
			$this->template_dir  = $templateDir;
			$this->tpl_ext = WI_CONFIG::$smarty_tpl_ext;
			$this->_trimwhitespace = WI_CONFIG::$trim_white_space;

            // default folders
			$this->getSmarty()->compile_check = WI_CONFIG::$smarty_compile_check;
            $this->getSmarty()->setConfigDir(WI_CONFIG::$smarty_config_dir);
            $this->getSmarty()->setCompileDir(TMP.WI_CONFIG::$smarty_compile_dir);
			

			// register dynamic block for every template instance
			//$this->getSmarty()->registerPlugin('block', 'dynamic', 'smarty_block_dynamic', false);
        }
		
        /**
         * 获取smarty对象
         */
		function getSmarty(){
			if(!is_object($this->_smarty)){
				require_once ( __DIR__.'/baselib/Smarty/Smarty.class.php');
				$this->_smarty =  new Smarty();
			}
			return $this->_smarty;
		}
		

        /**
         * set to debug mode for smarty
         * @param bollean $debug true for false
         */ 
		function setDebugMode($debug){
			if($debug){
				$this->getSmarty()->force_compile =true;
				$this->getSmarty()->loadPlugin('Smarty_Internal_Debug');
				register_shutdown_function(array('Smarty_Internal_Debug','display_debug'),$this->_smarty);
			}
		}
		

        /**
         * assign($tpl_var, $value = null, $nocache = false)
         * hack smarty by chenjin 20130315
         * @param string $param 模版变量
         */
		function assign($param)
		{
			$this->getSmarty()->assign($param);
		}

        /**
         * By default templates are searched in the folder specified by the
         * template_folder configuration setting, but we can force Smarty to
         * look for those templates somewhere else. This method is obviously to be
         * used *before* rendering the template ;)
         *
         * @param string $templateFolder The new path where we'd like to search for templates
         * @return Returns always true.
         */
        function setTemplateDir( $templateDir )
        {
			$this->template_dir  = $templateDir;
            return true;
        }


        /**
         * 设置模版文件路径
         * @param $templateFile 模版文件路径
         */ 
		function setTemplateFile($templateFile)
		{
			$this->_templateFile = $templateFile;
		}

       /**
        * Returns the name of the template file
        *
	    * @return The name of the template file
	    *
	    * :TODO: 
	    * This code could do with some refactoring, its' pretty similar to what we've got in Template::_smarty_include()
       */
        function getTemplateFile()
        {			
            return $this->template_dir .DS.$this->_templateFile.$this->tpl_ext;
        }

		/**
		 * Load all the required smarty filters
		 */
		function loadFilters()
		{
			if( $this->_trimwhitespace)
				$this->getSmarty()->loadFilter( 'output', 'trimwhitespace' );
		}

        /**
         * Renders the template and returns the contents as an string
         *
         * @return The result as an string
         */
        function fetch()
        {
			$this->loadFilters();
            return $this->getSmarty()->fetch( $this->getTemplateFile());
        }

        /**
         * Displays the result of rendering the template
         *
         * @return I don't know :)
         */
        function display()
        {
			$this->loadFilters();	
            return $this->getSmarty()->display( $this->getTemplateFile());
        }

		/**
         * the Template object is by default not cached
         *
         * @param viewId Not used
         * @return always false
         */
        function isCached( $viewId )
        {
            return false;
        }
    }
