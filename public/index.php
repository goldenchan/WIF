<?php
/**
 // File name   : index.php
 // Description : app入口文件
 // Copyright(C), Chenjin, 2012, All Rights Reserved.
 //
 // Author: Chenjin (wind.golden@gmail.com)
 */
/**
 * 目录分割符
 */
define('DS', DIRECTORY_SEPARATOR);
/**
 * application 路径
 */
define('APP_ROOT_PATH', dirname(__DIR__) . DS . 'application' . DS);
/**
 * 系统文件路径
 */
define('SYS_ROOT_PATH', dirname(__DIR__) . DS . 'system' . DS);
/**
 * 加载应用配置
 */
require dirname(__DIR__) . DS . 'configs/app_config.php';
/**
 * bootstrap
 */
require APP_ROOT_PATH . DS . "bootstrap.php";
$router = new Router;
$router->route(WI_CONFIG::$routes)->default_route(array(
    'default/Default',
    'error404'
))->execute();
