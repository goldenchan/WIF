<?php
/**
 * Name: class.wi_log.php
 * Description: ¿¿¿¿
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2001-6-3
 * Version: 1.0
 */
require( __DIR__.'/baselib/Log/Log.php');
class WI_Log
{
	var $logger = null;
	function WI_Log($handler=null, $name = '', $ident = '', $conf = array(),
                        $level = PEAR_LOG_DEBUG){
		if(!empty($handler)){
			$this->logger = Log::singleton(
				$handler,
				APP_ROOT_PATH."logs".DS.$name,
				$ident,
				$conf,
                $level);
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
