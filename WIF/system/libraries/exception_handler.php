<?php
/**
* 异常处理类文件
* @file Exception_Handler.php
* @package bootstrap 
* @author 陈金(wind.golden@gmail.com)
 */	
/**
 * 异常处理类
 */
 class Exception_Handler extends Exception
{
    /**
     * exception处理函数
     * @param $handler
     */
	public function runtimeException($handler)
    {
        error_log('出现异常：'.$handler->getMessage(). '; 发生在文件的'.$handler->getFile().'第'.$handler->getLine(). '行, '.$handler->getTraceAsString());
	}
}
