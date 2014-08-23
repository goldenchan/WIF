<?php
/**
 * Memcache 操作类 (支持同步和分组访问)
 * 
 * author     : heiyeluren <http://blog.csdn.net/heiyeshuwu>
 * created    : 2007-06-21
 * lastModifed: 2007-06-21
 */

/**
 * Memcache操作类
 */
class MemcacheOpt
{
    //---------------------
    //  属性定义
    //---------------------
   
    /**
     * 是否进行多组同步 
     */
    var $isGroup    = true;
    
    /**
     * 多组同步情况下是否使用减轻负载的随机存取 
     */
    var $isRandom   = true;

    /**
     * 默认的Memcache服务器端口 
     */
    var $mmcPort    = 11211;

    /**
     * 保存原始组信息
     */
    var $groups     = array();

    /**
     * 保存第一、二组Memcache服务器信息
     */
    var $groupMaster     = array();
    var $groupSlave     = array();
    
    /**
     * 保存第一、二组连接对象
     */
    var $mmcMaster       = ''; 
    var $mmcSlave       = ''; 
    

    //---------------------
    //   内部操作方法
    //---------------------

    /**
     * 显示错误信息
     *
     * @param string $msg 需要显示消息的内容
     * @param string $type 消息类型，error是普通错误消息，fatal 是致命错误，将终止程序执行, message 是普通消息，缺省状

态
     * @return bool true
     */
    function showMessage($msg, $type){
        $msg .= " ";
        switch($type){
            case 'error':
                echo("Memcache Error: ". $msg);
                break;
            case 'fatal':
                die("Memcache Fatal: ". $msg);
                break;
            case 'message':
                echo("Memcache Message: ". $msg);
                break;
            default:
                echo("Memcache Error: ". $msg);
        }
        return true;
    }


    /**
     * 构造函数 (初始化分组和连接到服务器)
     */
    function MemcacheOpt($hostArray, $hostArray2=array()){
        if (!is_array($hostArray) || empty($hostArray)){
            $this->showMessage('Memcache host list invalid', 'fatal');
        }
        $this->groups = array_merge($hostArray, $hostArray2);
        $this->splitGroup($hostArray, $hostArray2);
        $this->connect();
    }

    /**
     * 对组进行切分 (按照是否需要分组进行相应的切分)
     *
     * @param array $hostArray 主机数组列表1
     * @param array $hostArray2 主机数组列表2
     * @return void
     */
    function splitGroup($hostArray, $hostArray2=array()){
        //如果只有一台机器则不使用分组
        if (count($hostArray) < 2 && empty($hostArray2)){
            $this->isGroup = false;
        }

        //使用分组
        if ($this->isGroup){
            if (is_array($hostArray2) && !empty($hostArray2)){
                $this->groupMaster = $hostArray;
                $this->groupSlave = $hostArray2;
            }else{
                $count = ceil(count($hostArray) / 2);
                $this->groupMaster = array_splice($hostArray, 0, $count);
                $this->groupSlave = array_splice($hostArray, 0);
            }
        }else{
            $this->groupMaster = $hostArray; 
        } 
    }

    /**
     * 连接到Memcache服务器
     */
    function connect(){
        if (!is_array($this->groupMaster) || empty($this->groupMaster)){
            $this->showMessage("Memcache host1 array invalid", 'error');
            return false;
        }

        //连接第一组Memcache服务器
        $this->mmcMaster = new Memcache;
        foreach($this->groupMaster as $hosts){
            $tmp = explode(":", $hosts);
            $host = $tmp[0];
            $port = (!isset($tmp[1]) || $tmp[1]=='') ? $this->mmcPort : $tmp[1];
            $this->mmcMaster->addServer($host, $port);
        }

        //如果需要分组则连接第二组Memcache服务器
        if ($this->isGroup){
            if ( !is_array($this->groupSlave) || empty($this->groupSlave) ){
                $this->showMessage("Memcache host2 array invalid", 'error');
                return false;
            }
            $this->mmcSlave = new Memcache;
            foreach($this->groupSlave as $hosts){
                $tmp = explode(":", $hosts);
                $host = $tmp[0];
                $port = (!isset($tmp[1]) || $tmp[1]=='') ? $this->mmcPort : $tmp[1];
                $this->mmcSlave->addServer($host, $port);
            }
        }
    }

