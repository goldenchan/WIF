<?php
/**
 * Name: class.wi_memcache.php
 * Description: memcache
 * Created by: chenjin
 * Project : WI
 * Time: 2010-6-2
 * Version: 1.0
 */

require( __DIR__.'/Memcache/MemcacheOpt.php');

class WI_Memcache extends MemcacheOpt
{
	function WI_Memcache($hostArray=null, $hostArray2=array())
	{
		if(is_array($hostArray)){
			parent::MemcacheOpt($hostArray, $hostArray2);
		}
		elseif(isset(WI_CONFIG::$memcache_master))
		{
			parent::MemcacheOpt(WI_CONFIG::$memcache_master, WI_CONFIG::$memcache_slave);
		}
		else
		{
			trigger_error('Cache: missing memcached server config', E_USER_ERROR);
			exit(1);
		}
	}
} 
