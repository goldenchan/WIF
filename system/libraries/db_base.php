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
class Db_Base {
    /**
     * db object
     * @var object
     */
    static $db = null;
    /**
     * Singleto method that should be used to get access to the global database connection. This method
     * will load the database information from the database configuration file (config/config.php) and
     * will initialize a connection based on its information. If it is unable to start a new database connection, this
     * method wil stop all processing and display an error message.
     *
     * @param string $dsn 连接串
     * @param string $user 用户名
     * @param string $password 密码
     * @return Returns a reference to a PDb driver, with a working connection to the database.
     */
    public static function getDb($dsn,$user,$password) {
        if( !isset( self::$db[$dsn] )) {
            include_once __DIR__ . DS . 'baselib' . DS . "class.wi_pdo.php";
            self::$db[$dsn] = new WI_PDO(WI_CONFIG::$dbs[$dsn]['dsn'], WI_CONFIG::$dbs[$dsn]['user'], WI_CONFIG::$dbs[$dsn]['password']);
            self::$db[$dsn]->from_disk_cache = false;
            self::$db[$dsn]->use_disk_cache = false;
        }
        return self::$db[$dsn];
    }
}
