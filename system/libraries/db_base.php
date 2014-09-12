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
     */
    static $dbh = null;
    /**
     * statement object
     */
    var $stmt = null;
    /**
     * db object
     * @var object
     */
    static $db = null;
    /**
     * sql中的变量
     * @var array
     */
    var $parameters = null;
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
    public static function getDb($dsn) {
        if (!isset(self::$db[$dsn])) {
            try {
                self::$db[$dsn] = new PDO(WI_CONFIG::$dbs[$dsn]['dsn'], WI_CONFIG::$dbs[$dsn]['user'], WI_CONFIG::$dbs[$dsn]['password'], array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ));
                // We can now log any exceptions on Fatal error.
                self::$db[$dsn]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Disable emulation of prepared statements, use REAL prepared statements instead.
                self::$db[$dsn]->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$db[$dsn]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$dbh = self::$db[$dsn];
            }
            catch(PDOException $e) {
                self::$isConnected = false;
                throw new Exception($e->getMessage());
            }
        }
        return self::$db[$dsn];
    }
    /**
     * 获取一个字段的值
     * @param string $query 带有变量的SQL语句
     * @param array $params SQL语句的变量值
     */
    public function getVar($query, $params = array()) {
        try {
            $this->stmt = self::$dbh->prepare($query);
            $this->stmt->execute($params);
            $values = array_values($this->stmt->fetch());
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
        // If there is a value return it else return null
        return (isset($values[0]) && $values[0] !== '') ? $values[0] : null;
    }
    /**
     * 获取一列
     * @param string $query 带有变量的SQL语句
     * @param array $params SQL语句的变量值
     */
    public function getCol($query, $params = array()) {
        $results = $this->getResults($query, $params);
        // Extract the column values
        for ($i = 0; $i < count($results); $i++) {
            $new_array[$i] = array_values($results[$i]) [0];
        }
        return $new_array;
    }
    /**
     * 获取一行
     * @param string $query 带有变量的SQL语句
     * @param array $params SQL语句的变量值
     */
    public function getRow($query, $params = array()) {
        try {
            $this->stmt = self::$dbh->prepare($query);
            $this->stmt->execute($params);
            return $this->stmt->fetch();
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * 获取多条数据
     * @param string $query 带有变量的SQL语句
     * @param array $params SQL语句的变量值
     */
    public function getResults($query, $params = array()) {
        try {
            $this->stmt = self::$dbh->prepare($query);
            $this->stmt->execute($params);
            return $this->stmt->fetchAll();
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * query insert update delate raplace
     * @param string $query 带有变量的SQL语句
     * @param array $params SQL语句的变量值
     */
    public function query($query, $params = array()) {
        try {
            $this->stmt = self::$dbh->prepare($query);
            $ret = $this->stmt->execute($params);
            if (preg_match("/^(insert|replace)\s+/i", $query)) return self::$dbh->lastInsertId();
            elseif (preg_match("/^(delete|update|drop|create)\s+/i", $query)) return $this->stmt->rowCount();
            else return $ret;
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
