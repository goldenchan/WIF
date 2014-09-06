<?php
/**
 * Name: class.wi_mysql.php
 * Description: 基于ez_sql的mysql操作类，简便快捷的进行mysql数据库操作
 * Created by: chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2010-6-3
 * Version: 1.0
 */
require (__DIR__ . '/ez_sql/ez_sql_core.php');
require (__DIR__ . '/ez_sql/ez_sql_pdo.php');
class WI_PDO extends ezSQL_pdo {
    function WI_PDO($dbuser = '', $dbpassword = '', $dbname = '', $dbhost = 'localhost', $dbcharset = 'utf8') {
        if (!empty($dbuser)) {
            parent::ezSQL_pdo($dbuser, $dbpassword, $dbname, $dbhost, $dbcharset);
        }
        elseif (isset(WI_CONFIG::$mysql_dbser)) {
            parent::ezSQL_pdo(WI_CONFIG::$dbs['default']['dsn'], WI_CONFIG::$dbs['default']['user'],  WI_CONFIG::$dbs['default']['password']);
        }
        else {
            trigger_error('DB: missing database server config', E_USER_ERROR);
            exit(1);
        }
    }
}
