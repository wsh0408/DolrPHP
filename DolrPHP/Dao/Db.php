<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     Db
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * Db类
 **/
class Db
{
    /**
     * 日志对象
     * 
     * @var object
     */
    protected static $logger = null;

    /**
     * 数据库连接实例
     * 
     * @var null
     */
    public static $db = null;

    /**
     * 适配器名称
     * 
     * @var string
     */
    public static $adapterType = '';

    /**
     * 数据表前缀
     * 
     * @var string
     */
    public static $db_prefix = '';

    /**
     * 数据库初始化
     * 连接数据库，选择数据库，设置表前缀
     * 
     * @param string $host   host address
     * @param string $user   username
     * @param string $pass   password
     * @param string $dbname database name
     * @param string $prefix table prefix
     *
     * @return void
     */
    public static function setup($host = 'localhost', $user = 'root', $pass = '', $dbname = 'dolrphp', $prefix = '') 
    {
        self::$db          = Db_connectingManager::getInstance($host, $user, $pass, $dbname);
        self::$adapterType = ucfirst(strtolower(Db_connectingManager::getEnableEngine()));
        self::$db_prefix   = $prefix;

    }

    /**
     * dispense a record
     * @param string $tableName table name
     * @return object
     */
    public static function dispense($tableName = '') 
    {
        //return new
    }

    /**
     * 将数组转换成 ActiveRecord
     *
     * @return array | object
     */
    public static function convertToActiveRecord($tableName, $data) 
    {
        reset($data);
        if (is_array($data[key($data)])) { //如果第一个元素是数组则为二维
            foreach ($data as &$val) {
                $val = new Db_ActiveRecord($tableName, $val);
            }
        }

        return $data;
    }

    /**
     * 获取数据库连接对象
     * 
     * @return object
     */
    public static function getDatabase() 
    {
        return self::$db;
    }

    /**
     * 设置日志记录对象
     * 
     * @param Db_Logger $logger
     */
    public static function setLogger(Db_Logger $logger) 
    {
        self::$logger = $logger;
    }

    /**
     * 获取日志对象
     * 
     * @return Db_DefAultLogger | null | object
     */
    public static function getLogger() 
    {
        if (is_null(self::$logger))
            self::$logger = new Db_DefAultLogger();

        return self::$logger;
    }
}
