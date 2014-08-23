<?php
/**
 * Name: class.wi_mysql.php
 * Description: 基于ez_sql的mysql操作类，简便快捷的进行mysql数据库操作
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2010-6-3
 * Version: 1.0
 */

require( __DIR__.'/ez_sql/ez_sql_core.php');
require( __DIR__.'/ez_sql/ez_sql_mysql.php');

class WI_MySQL extends ezSQL_mysql
{
	function WI_MySQL($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost',$dbcharset = 'utf8')
	{
		if(!empty($dbuser)){
			parent::ezSQL_mysql($dbuser, $dbpassword, $dbname, $dbhost,$dbcharset);
		}
		elseif(isset(WI_CONFIG::$mysql_dbser))
		{
			parent::ezSQL_mysql(
				WI_CONFIG::$mysql_dbser,
				WI_CONFIG::$mysql_dbpassword,
				WI_CONFIG::$mysql_dbname,
				WI_CONFIG::$mysql_dbhost,
				WI_CONFIG::$mysql_dbcharset);
		}
		else
		{
			trigger_error('DB: missing database server config', E_USER_ERROR);
			exit(1);
		}
	}
} 
