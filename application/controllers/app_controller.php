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
    var $_appview = null;
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
    function checkLogin(){
		return is_array($this->session->read('user_info'));
    }

    /**
     * 获取当前用户资料
	 * @param $prop 用户属性
	 * @return string|aray 
			string 如果用户属性存在，返回对应属性值
			array 属性未赋值 返回当前用户的信息数组
	*/
	function me($prop = '')
	{
		$current_user_info = $this->session->read('user_info');
		if($prop !== '' && isset($current_user_info[$prop]))
		{
			return $current_user_info[$prop];
		}
		else
			return $current_user_info;
	}

    /**
     * view对象 重载
     * @param $reload 是否重载view对象属性
     * @see Controller:view();
     */
    public function view($reload = false)
    {
		if(!is_object($this->_appview))
        {
            if($this->content_type === 'text/html')
            {
                //$this->disableHttpCache();
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: Sun, 17 Mar 2013 05:00:00 GMT');
                if($this->pre_load_view_params)
                {
                    $is_login = $this->checkLogin();
                    parent::view()->setValue('is_login',$is_login);//是否登录       
                    $is_login && parent::view()->setValue('me',$this->me());//当前用户信息
                    
                    parent::view()->setValue('c_props',array('action'=>$this->action,'name'=>$this->name));
                    //parent::view()->setValue('c_props',my_get_object_vars($this));//当前控制器的所有public属性数组
                }
            }
			$this->_appview = parent::view();
		}
		return $this->_appview;
    }
    

    /**
     * 404跳转
     */
    public function page404()
    {
	$this->view()->render('default/page404');
	exit;
    }


    /**
     * 将特殊的二维数组转成一维 
     * @example 
        (1) 将 array( array('note_id'=>20),array('note_id'=>23) ) 转成 array(20,23);
        (2) 将 array( array('note_id'=>20,'count'=>2),array('note_id'=>23,'count'=>4) ) $key_field = 'note_id',$value_field='count'  转成 array(20=>2,23=>4) or array(2=>20,4=>23);
    * @param array $array 要处理的二维数组 形如 array( array('note_id'=>20),array('note_id'=>23) ) 
    * @param string $key_field    在二位数组中键为$key_field 的值被放到新数组的key 
    * @param string $value_field  在二位数组中键为$value_field的值被放到新数组的value
    * @return array 新的一维数组
    */
    function two2one($array,$key_field=null,$value_field=null)
    {
        if(count($array) == 0 || !is_array(end($array))  )
            return array();

        $new_array = array();
        if(count(end($array)) == 1)
        {
            foreach($array as $v)
            {
                $new_array = array_merge($new_array,array_values($v));
            }
        }elseif($key_field != null && $value_field != null)
        {
            foreach($array as $k => $v)
            {
                 $new_array[$key_field === '__int__' ? $k : $v[$key_field]] = $v[$value_field];
            }
        }
        return $new_array;
    }


    /**
     * GET数组转换成字符串 拼装分页器中的url用到
	 * @param array $params_data 通常是GET数组（$this->params_data）
     * @param array $excluded_keys 需要排除的GET数组键
     * @return string  通常是url串的后半部分
	*/
    function array2Params($params_data,$excluded_keys = array('page'))
    {
		if(!is_array($params_data))
			return '';
		$params = array();
		foreach($params_data as $param => $value)
		{
			if(is_array($value))
                $value = end($value);
            if(!in_array($param,$excluded_keys))
			    $params[] = $param .'='.$value;
		}
		return implode('&',$params);
    }

    /**
     * GET数组转换成查询条件数组 可以用于form get提交后的转换
     * @param array $params_data 通常是GET数组（$this->params_data）
     * @param array $fields  数据库表的有效字段数组（例如 user_name tag_id）
	 * @return array 安全的查询条件数组
     */
    function params2condition($params_data,$fields=array())
    {
        if(!is_array($params_data) || count($fields) === 0)
            return array();

        $condition = array();
        foreach($params_data as $param => $value)
        {
            if($value === '')
                continue;
            if(in_array($param,$fields) &&( strpos($param,'content') !== false || strpos($param,'name') !== false || strpos($param,'title') !== false))
            {//如果是字符串，通过$params_data['exact_query'] 是否存在 决定是否执行模糊查询 
                    $condition[$param] = isset($params_data['exact_query'])&& $params_data['exact_query']== false ? array('like'=>'%'.$value.'%') : $value;
            }
            elseif(strpos($param,'date') !== false || strpos($param,'count') !== false || strpos($param,'amount') !== false || strpos($param,'num') !== false)
            {
                if(strpos($param,'date') !== false)
                {
                    $value = strtotime($value);
                }
                if(strpos($param,'min') !== false)
                {
                     //如果param 是 min_create_date 或 create_date_min
                    $field = trim(trim($param,'min'),'_');
                    if(in_array($field,$fields))
                    {
                        $condition[$field]['>='] = $value;
                    }
                }
                elseif(strpos($param,'max') !== false)
                {
                    //如果param 是 max_create_date 或 create_date_max
                    $field = trim(trim($param,'max'),'_');
                    if(in_array($field,$fields))
                    {
                        $condition[$field]['<='] = $value;
                    }
                }
                elseif(in_array($param,$fields))
                    $condition[$param] = $value;
            }
            elseif(in_array($param,$fields))
                $condition[$param] = $value;
        }
        return $condition;  
    }

    
    /**
     *
     *  获取调用一些rop api需要的 session
     *
     * @return string rop接口需要的session
     */
    function getRopSession()
    {
        return  $this->session->read('rop_token');
    }
    
    /**
     *
     * 处理rop接口执行的错误
     * @param string              $json json串 
     * @return int|array|string   如果session失效，则返回403，
                                如果有错误信息，则返回错误code和错误message
                                 如果没有错误，则返回原json字符串    
     */
    function handleRopError($json)
    {
        $array = json_decode($json,true);
        if(isset($array['subErrors']) && count($array['subErrors']) === 1)
        {
            if(isset($array['subErrors'][0]['code']) && $array['subErrors'][0]['code'] == 21)//{"code":"21" 无效参数
                return 403;
            else
                return json_encode($array['subErrors'][0],JSON_UNESCAPED_UNICODE);
        }
        else
        {
            return $json;
        }
    }

    
    /**
     *根据 GET或POST 数组来执行 ROP 接口
     * @param  $params array   GET 或 POST 数据，必填字段 具体参考 system/libraries/functions.php 的request_http_client函数
     * @param  $ignored_fields 签名验证忽略的字段，默认为空
     * @param  $method 协议方式(GET or POST) 默认为GET 具体参考 system/libraries/functions.php 的request_http_client函数
     * @return integer|array 返回接口执行结果
     *
     */
    function executeRopApi($params, $ignored_fields=array(),$method="GET")
    {
        $api_url = WI_CONFIG::$rop_api_url_prefix;
        $str_for_sign = WI_CONFIG::$rop_app_secret;
        
        $index = 0;
        $params = array_merge($params,array('appcode'=>WI_CONFIG::$rop_app_code,'v'=>WI_CONFIG::$rop_app_version,'format'=>WI_CONFIG::$rop_app_format));

        ksort($params);
        foreach($params as $key => $value)
        {
            $api_url .= ($index ==0 ? '?' : '&').$key.'='.$value;
            $str_for_sign .= (!in_array($key,$ignored_fields) ? $key.$value : '');
            $index++;
        }
        
        $sign = strtoupper(sha1($str_for_sign.WI_CONFIG::$rop_app_secret));
        $api_url .='&sign='.$sign ;
        $params['sign'] = $sign;
        $method=="POST" && $api_url = WI_CONFIG::$rop_api_url_prefix;
        $json_result = request_http_client($api_url, array('formvars'=>$params,'formfiles'=>array()), $method);

        
        if($this->rop_api_debug)
            register_shutdown_function(array($this, 'showRopApiDebug'),$api_url,$params,strtoupper($method),$json_result); 

        return $this->handleRopError($json_result);
    }

    /**
     *
     * Rop api debug
     * @param string $api_url api url
     * @param array $params 参数
     * @param string $method GET or POST
     * @param string $json_result json返回串
     * @return string rop debug html内容
     */
    function showRopApiDebug($api_url,$params,$method,$json_result)
    {
        $output = '<p><font color=944030 face=arial size=2><b>ROP API Debug...</b></font></p><p><font face=arial size=2 color=000099><b>'.$method.'</b> </font>';
        $output .= '<font face=arial size=2 color=000099><b>'.$api_url.'</font></p>';
        $method== 'POST' && $output .= '<p><b> POST Data:</b><font face=arial size=2 color=000099> '.var_export($params,true).'</font></p>';
        $output .= '<p><b>==></b><b>Return:</b><font face=arial size=2 color=000099> '.var_export($json_result,true).'</font></p><br/>';
        echo $output;
    }
}