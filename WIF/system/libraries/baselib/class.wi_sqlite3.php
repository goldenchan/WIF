<?php
/**
 * Name: class.wi_sqlite3.php
 * Description: 基于ez_sql的sqlite操作类，简便快捷的进行sqlite数据库操作
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2010-6-3
 * Version: 1.0
 */

require( __DIR__.'/ez_sql/ez_sql_core.php');
require( __DIR__.'/ez_sql/ez_sql_pdo.php');

class WI_Sqlite3 extends ezSQL_pdo
{
	function WI_Sqlite3($db_path='', $db_name='')
	{
		global $_WI_CONFIG;
		if(!empty($db_name)){
			parent::ezSQL_pdo('sqlite:'.$db_path.$db_name,'wi','wi');
		}
		elseif(isset($_WI_CONFIG['sqlite']))
		{
			parent::ezSQL_pdo('sqlite:'.$_WI_CONFIG['sqlite']['db_path'].$_WI_CONFIG['sqlite']['db_name'],'wi','wi');
		}
		else
		{
			trigger_error('Sqlite3: missing database server config', E_USER_ERROR);
			exit(1);
		}
	}
} 
