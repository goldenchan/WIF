<?php

/**
 *  数据库基础类文件
 *
 * @filename   : db_base.php
 * @package model
 * @author: Chenjin (wind.golden@gmail.com) 
*/
/**
 *
 * Provides a singleton for accessing the db and interfaces with PDb. Please use the 
 * getDb() singleton method to get access to the global database object instead of creating
 * new objects every time.
 */
class Db_Base 
{
    /**
     * db object
     * @var object
     */
	static $db=null;

	/**
	 * Singleto method that should be used to get access to the global database connection. This method
	 * will load the database information from the database configuration file (config/config.php) and
	 * will initialize a connection based on its information. If it is unable to start a new database connection, this
	 * method wil stop all processing and display an error message.
	 *
	 * @return Returns a reference to a PDb driver, with a working connection to the database.
	 */
	public static function &getDb()
	{
		if( !isset( self::$db ) || self::$db == null) {
			
			$username = WI_CONFIG::$mysql_dbuser;
			$password = WI_CONFIG::$mysql_dbpassword;
			$dbname = WI_CONFIG::$mysql_dbname;
			$host = WI_CONFIG::$mysql_dbhost;
			$dbcharset =WI_CONFIG::$mysql_dbcharset;

			require __DIR__.DS.'baselib'.DS."class.wi_mysql.php";
			self::$db = new WI_MySQL($username, $password, $dbname,$host,$dbcharset);     
			if( !self::$db->quick_connect( $username, $password, $dbname,$host,$dbcharset )) {
				 $message = "Fatal error: could not connect to the database! erroMsg:".self::$db->last_error;
				 throw( new Exception( $message ));
				 die();
			}
			self::$db->from_disk_cache = false;
			self::$db->use_disk_cache = false;
		}
		
		return self::$db;
	}
}
