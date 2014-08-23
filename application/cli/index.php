#!/usr/bin/php
<?php
/**
 * cli入口文件
 * @file cli/index.php
 * @package controller 
 * @category  controller 
 * @exmaple 使用方法： "{PHP_PATH}/php {APP_ROOT_PATH}/cli/index.php {$controller_name} {$action_name}" 其中控制器名字没有controller后缀 
				例如 {PHP_PATH}/php {APP_ROOT_PATH}/cli/index.php note deamon
 * @author 陈金(wind.golden@gmail.com)
 */
/**
 * cli 目录文件分割符
 */
define('DS', DIRECTORY_SEPARATOR);
/**
 * cli app 路径
 */
define('APP_ROOT_PATH', dirname(__DIR__).DS);
/**
 * cli system 路径
 */
define('SYS_ROOT_PATH', dirname(APP_ROOT_PATH).DS.'system'.DS);
/**
 * cli 语言路径
 */
define('LANG_PATH', APP_ROOT_PATH."langs".DS);//Path to the language directory
/**
 * cli tmp 路径
 */
define('TMP',APP_ROOT_PATH."tmp".DS);//Path to the TMP directory
require SYS_ROOT_PATH."libraries".DS.'functions.php';
require dirname(APP_ROOT_PATH).DS.'configs'.DS.'app_config.php';
set_include_path(SYS_ROOT_PATH."libraries".PATH_SEPARATOR.SYS_ROOT_PATH."cores".PATH_SEPARATOR.APP_ROOT_PATH."controllers".PATH_SEPARATOR.APP_ROOT_PATH."models".PATH_SEPARATOR.APP_ROOT_PATH."helpers".PATH_SEPARATOR.SYS_ROOT_PATH."libraries".DS.'cache');
spl_autoload_extensions('.php');
spl_autoload_register('auto_load');
date_default_timezone_set('Asia/Shanghai');
if($_SERVER["argc"] !== 3 && $_SERVER["argc"] !== 4)
{
	echo "We exactly only accept 2 or 3 arguments!\n";
	exit(0);
}

if(in_array($_SERVER["argv"][1],array('newCtrler','newModel','newTpl','newAction','newRouter')))
{
    if(isset($_SERVER["argv"][3]))
        $_SERVER["argv"][1]($_SERVER["argv"][2],$_SERVER["argv"][3]);
    else
        $_SERVER["argv"][1]($_SERVER["argv"][2]);

    exit;
}

$_SERVER["argv"][1] .= "_Controller";


if(!class_exists($_SERVER["argv"][1]))
{
	echo "Class ".$_SERVER["argv"][1]." does not exsits!\n";
	exit(0);
}
$controller_class  = new $_SERVER["argv"][1](array('auto_render'=>false,'model_debug'=>false,'view_debug'=>false));
if(!method_exists($controller_class,$_SERVER["argv"][2]))
{
	echo "method ".$_SERVER["argv"][2]." in class ".$_SERVER["argv"][1]." does not exsits!\n";
	exit(0);
}
elseif(!is_callable(array($controller_class,$_SERVER["argv"][2])))
{
	echo "method ".$_SERVER["argv"][2]." in class ".$_SERVER["argv"][1]." could not be called!\n";
	exit(0);
}
call_user_func( array($controller_class,$_SERVER["argv"][2]), isset($_SERVER["argv"][3])?$_SERVER["argv"][3]:null );


/**同时创建Action和Template
 ** TODO  修改config.php中的路由映射
 **/
function newRouter($controller_name,$action )
{
    $theme = WI_CONFIG::$default_template_theme;

    $controller_file = APP_ROOT_PATH.DS.'controllers'.DS.strtolower($controller_name).'_controller.php';
    $tpl_file = APP_ROOT_PATH.DS.'templates'.DS.$theme.DS.strtolower($controller_name).DS.strtolower($action).'.tpl';

        
    create_dir(APP_ROOT_PATH.DS.'templates'.DS.$theme.DS.$controller_name);

    if(!file_exists($tpl_file))//new template
        newTpl(strtolower($controller_name).'/'.strtolower($action));

    newAction($controller_name,$action);//new action

    //new router array*/
    //include_once APP_ROOT_PATH.DS."configs".DS."config.php";
    //var_dump(get_defined_vars());
}

