<?php
/**
 * 消息队列处理类文件
 * @file message_queue.php
 * @package helper
 * @author 陈金(wind.golden@gmail.com)
 */
include (__DIR__ . DS . 'baselib' . DS . 'class.wi_message_queue.php');
/**
 * 消息队列处理类
 *
 */
class Message_Queue {
    /**
     * 消息队列对象
     */
    var $msg_queue_obj = null;
    /**
     * 构造器
     * @param string $pheanstalk_host host
     */
    function __construct($pheanstalk_host = null) {
        $this->msg_queue_obj = new WI_MessageQueue($pheanstalk_host);
        if (!$this->msg_queue_obj->getConnection()->isServiceListening()) throw new Exception('No message queue service listening!');
    }
    /** 
     * 添加jobs
     * @param array|intger|string $data 要执行的job的字符串描述
     * @param string $tube job所属分类（管道）
     * @return boolean true or false 返回操作结果
     */
    function put($data, $tube = 'default') {
        return $this->msg_queue_obj->useTube($tube)->put($data);
    }
    /** 
     *  添加监听
     * @param string $tube 队列分类数据（管道）
     * @param sboolean true or false 返回操作结果
     */
    function watch($tube = 'default') {
        return $this->msg_queue_obj->watch($tube);
    }
    /** 
     * 忽略监听
     * @param string $tube 队列分类数据（管道）
     * @param boolean true or false 返回结果
     */
    function ignore($tube = 'default') {
        return $this->msg_queue_obj->ignore($tube);
    }
    /**
     * 魔术方法
     */
    function __call($method, $args) {
        call_user_func_array(array(
            $this->msg_queue_obj,
            $method
        ) , $args);
    }
}
?>
