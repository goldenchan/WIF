<?php
/**
 * Memcache ������ (֧��ͬ���ͷ������)
 * 
 * author     : heiyeluren <http://blog.csdn.net/heiyeshuwu>
 * created    : 2007-06-21
 * lastModifed: 2007-06-21
 */

/**
 * Memcache������
 */
class MemcacheOpt
{
    //---------------------
    //  ���Զ���
    //---------------------
   
    /**
     * �Ƿ���ж���ͬ�� 
     */
    var $isGroup    = true;
    
    /**
     * ����ͬ��������Ƿ�ʹ�ü��Ḻ�ص������ȡ 
     */
    var $isRandom   = true;

    /**
     * Ĭ�ϵ�Memcache�������˿� 
     */
    var $mmcPort    = 11211;

    /**
     * ����ԭʼ����Ϣ
     */
    var $groups     = array();

    /**
     * �����һ������Memcache��������Ϣ
     */
    var $groupMaster     = array();
    var $groupSlave     = array();
    
    /**
     * �����һ���������Ӷ���
     */
    var $mmcMaster       = ''; 
    var $mmcSlave       = ''; 
    

    //---------------------
    //   �ڲ���������
    //---------------------

    /**
     * ��ʾ������Ϣ
     *
     * @param string $msg ��Ҫ��ʾ��Ϣ������
     * @param string $type ��Ϣ���ͣ�error����ͨ������Ϣ��fatal ���������󣬽���ֹ����ִ��, message ����ͨ��Ϣ��ȱʡ״

̬
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
     * ���캯�� (��ʼ����������ӵ�������)
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
     * ��������з� (�����Ƿ���Ҫ���������Ӧ���з�)
     *
     * @param array $hostArray ���������б�1
     * @param array $hostArray2 ���������б�2
     * @return void
     */
    function splitGroup($hostArray, $hostArray2=array()){
        //���ֻ��һ̨������ʹ�÷���
        if (count($hostArray) < 2 && empty($hostArray2)){
            $this->isGroup = false;
        }

        //ʹ�÷���
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
     * ���ӵ�Memcache������
     */
    function connect(){
        if (!is_array($this->groupMaster) || empty($this->groupMaster)){
            $this->showMessage("Memcache host1 array invalid", 'error');
            return false;
        }

        //���ӵ�һ��Memcache������
        $this->mmcMaster = new Memcache;
        foreach($this->groupMaster as $hosts){
            $tmp = explode(":", $hosts);
            $host = $tmp[0];
            $port = (!isset($tmp[1]) || $tmp[1]=='') ? $this->mmcPort : $tmp[1];
            $this->mmcMaster->addServer($host, $port);
        }

        //�����Ҫ���������ӵڶ���Memcache������
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
     * �ر�Memcache����������
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
     * ���ݲ������ĺ���
     *
     * @param string $optType �������ͣ���Ҫ�� add, set, replace, delete, flush
     * @param string $key �ؼ��֣������ add,set,replace,delete ��Ҫ�ύkey����
     * @param string $val �ؼ��ֶ�Ӧ��ֵ������� add, set,replace ��Ҫ�ύvalue����
     * @param int $expire ������Ч�ڣ������ add,set,replace��Ҫ�ύexpire����
     * @return mixed ��ͬ����Ҫ������ͬ�ķ���
     */
    function opt($optType, $key=null, $val=null, $expire=0){
        if (!is_object($this->mmcMaster)){
            $this->showMessage("Not availability memcache connection object", 'fatal');
        }
        if ($this->isGroup && !is_object($this->mmcSlave)){
            $this->showMessage("Group 2 memcache host connection object not availability", 'error');
        }

        //�������ݲ���
        if ($optType=='add' || $optType=='set' || $optType=='replace'){
            $this->mmcMaster->set($key, $val, false, $expire);
            if ($this->isGroup && is_object($this->mmcSlave)){
                $this->mmcSlave->set($key, $val, false, $expire);
            }
            return true;
        }

        //��ȡ���ݲ���
        if ($optType == 'get'){

            //ȱʡ��ȡ��һ������
            if (!$this->isGroup || !is_object($this->mmcSlave)){
                return $this->mmcMaster->get($key);        
            }

            //����������������
            $num = ( $this->isRandom ? rand(1, 2) : 1 );
            $obj = "mmc". ($num==1?"Master":"Slave");
            $val = $this->$obj->get($key);

            //���û����ȡ�����ݣ����������һ��
            if ($val == ""){
                switch($num){
                    case 1: $val = $this->mmcSlave->get($key); break;
                    case 2: $val = $this->mmcMaster->get($key); break;
                    default: $val = $this->mmcMaster->get($key);
                }
            }
            return $val;
        }

        //ɾ�����ݲ���
        if ($optType == 'delete'){
            $this->mmcMaster->delete($key, $expire);
            if ($this->isGroup && is_object($this->mmcSlave)){
                $this->mmcSlave->delete($key);        
            }
            return true;
        }

        //������ݲ���     
        if($optType == 'flush'){
            $this->mmcMaster->flush();
            if ($this->isGroup && is_object($this->mmcSlave)){
                $this->mmcSlave->flush();        
            }
            return true;
        }
        
    }


    //---------------------
    //   �ⲿ��������
    //---------------------

    //����һ��Ԫ��
    function add($key=null, $val=null, $expire=0){
       return $this->opt('add', $key, $val, $expire); 
    }

    //����һ��Ԫ��
    function set($key=null, $val=null, $expire=0){
        return $this->opt('set', $key, $val, $expire);
    }

    //�滻һ��Ԫ��
    function replace($key=null, $val=null, $expire=0){
        return $this->opt('replace', $val, $expire);
    }

    //��ȡһ��Ԫ��
    function get($key=null){
        return $this->opt('get', $key);
    }

    //ɾ��һ��Ԫ��
    function delete($key=null, $timeout=0){
        return $this->opt('delete', $key, null, $timeout);
    }

    //�����е�Ԫ�ع��� (���ӿڲ�Ҫ����ʹ��)
    function flush(){
       return $this->opt('flush'); 
    }


    /**
     * ��ȡ����Memcache������״̬
     */
    function getStats(){
        $status = array();

        //�������ӵ�ÿ̨Memcache
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
     * ��ȡ����Memcache�������汾��
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