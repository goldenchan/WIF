<?php
/**
 * model抽象基础类
 * @package model
 * @author 陈金(wind.golden@gmail.com)
 * @create 2010-7-4 09:33
 */
/**  
 * model Base class with basic methods related to models. This class should not be used directly.
 */
abstract class Model {
    /**
     * db 句柄
     * @var object
     */
    protected $_pdo = null;
    /**
     * db 对象
     */
    protected $_dbo = null;
    /**
     * db statement 参数
     */
    protected $_stmtParams = array();
    /**
     * 表前缀
     * @var string
     */
    protected $_prefix = null;
    /**
     * 数据库是否初始化
     *@var boolean
     */
    protected $_dbInitialized = false;
    /**
     * 最后一条错误
     * @var string
     */
    protected $_lastError = "";
    /**
     * 正则验证
     * @var array 每个字段一个数组元素 比如 array('mobile'=>'/13[\d]{9}/')
     */
    public $validate_preg = null;
    /**
     * 不带前缀的表名
     * @var string
     */
    public $table;
    /**
     * 表结构信息
     * @var string
     */
    protected $_table_info = null;
    /**
     * 主键名
     * @var string
     */
    public $primary_key = null;
    /**
     * 支持的缓存类型
     *@var array
     */
    public $support_caches = array(
        'file',
        'mem',
        'redis',
        'sqlite'
    );
    /**
     * 缓存类名
     * @var string file mem redis sqlite
     */
    public $cache = null;
    /**
     * 主键值
     * @var int|string
     */
    protected $_primary_key_value = null;
    /**
     * debug
     * @var boolean
     */
    public $debug = false;
    /**
     * 是否事务处理
     */
    protected $_in_transaction = false;
    /**
     * 数据源
     * @see WI_CONFIG::$dbs
     */
    protected $_dsn = 'default';
    /**
     * 构造函数
     * @param string $default_cache_type 缓存类型
     */
    function __construct($default_cache_type = 'file') {
        default_value($this->_prefix, WI_CONFIG::$dbs[$this->_dsn]['table_prefix']);
        $this->setModelCache($default_cache_type);
        if (is_string($this->table) && $this->table !== '') {
            $this->table = $this->_prefix . $this->table;
        }
        else {
            throw new Exception('Model::__construct, the table name is null');
        }
    }
    /**
     * 初始化数据库对象
     * @return model对象
     */
    public function _initializeDb() {
        if (!isset($this->_dbo)) {
            $this->_dbo = new Db_Base();
            $this->_pdo = $this->_dbo->getDb($this->_dsn);
            $this->_dbo->do_profile = $this->_dbo->use_trace_log = $this->debug;
        }
        return $this;
    }
    /**
     * 获得数据库Handler
     * return db handler object
     */
    public function getPdo() {
        return $this->_initializeDb()->_pdo;
    }
    /**
     * 获得数据库对象
     * return db object
     */
    public function getDbo() {
        return $this->_initializeDb()->_dbo;
    }
    /**
     *  设置debug 模式
     * @param boolean $debug true of false
     */
    public function setDebugMode($debug = false) {
        $this->debug = $debug;
        register_shutdown_function(array(
            $this,
            'showCacheDebug'
        ));
    }
    /**
     *  缓存的debug 显示
     */
    public function showCacheDebug() {
        if (!$this->debug) return;
        if (is_object($this->cache)) {
            foreach ($this->cache->trace_log() as $log) {
                echo $log;
            }
        }
    }
    /**
     *   数据库的debug 显示
     */
    public function showDbDebug() {
        if (!$this->debug) return;
        $this->_initializeDb();
        if (count($this->_db->trace_log) > 0) {
            foreach ($this->_db->trace_log as $k => $log) {
                echo $log;
                echo "<font face=arial size=2 color=000099><b>Execute Time</b> " . $this->_db->profile_times[$k]['time'] . 's</font>';
            }
        }
        else echo '<div align=center><font face=arial size=2 color=000099><strong>No sql query has been executed</strong></font></div>';
    }
    /**
     *  返回数据库操作错误
     * @return string  db error
     */
    public function DbError() {
        return $this->_lastError;
    }
    /**
     * 表切换
     * @param string $tbl_name 表名
     * @param string $tbl_prefix 前缀
     * @return model对象
     */
    protected function selectTable($tbl_name, $tbl_prefix = null) {
        $this->table = isset($tbl_prefix) ? $tbl_prefix . $tbl_name : $this->_prefix . $tbl_name;
        return $this;
    }
    /**
     * 执行sql语句
     * @param string  $sql sql语句
     * @param string $type 执行类型 其中 all(取所有) one（取一个） row（取一行） col（取一列） query（非select操作 比如update insert delete )
     * @return boolean|array 执行成功返回true 失败返回false
     */
    public function execute($sql = '', $type = 'all', $params = array()) {
        $function_list = array(
            'one' => 'getVar',
            'row' => 'getRow',
            'col' => 'getCol',
            'all' => 'getResults',
            'query' => 'query'
        );
        if (!isset($function_list[$type])) {
            $this->_lastError = 'wrong params for execute function';
            return false;
        }
        $function = $function_list[$type];
        return $this->getDbo()->$function($sql, $params);
    }
    /**
     * 事务处理 开始
     * @note 用了事务的表无法用缓存 读写数据时候不能加缓存key和缓存组
     * @return model对象
     */
    public function startTransaction() {
        $this->_in_transaction = true;
        return $this->getPdo()->beginTransaction();
    }
    /**
     * 事务处理 提交
     * @note 用了事务的表无法用缓存 读写数据时候不能加缓存key和缓存组
     * @return model对象
     */
    public function commit() {
        $this->_in_transaction = false;
        return $this->getPdo()->commit();
    }
    /**
     * 事务处理 回滚
     * @note 用了事务的表无法用缓存 读写数据时候不能加缓存key和缓存组
     * @return model对象
     */
    public function rollBack() {
        $this->_in_transaction = false;
        return $this->getPdo()->rollBack();
    }
    /**
     * 获取一条记录
     * @note 此操作可关联缓存
     * @param array  $condition 查询条件，只能获取一行
     * @param string|array  $return_fields 字符串时 '*'为取所有  'user_id,user_name'为取部分, 数组时  array('user_id','user_name')为取部分
     * @param string $cache_key  缓存key
     * @param string $cache_group  缓存组
     * @return array 返回查询结果
     */
    public function find($condition = array() , $return_fields = '*', $cache_key = null, $cache_group = null) {
        $result = array();
        $cache_available = isset($cache_group, $cache_key, $this->cache);
        $cache_available && $result = $this->cache->get($cache_key, $cache_group, $condition);
        if (empty($result)) {
            $select_sql = $this->buildSqlScript("select", $this->table, '*', $condition);
            $result = $this->execute($select_sql, 'row');
            if (!empty($result) && $cache_available) {
                $this->cache->set($cache_key, $cache_group, $result);
            }
        }
        if (is_array($result) && !in_array($return_fields, array(
            '*',
            '',
            array()
        ))) {
            if (is_string($return_fields)) {
                $return_fields = explode(',', $return_fields);
            }
            $result = array_intersect_key($result, array_flip($return_fields));
        }
        return $result;
    }
    /**
     * 通过主键值获取一条记录
     * @note 此操作可关联缓存
     * @param string|int $primary_key_value  主键值
     * @param string|array $return_fields 字符串时 '*'为取所有  'user_id,user_name'为取部分 数组时  array('user_id','user_name')为取部分
     * @param string $cache_group  缓存组
     * @return array 返回查询结果
     */
    public function findById($primary_key_value = null, $return_fields = array() , $cache_group = null) {
        return $this->setIdValue($primary_key_value)->find(array(
            $this->primary_key => $this->_primary_key_value
        ) , $return_fields, $this->_primary_key_value, $cache_group);
    }
    /**
     * 设置主键的值
     * @param mixed   $value 主键的值
     * @return model object
     */
    protected function setIdValue($value) {
        $this->_primary_key_value = $value;
        return $this;
    }
    /**
     * 设置缓存类型
     * @param string  $type 类型
     * @return model object
     */
    public function setModelCache($type = 'file') {
        $this->cache = new Cache_Provider($type, $this->debug);
        return $this;
    }
    /**
     * 更新一条记录
     * @note 此操作可关联缓存 涉及事务处理
     * @param array  $update_info  要更新的数据
     * @param array  $condition   更新条件
     * @param string $cache_key  缓存key
     * @param string $cache_group  缓存组
     * @return boolean|int 成功则返回影响的行数, 失败返回false
     */
    public function update($update_info = array() , $condition = array() , $cache_key = null, $cache_group = null) {
        $update_sql = $this->buildSqlScript('update', $this->table, $update_info, $condition);
        $retval = $this->execute($update_sql, 'query', $this->_stmtParams);
        $cache_available = isset($cache_group, $cache_key, $this->cache);
        if ($retval !== false && $cache_available && !$this->_in_transaction) {
            $this->cache->update($cache_key, $cache_group, $update_info, $condition);
            return true;
        }
        return $retval;
    }
    /**
     *  插入操作
     * @note 此操作可关联缓存 涉及事务处理
     * @param array  $insert_info  要插入的数据
     * @param string $cache_group  缓存组
     * @return boolean|string|int  操作失败返回 false，操作成功时，有主键值则返回主键值，无主键值返回true
     */
    public function save($insert_info = array() , $cache_group = null) {
        $insert_sql = $this->buildSqlScript("insert", $this->table, $insert_info, array());
        if ($this->execute($insert_sql, 'query', $this->_stmtParams) && !$this->_in_transaction) {
            $cache_key = null;
            if ($this->primary_key !== null) {
                $insert_info[$this->primary_key] = $this->_primary_key_value = $cache_key = $this->_db->insert_id;
            }
            $cache_available = isset($cache_group, $cache_key, $this->cache);
            if ($cache_available && !$this->_in_transaction) {
                $result = array_merge($this->getTableDict('default') , $insert_info);
                $this->cache->set($cache_key, $cache_group, $result);
            }
            return isset($this->primary_key) ? $this->_primary_key_value : true;
        }
        return false;
    }
    /**
     * Replace操作
     * @note 此操作未关联缓存 不涉及事务
     * @param array  $replace_info  要Replace的数据
     * @return boolean  true or false
     */
    public function replace($replace_info = array()) {
        $insert_sql = $this->buildSqlScript("replace", $this->table, $replace_info, array());
        return $this->execute($insert_sql, 'query', $this->_stmtParams);
    }
    /**
     * 删除一条记录
     * @note 此操作可关联缓存 涉及事务处理
     * @param array $condition  删除条件
     * @param string $cache_key  缓存key
     * @param string $cache_group  缓存组
     * @return boolean  true or false
     */
    public function delete($condition = array() , $cache_key = 0, $cache_group = null) {
        $delete_sql = $this->buildSqlScript('delete', $this->table, null, $condition);
        $retval = $this->execute($delete_sql, 'query', $this->_stmtParams);
        $cache_available = isset($cache_group, $cache_key, $this->cache);
        if ($retval !== false && $cache_available && !$this->_in_transaction) {
            $this->cache->delete($cache_key, $cache_group, $condition);
            return true;
        }
        return $retval;
    }
    /**
     * 删除多条记录 涉及事务处理
     * @param array  $condition  删除条件
     * @param string $cache_group  缓存组
     * @return boolean  true or false
     */
    public function deleteAll($condition = array() , $cache_group = null) {
        $delete_sql = $this->buildSqlScript('delete', $this->table, null, $condition);
        $retval = $this->execute($delete_sql, 'query', $this->_stmtParams);
        $cache_available = isset($cache_group, $cache_key, $this->cache);
        if ($retval !== false && $cache_available && !$this->_in_transaction) {
            $select_sql = $this->buildSqlScript('select', $this->table, array(
                $this->primary_key
            ) , $condition, 'mysql', $this->getDbo());
            $cache_keys = $this->execute($select_sql, 'col');
            foreach ($cache_keys as $cache_key) {
                $this->cache->delete($cache_key, $cache_group, $condition);
            }
            return true;
        }
        return $retval;
    }
    /**
     * 更新多条记录 涉及事务处理
     * @param array  $update_info  要更新的数据
     * @param array  $condition 查询条件
     * @param string $cache_group  缓存组名
     * @return boolean 更成成功返回true 失败返回false
     */
    public function updateAll($update_info = array() , $condition = array() , $cache_group = null) {
        $sql = $this->buildSqlScript('update', $this->table, $update_info, $condition);
        $retval = $this->execute($sql, 'query', $this->_stmtParams);
        $cache_available = isset($cache_group, $cache_key, $this->cache, $this->primary_key);
        if ($retval !== false && $cache_available && !$this->_in_transaction) {
            $select_sql = $this->buildSqlScript('select', $this->table, array(
                $this->primary_key
            ) , $condition, 'mysql', $this->getDbo());
            $cache_keys = $this->execute($select_sql, 'col');
            foreach ($cache_keys as $cache_key) {
                $this->cache->update($cache_key, $cache_group, $update_info, $condition);
            }
            return true;
        }
        return $retval;
    }
    /**
     * 批量添加
     * @param array  $insert_info  要插入的数据
     * @param string $cache_key    缓存key
     * @param string $cache_group  缓存组
     * @return boolean  true
     */
    public function saveAll($insert_info = array() , $cache_key = 0, $cache_group = null) {
        foreach ($insert_info as $row) {
            $this->save($row, $cache_key, $cache_group);
        }
        return true;
    }
    /**
     *  查询记录是否存在
     * @param array   $condition  查询条件
     * @return boolean  true or false
     */
    public function hasAny($condition = array()) {
        $return_fields = isset($this->primary_key) ? array(
            $this->primary_key
        ) : '*';
        $return_fields = '*';
        $select_sql = $this->buildSqlScript("select", $this->table, $return_fields, $condition);
        $result = $this->execute($select_sql, 'col', $this->_stmtParams);
        return (count($result) > 0);
    }
    /**
     * 生成表结构信息并生成持久化缓存数据
     * @param string  $table_name  表名
     * @return array  table information
     */
    public function generateTableInfo($table_name = '') {
        if ($this->_table_info !== null) {
            return $this->_table_info;
        }
        $table_name = ($table_name == '' ? $this->table : $table_name);
        $file = TMP . "persistent" . DS . $table_name . ".php";
        create_dir(TMP . "persistent" . DS);
        if (file_exists($file)) {
            include $file;
            return $table_info;
        }
        else {
            $this->_initializeDb();
            $table_info = null;
            $result = mysql_query('SHOW FULL COLUMNS FROM ' . $table_name, $this->getDbo());
            while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
                $field = $row['Field'];
                unset($row['Field']);
                $table_info[$field] = array_change_key_case($row);
            }
            unset($result);
            if ($table_info !== null) {
                write_file($file, "<?php \$table_info= " . var_export($table_info, TRUE) . ";");
            }
            return $table_info;
        }
    }
    /**
     *  获取数据表数据字典,每个字段可返回的属性有
     type(类型),
     collation(字符集),
     null(是否为空),
     key(键属性),
     default(默认值),
     extra(补充属性),
     privileges(权限),
     comment(注释)
     * @param $prop  string 上面列举的可返回属性之一
     * @param $fields array  字段名数组
     * @return array   要求字段的一维属性数组
     */
    public function getTableDict($prop = '', $fields = array()) {
        $support_properities = array(
            'type',
            'collation',
            'null',
            'key',
            'default',
            'extra',
            'privileges',
            'comment'
        );
        if (!in_array($prop, $support_properities)) {
            throw new Exception('Model::getTableDict(): the property  ' . $prop . ' is not supported ');
        }
        $this->_table_info = $this->generateTableInfo($this->table);
        $all_fields = array_keys($this->_table_info);
        if ($fields === array() || $fields === array(
            '*'
        ) || $fields === '*') $fields = $all_fields;
        if ($fields !== array_intersect((array)$fields, $all_fields)) {
            throw new Exception('Model::getTableDict(): ' . implode(',', $fields) . ' contain invalid fields');
        }
        $colums_prop = array();
        foreach ($this->_table_info as $column => $props) {
            if (in_array($column, $fields)) {
                $colums_prop[$column] = $props[$prop];
            }
        }
        return $colums_prop;
    }
    /**
     * 获取多条记录
     * @param array  $condition 查询条件 若为array()，则查询所有记录
     * @param array  $return_fields 返回的字段 若为array()，则查询所有字段
     * @param array $order_fields 排序字段 array($field1,$field2)
     * @param array|boolean $asc (boolean)是否升序 默认升序 用法比如
     （1）true则表示所有$order_fields都升序, false则表示所有$order_fields都降序
     （2）array(true,false)表示第一个字段升序，第二个字段降序
     * @param int $start 起始数
     *
     * @param int $itemsPerPage 每页数量
     * @param string $group_by GROUP BY 后的字段 一般不用
     * @return array 返回查询结果
     */
    public function findAll($condition = array() , $return_fields = '*', $order_fields = array() , $asc = true, $start = 0, $itemsPerPage = 25, $group_by = array()) {
        $group_by_sql = $select_sql = $limit_sql = $order_sql = '';
        if ($order_fields) {
            if (is_bool($asc)) {
                $direction = !$asc ? ' DESC' : ' ASC';
                $order_sql.= ' ORDER BY ' . (is_array($order_fields) ? implode($direction . ',', $order_fields) . $direction : ($order_fields !== '' ? implode($direction . ',', explode(',', $order_fields)) . $direction : strval($this->primary_key)));
            }
            elseif (is_array($asc) && count($asc) == count($order_fields)) {
                $order_sql.= ' ORDER BY ';
                foreach ($order_fields as $k => $field) {
                    $order_sql.= $field . ' ' . (!$asc[$k] ? ' DESC' : ' ASC') . ($k == count($asc) - 1 ? ' ' : ',');
                }
            }
        }
        if ($group_by) {
            $group_by_sql.= ' GROUP BY ' . (is_array($group_by) ? '`' . implode('` `', $group_by) . '`' : $group_by);
        }
        if (is_numeric($start) && $start >= 0) {
            $limit_sql = ' LIMIT ' . $start . "," . $itemsPerPage;
        }
        $select_sql = $this->buildSqlScript("select", $this->table, $return_fields, $condition);
        $select_sql.= $group_by_sql . $order_sql . $limit_sql;
        return $this->execute($select_sql, 'all', $this->_stmtParams);
    }
    /**
     *  获取指定条件的记录条数
     * @param array $condition                查询条件 若为array()，则查询所有记录
     * @param array|string $distinct_fields   排除相同记录的字段
     * @return int 返回查询结果条数
     */
    public function getCount($condition = '', $distinct_fields = '') {
        $select_sql = $this->buildSqlScript("select", $this->table, 'count(1)', $condition);
        $group_by_sql = '';
        if ((is_array($distinct_fields) && count($distinct_fields) === 1) || (is_string($distinct_fields) && strlen($distinct_fields) > 0)) {
            $field = is_array($distinct_fields) ? '`' . implode('` `', $distinct_fields) . '`' : $distinct_fields;
            $select_sql = $this->buildSqlScript("select", $this->table, 'count(distinct(' . $distinct_fields . '))', $condition);
        }
        return intval($this->execute($select_sql . $group_by_sql, 'one', $this->_stmtParams));
    }
    /**
     * Build sql script from the arguments
     * @param string $op_type (insert select update delete replace)
     * @param string $table  the table name which will be affected
     * @param array $fields  which fields you want to return from select sql
     * @param array $where  which condition you want to set from select sql
     * @return string the sql string
     * @access public
     */
    public function buildSqlScript($op_type = 'insert', $table = '', $fields = array() , $where = array()) {
        $sql = $where_sql = '';
        $columns = $values = array();
        $stmt_params = array();
        if (($table != '' && $op_type === 'select') || (!empty($fields) && $op_type !== 'select') || $op_type == 'delete') {
            switch ($op_type) {
                case 'insert':
                case 'replace':
                    $sql.= strtoupper($op_type) . " INTO " . $table . "";
                    $sql.= "(`" . implode("`,`", array_keys($fields)) . "`) ";
                    $sql.= "VALUES(" . "'" . implode(',', array_fill(0, count($fields) , '?')) . "')";
                    foreach (array_values($fields) as $k => $value) $stmt_params[] = $value;
                    break;

                case 'update':
                    $sql.= "UPDATE " . $table . " SET ";
                    $count = count($fields);
                    $index = 0;
                    foreach ($fields as $column => $value) {
                        $index++;
                        $sql.= "`{$column}`=?";
                        $sql.= ($count > $index) ? "," : " ";
                        $stmt_params[] = $value;
                    }
                    break;

                case 'select':
                    if (is_string($fields)) $sql.= "SELECT " . ($fields === '*' || $fields === '' ? "*" : $fields) . " FROM " . $table . $where_sql;
                    else $sql.= "SELECT " . !empty($fields) ? implode(',', $fields) : '*' . " FROM " . $table;
                    break;

                case 'delete':
                    $sql.= "DELETE FROM " . $table;
                    break;

                default:
                    break;
                }
                if ($op_type !== 'insert' && $op_type !== 'replace') {
                    //处理where 条件
                    if (is_array($where) && count($where) > 0) {
                        $count = count($where);
                        $sql.= " WHERE ";
                        $index = 0;
                        foreach ($where as $column => $value) {
                            $index++;
                            if (is_array($value)) {
                                $index1 = 0;
                                $sql.= '(';
                                $count = count($value);
                                foreach ($value as $k => $v) {
                                    if ($k === $index1) {
                                        $sql.= " `{$column}` =?";
                                        $sql.= ($count > $index1 + 1) ? " OR " : " ";
                                    }
                                    else {
                                        $sql.= " `{$column}` " . $k . '?';
                                        $sql.= ($count > $index1 + 1) ? " AND " : " ";
                                    }
                                    $stmt_params[] = $v;
                                    $index1++;
                                }
                                $sql.= ')';
                            }
                            else {
                                if ($column === '&&') //如果是包含多个字段条件的字符串
                                $sql.= $value;
                                else {
                                    $sql.= strpos($column, ')') > 0 ? "{$column}=?" : "`{$column}`=?";
                                    $stmt_params[] = $value;
                                }
                            }
                            $sql.= ($count > $index) ? " AND " : " ";
                        }
                    }
                    else if (is_string($where) && $where !== '') {
                        $sql.= ' WHERE ' . $where;
                    }
                }
            }
            $this->_stmtParams = $stmt_params;
            return $sql;
        }
        /**
         *  魔术函数
         *  (1)根据ID获取某个字段 （数据库中字段名是tag_name  这里条用就得用TagName 这样做是为了统一函数名规范）
         *	函数第一个参数  id值，	第二个参数  可选参数，为缓存组名(cache_group)
         *	(2)根据查询条件获取某个字段
         *
         *
         * @example1 取tag_id=1对应的tag_name,
         *		 $tag_name = $this->findTagNameById(1,'tag');
         *
         * @example2 根据条件获取一条记录的某个字段，必须保证该条件下只有一条记录，此时不能用缓存
         *
         *	    例如：取tag_id =2 的单个tag_name
         *		$tag_name = $this->findTagName(array('tag_id'=>2));
         *
         * @param string $method 函数名
         * @param array $args 参数
         * @return  array|string  执行$method($args)的返回值
         */
        public function __call($method, $args) {
            if (substr($method, 0, 4) === 'find' && $method !== 'find' && $method !== "findById") {
                if (substr($method, -4) === 'ById') {
                    $field_name = word_underscore(substr($method, 4, -4));
                    $result = $this->findById($args[0], array(
                        $field_name
                    ) , isset($args[1]) ? $args[1] : null);
                }
                else {
                    $field_name = word_underscore(substr($method, 4));
                    $result = $this->find($args[0], array(
                        $field_name
                    ) , isset($args[1]) ? $args[1] : null, isset($args[2]) ? $args[2] : null);
                }
                if (empty($result)) return null;
                return $result[$field_name];
            }
            throw new Exception('Model::__call(): method ' . $method . ' of  ' . get_class($this) . ' dose not exists, exiting.');
        }
    }
    
