<?php

/**
* sqlite操作封装类文件
* @file sqlite_cache.php
* @package cache
* @author 陈金(wind.golden@gmail.com)
*/	

include __DIR__.DS.'baselib'.DS."class.wi_sqlite3.php";
/**
 * sqlite操作封装类
 */
class Sqlite_Cache extends WI_Sqlite3
{
	/**
	 *  定义一个静态属性
	 *
	 * @var static object
	 * @access protected
	 */
    static $_instance = null;

    /**
     * sqlite table
     * @var string
     */
    static $sqlite_table = null;

    /**
     * sqlite tables
     * @var array
     */
    static $sqlite_tables = null;

    /**
     * debug
     * @var boolean
     */
    var $debug =  false;

    /**
     * debug info
     * @var array
     */
	var $debug_info = array();

    /**
     * whether show error 
     * @var boolean
     */
	public $show_errors = false;
    
    /**
     * 构造函数
     */
	function __construct(){
		global $sqlite_tables;
		require APP_ROOT_PATH."configs".DS."sqlite.php";
		self::$sqlite_tables = $sqlite_tables;
	}
	
	
	/**
	 *  实例化sql对象 
     * @param string $db_path db 路径
	 * @param string $db_name db 名
	 * @param string $table_name 表名
	 * @return object sqlite3 对象
	 */
	public static function getInstance($db_path="", $db_name='defaultDb',$table_name="answer")
	{
		if($db_name == '') //缓存开关 db_name为空
			return null;
		
		if($db_path === "") 
			$db_path = TMP."sqlite";
		$db_name = strval($db_name);

		self::$_instance = new self();
		
		$db_name = $db_name.'.db3';
		if(!file_exists($db_path.$db_name)) {
			create_dir($db_path);
			self::$_instance->WI_Sqlite3($db_path, $db_name);
			self::initTable($table_name);
			@chmod($db_path.$db_name, 0777);
		}
		else//文件存在则判断表示表是否创建
		{
			self::$_instance->WI_Sqlite3($db_path, $db_name);
			$count = self::$_instance->get_var("select count(*) from sqlite_master where tbl_name='".self::$_instance->escape($table_name)."'");
			if($count == 0)
			{
				self::initTable($table_name);
			}
		}
		
		return self::$_instance;
	}

	/**
	 *  初始化化sql对象 
	 * @param string $table_name 表名
	 * @return boolean  true or false
	 */
	private static function initTable($table_name = '')
	{
		if(is_array($table_name))
		{
			foreach($table_name as $table)
			{
				self::initTable($table);
			}
		}
		else
		{
			if(!array_key_exists($table_name,self::$sqlite_tables))
			{
				return false;
			}
			
			$sql = self::$sqlite_tables[$table_name];
			$sql_list = explode(';',$sql);
			foreach($sql_list as $sql)
			{
				if($sql != '')
				{
					self::$_instance->query($sql);
				}
			}
			return true;
		}
	}

	/**
	 *  根据key 获取dir  
	 * @param string $key key 
	 * @return string  dir
	 */
	private function getDir($key,$group='')
	{
		if(!empty($key))
		{
			$hash = md5($key.$group);
			return TMP."cache".DS."sqlite".DS.$hash{0}.$hash{1}.DS.$hash{2}.$hash{3}.DS;
		}
		return TMP."sqlite";
	}

	
	/**
	 *  条件查询表记录 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $condition 查询条件 
	 * @param array $order_by 按什么字段排序 
	 * @param int $start 起始记录数 
	 * @param int $itemsPerPage 每页记录数 
	 * @param string $table 表名 
	 * @return array 数据记录
	 */
	public static function getList($cache_key,$cache_group,$condition,$order_by = array(),$start = -1 ,$itemsPerPage = 25,$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);
		
		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
			return null;

		$order_sql = $select_sql = $limit_sql = '';
		$result = null;
		if($order_by)
		{
			$order_sql =' ORDER BY ' .(is_array($order_by) ? '`'.implode('`,`',$order_by).'`' : $order_by);
		}
		if($start != -1 && $start >= 0)
		{
			$limit_sql = ' LIMIT '.$start.",".$itemsPerPage;
		}
		$select_sql = get_sql_script('select',self::$sqlite_table,null,$condition,'sqlite');
		$select_sql .= $order_sql.$limit_sql; 

