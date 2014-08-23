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
require( __DIR__.'/ez_sql/ez_sql_sqlite.php');

class WI_Sqlite extends ezSQL_sqlite
{
	function WI_Sqlite($db_path='', $db_name='')
	{
		global $_WI_CONFIG;
		if(!empty($db_name)){
			parent::ezSQL_sqlite($db_path, $db_name);
		}
		elseif(isset($_WI_CONFIG['sqlite']))
		{
			parent::ezSQL_sqlite($_WI_CONFIG['sqlite']['db_path'],$_WI_CONFIG['sqlite']['db_name']);
		}
		else
		{
			trigger_error('Sqlite: missing database server config', E_USER_ERROR);
			exit(1);
		}
	}
} 
