<?php
/**
 * Name: class.wi_message_queue.php
 * Description: 消息队列处理类初始化
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2010-6-3
 * Version: 1.0
 */

require( __DIR__.DS.'Pheanstalk'.DS.'pheanstalk_init.php');
class WI_MessageQueue extends Pheanstalk_Pheanstalk
{
	function __construct($pheanstalk_host=null)
	{
		if(isset($pheanstalk_host))
		{
			parent::__construct($pheanstalk_host);
		}
		elseif(isset(WI_CONFIG::$pheanstalk_host))
		{
			parent::__construct($pheanstalk_host);
		}
		else
		{
			trigger_error('Cache: missing Pheanstalk server config', E_USER_ERROR);
			exit(1);
		}
	}
} 
