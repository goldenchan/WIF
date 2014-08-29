<?php
/**
 * Name: class.wi_log.php
 * Description: 基于PEAR::Log的日志处理
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2001-6-3
 * Version: 1.0
 */

require( __DIR__.'/Log/Log.php');

class WI_Log
{
	var $logger = null;

	function WI_Log($handler=null, $name = '', $ident = '', $conf = array(),
                        $level = PEAR_LOG_DEBUG)
	{
		if(!empty($handler)){
			$this->logger = &Log::singleton(
				$handler,
				$name,
				$ident,
				$conf,
				$level);
		}
		elseif(isset(WI_CONFIG::$log))
		{
			$this->logger = &Log::singleton(
				WI_CONFIG::$log['handler'],
				WI_CONFIG::$log['name'],
				WI_CONFIG::$log['ident'],
				WI_CONFIG::$log['conf'],
				WI_CONFIG::$log['level']);
		}
		else
		{
			trigger_error('Log: minssing log config', E_USER_ERROR);
			exit(1);
		}
	}

	function log($message, $priority = null)
	{
		return $this->logger->log($message, $priority);
	}
} 
