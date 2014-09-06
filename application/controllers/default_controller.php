<?php
/**
 * default控制器文件
 * @file application/controllers/default_controller.php
 * @package controller
 * @category  controller
 * @author Developer User (developer@gmail.com)
 * @date 2014-08-21 12:39:52
 */
/**
 * 默认控制器
 */
class Default_Controller extends App_Controller {
    /**
     * 是否加载预定义包含用户权限的view变量,默认false
     * @var boolean
     * @see App_Controller
     */
    var $pre_load_view_params = true;
    /**
     * 首页
     * @param $params array('_URL'=>'url ','_GET'=>'GET参数和值','_POST'=>'POST参数和值','FILES'=>'FILES参数和值')
     */
    public function index($params) { 
        //return $this->model('Driver')->find(array('driver_name'=>'哈哈')); 自动把return的数组对象转成json串
        $this->view()->render();
    }
    /**
     * 404
     */
    public function error404() {
        $this->view()->render('default/error404');
    }
}
