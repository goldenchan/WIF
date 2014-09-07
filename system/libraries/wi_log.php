<?php
/**
 * Name: class.wi_log.php
 * Description: 靠靠
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2001-6-3
 * Version: 1.0
 */
require( __DIR__.'/baselib/Log/Log.php');
class WI_Log
{
    /**
     * 日志handler
     */
    var $logger = null;
    /**
     * 构造函数
     * @param string $handler   The type of concrete Log subclass to return.
     *                          Attempt to dynamically include the code for
     *                          this subclass. Currently, valid values are
     *                          'console', 'syslog', 'sql', 'file', and 'mcal'.
     *
     * @param string $name      The name of the actually log file, table, or
     *                          other specific store to use. Defaults to an
     *                          empty string, with which the subclass will
     *                          attempt to do something intelligent.
     *
     * @param string $ident     The identity reported to the log system.
     *
     * @param array  $conf      A hash containing any additional configuration
     *                          information that a subclass might need.
     */
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
    /**
     * 写日志
     * @param string $message 日志内容
     * @param string $priority The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     */
    function log($message, $priority = null)
    {
		return $this->logger->log($message, $priority);
	}
} 