//新建action
function newAction($controller,$action)
{
    $controller_file = APP_ROOT_PATH.DS.'controllers'.DS.strtolower($controller).'_controller.php';
    
    if(!file_exists($controller_file))
    {
        newCtrler($controller,$action);
    }
    else
    {
        if(strpos(file_get_contents($controller_file),'function '.$action) !== false)
        {
            echo "action ".$action." already exists\n";
        }
        else
        {
            $fh = fopen($controller_file, 'r+') or die("can't open file");

            $stat = fstat($fh);
            ftruncate($fh, $stat['size']-1);
            fclose($fh); 
            $content = 'function '.$action.'(){//action 

        }
        
    }';
        append_file($controller_file, $content);
        }
    }
}


//新建控制器(action 可有可无)
function newCtrler($controller_name='default',$action=null)
{
    if(strtolower($controller_name) == 'app')
        exit("invalid controller name!\n");

    $content='<?php
    /**
    * '.strtolower($controller_name).'控制器文件
    * @file application/controllers/'.strtolower($controller_name).'_controller.php
    * @author Developer(developer@gmail.com)
    * @package controller
    * @date '.date('Y-m-d H:i:s').'
     */	
    /**
    * '.strtolower($controller_name).'控制器 
    */
    class '.ucfirst($controller_name).'_Controller extends App_Controller { 
    '
        .($action !==null ? 'function '.$action.'(){//action 

    }':'').'



}';
    write_file(APP_ROOT_PATH.DS.'controllers'.DS.strtolower($controller_name).'_controller.php', $content,0777);
}

//新建Model
function newModel($model_name="users")
{
    if(strtolower($model_name) == 'app')
        exit("invalid model name!\n");
    $classname = word_camelcase($model_name);
    $content='<?php
/**
 * '.$classname.'Model封装文件
 * @file application/models/'.strtolower($model_name).'_model.php
 * @author Developer(developer@gmail.com)
 * @date '.date('Y-m-d H:i:s').'
 */
/**
 * '.$classname.'Model封装
 */
 Class '.$classname.'_Model extends App_Model
{
	/**
     * 无前缀的表名
     * @var string
     */
    var $table = "company_user";
    
    /**
     * 主键字段
     * @var string
     */
	var $primary_key;
    
    /**
     * 正则验证 键为 控制器方法validate($data)的$data中的键
     * @var array
     */
	var $validate_preg=array();
    
    /**
     * db验证 键为 控制器方法validate($data)的$data中的键
     * @var array
     */
	var $validate_db = array();
}';


if(file_exists(APP_ROOT_PATH.DS.'models'.DS.strtolower($model_name).'_model.php'))
    exit("model file ".strtolower($model_name)."_model.php already exists!\n");
    write_file(APP_ROOT_PATH.DS.'models'.DS.strtolower($model_name).'_model.php', $content,0777);
}

//新建一个空的模版
function newTpl($tpl_data='default/index')
{
    $theme = WI_CONFIG::$default_template_theme;
    $tmps = explode('/',$tpl_data);
    
    if(count($tmps) !==2)
        exit("incorrect template data \n");
    
    $content='{**
* controller:'.strtolower($tmps[0]).' action:'.strtolower($tmps[1]).'
* @file application/templates/'.$theme.DS.strtolower($tmps[0]).'/'.strtolower($tmps[1]).'.tpl
* @author Developer(developer@gmail.com)
* @date '.date('Y-m-d H:i:s').'
*/		
**}
{extends file="../layout/base.tpl"}
{block name=keywords} keywords {/block}
{block name=description} description {/block}
{block name=title}page title {/block}
{block name=head}{/block}
{block name="body"}

{/block} ';
if(file_exists(APP_ROOT_PATH.DS.'templates'.DS.$theme.DS.strtolower($tmps[0]).DS.strtolower($tmps[1]).'.tpl'))
    exit("template file ".strtolower($tmps[0]).DS.strtolower($tmps[1]).".tpl already exists!\n");

create_dir(APP_ROOT_PATH.DS.'templates'.DS.$theme.DS.$tmps[0]);
write_file(APP_ROOT_PATH.DS.'templates'.DS.$theme.DS.strtolower($tmps[0]).DS.strtolower($tmps[1]).'.tpl', $content,0777);
}