		$result = $sqlite->get_results($select_sql,ARRAY_A);
		return $result;

	}
	
	/**
	 *  添加表记录 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $insert_info 待添加数据 
	 * @param string $table 表名 
	 * @return boolean true or false
	 */
	public static function add($cache_key,$cache_group = null,$insert_info =array(),$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);
		
		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
		{
			return null;
		}

		$insert_sql = get_sql_script("replace",self::$sqlite_table,$insert_info,null,"sqlite");

		if($sqlite->query($insert_sql) !== false)
		{
			return $sqlite->insert_id;
		}
		return false;
	}
	
	/**
	 *  添加表记录 与add一样 方便调用 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $insert_info 待添加数据 
	 * @param string $table 表名 
	 * @return boolean true or false
	 */
	public function set($cache_key,$cache_group = null,$insert_info =array())
	{
		$t = self::add($cache_key,$cache_group,$insert_info);
		$this->set_debug_info(__FUNCTION__,$cache_group."_".$cache_key,$insert_info);
		return $t;
	}
	

	/**
	 *  更新表记录 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $update_info, 待更新数据 
	 * @param array $condition 查询条件
	 * @param string $table 表名 
	 * @return boolean true or false
	 */
	public function update($cache_key,$cache_group,$update_info,$condition,$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);

		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
			return null;

		$update_sql = get_sql_script("update",self::$sqlite_table,$update_info,$condition,"sqlite");
		
		if($sqlite->query($update_sql) !== false)
		{
			$this->set_debug_info(__FUNCTION__,$cache_group."_".$cache_key,$update_sql);
			return true;
		}
		return false;
	}
	

	/**
	 *  获取记录 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $condition 查询条件
	 * @param string $table 表名 
	 * @return array 记录数据
	 */
	public function get($cache_key,$cache_group,$condition,$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);
		
		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
			return null;

		$select_sql = get_sql_script("select",self::$sqlite_table,null,$condition,"sqlite");
		
		$result = $sqlite->get_row($select_sql,ARRAY_A);
		$this->set_debug_info(__FUNCTION__,$cache_group."_".$cache_key,$result);
		return $result;
	}

	/**
	 *  删除记录 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $condition 查询条件
	 * @param string $table 表名 
	 * @return Boolean true or false
	 */
	public function delete($cache_key,$cache_group,$condition,$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);

		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
			return null;

		$delete_sql = get_sql_script("delete",self::$sqlite_table,null,$condition,"sqlite");
		
		if($sqlite->query($delete_sql) !== false)
		{
			$this->set_debug_info(__FUNCTION__,$cache_group."_".$cache_key,$delete_sql);
			return true;
		}
		return false;
	}


	/**
	 *  获取记录数 
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param array $condition 查询条件
	 * @param string $table 表名 
	 * @return int 记录数
	 */
	public function getCount($cache_key,$cache_group,$condition,$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);
		
		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
			return null;

		$select_sql = get_sql_script("select",self::$sqlite_table,'count(*) AS count',$condition,"sqlite");
		$result = $sqlite->get_var($select_sql);
		return $result;
	}
	
	
	/**
	 *  手工设置表名

	 * @param string $table 表名 
	 * @return Boolean true
	 */
	public function setTable($table)
	{
		self::$sqlite_table = $table;
		return true;
	}
	
	/**
	 *  执行sql语句
	 * @param string $cache_key key 
	 * @param string $cache_group group 
	 * @param string $sql sql语句
	 * @param string $table 表名 
	 * @return mixed 执行结果
	 */
	public static function execute($cache_key,$cache_group,$sql,$table='')
	{
		if($table == '')
			self::setTable($cache_group);
		else
			self::setTable($table);
		
		$db_path = self::getDir($cache_key);
		$db_name = $cache_group."_".$cache_key;
		$sqlite = self::getInstance($db_path,$db_name,self::$sqlite_table);
		if($sqlite === null || !is_object($sqlite)) 
			return null;
		
		return $sqlite->get_results($sql,ARRAY_A);
	}
	

	/**
	 *  关闭链接
	 */
	public function Close()
	{
		self::$_instance = null;
	}

	/**
	 *  获取数据库错误
	 */
	public function getDbError()
	{
		return( self::$_lastError );
    }
    
     /**
     * 设置debug info
     * @param string $method  方法
     * @param string|integer $key 关键字
     * @param array|integer|string $data 数据
     */ 
	function set_debug_info($method='get',$key='',$data='')
	{
		if($this->debug)
		{
			$output  .= '<p><font face=arial size=2 color=000099><b>Real Cache Key </b> '.$key.' <b>==></b></font></p>';
			$output  .= '<p><font face=arial size=2 color=000099><b>'.ucfirst($method).' Cache Data</b> '.(!is_string($data)&&$data===''? '':var_export($data,true)).'</font></p>';
			$this->debug_info[0]  = '<p><font color=800080 face=arial size=2><b>Mem Cache</b> <b>Debug..</b></font></p>';
			$this->debug_info[] = $output;
		}
	}
}