    /**
     * 关闭Memcache服务器连接
     */
    function close(){
        if (is_object($this->mmcMaster)){
            $this->mmcMaster->close();
        }
        if (is_object($this->mmcMaster)){
            $this->mmcMaster->close();
        }        
        return true;
    }

    /**
     * 数据操作核心函数
     *
     * @param string $optType 操作类型，主要有 add, set, replace, delete, flush
     * @param string $key 关键字，如果是 add,set,replace,delete 需要提交key参数
     * @param string $val 关键字对应的值，如果是 add, set,replace 需要提交value参数
     * @param int $expire 数据有效期，如果是 add,set,replace需要提交expire参数
     * @return mixed 不同的需要产生不同的返回
     */
    function opt($optType, $key=null, $val=null, $expire=0){
        if (!is_object($this->mmcMaster)){
            $this->showMessage("Not availability memcache connection object", 'fatal');
        }
        if ($this->isGroup && !is_object($this->mmcSlave)){
            $this->showMessage("Group 2 memcache host connection object not availability", 'error');
        }

        //加入数据操作
        if ($optType=='add' || $optType=='set' || $optType=='replace'){
            $this->mmcMaster->set($key, $val, false, $expire);
            if ($this->isGroup && is_object($this->mmcSlave)){
                $this->mmcSlave->set($key, $val, false, $expire);
            }
            return true;
        }

        //获取数据操作
        if ($optType == 'get'){

            //缺省获取第一组数据
            if (!$this->isGroup || !is_object($this->mmcSlave)){
                return $this->mmcMaster->get($key);        
            }

            //分组情况下逐组访问
            $num = ( $this->isRandom ? rand(1, 2) : 1 );
            $obj = "mmc". ($num==1?"Master":"Slave");
            $val = $this->$obj->get($key);

            //如果没有提取到数据，则访问另外一组
            if ($val == ""){
                switch($num){
                    case 1: $val = $this->mmcSlave->get($key); break;
                    case 2: $val = $this->mmcMaster->get($key); break;
                    default: $val = $this->mmcMaster->get($key);
                }
            }
            return $val;
        }

        //删除数据操作
        if ($optType == 'delete'){
            $this->mmcMaster->delete($key, $expire);
            if ($this->isGroup && is_object($this->mmcSlave)){
                $this->mmcSlave->delete($key);        
            }
            return true;
        }

        //清空数据操作     
        if($optType == 'flush'){
            $this->mmcMaster->flush();
            if ($this->isGroup && is_object($this->mmcSlave)){
                $this->mmcSlave->flush();        
            }
            return true;
        }
        
    }


    //---------------------
    //   外部操作方法
    //---------------------

    //增加一个元素
    function add($key=null, $val=null, $expire=0){
       return $this->opt('add', $key, $val, $expire); 
    }

    //增加一个元素
    function set($key=null, $val=null, $expire=0){
        return $this->opt('set', $key, $val, $expire);
    }

    //替换一个元素
    function replace($key=null, $val=null, $expire=0){
        return $this->opt('replace', $val, $expire);
    }

    //获取一个元素
    function get($key=null){
        return $this->opt('get', $key);
    }

    //删除一个元素
    function delete($key=null, $timeout=0){
        return $this->opt('delete', $key, null, $timeout);
    }

    //让所有的元素过期 (本接口不要轻易使用)
    function flush(){
       return $this->opt('flush'); 
    }


    /**
     * 获取所有Memcache服务器状态
     */
    function getStats(){
        $status = array();

        //单独连接到每台Memcache
        foreach($this->groups as $key=>$hosts){
            $tmp = explode(":", $hosts);
            $host = $tmp[0];
            $port = (!isset($tmp[1]) || $tmp[1]=='') ? $this->mmcPort : $tmp[1];

            $conn = new Memcache;
            $conn->connect($host, $port);
            $s = $conn->getStats();
            $s['host'] = $host;
            $s['port'] = $port;
            $status[$key] = $s;
        }
        return $status;
    }

    /**
     * 获取所有Memcache服务器版本号
     */
    function getVersion(){
        $version = array();
        $stats = $this->getStats();
        foreach($stats as $key=>$s){
            $v['host'] = $s['host'];
            $v['port'] = $s['port'];
            $v['version'] = $s['version'];
            $version[$key] = $v;
        }
        return $version;
    }

}
?>