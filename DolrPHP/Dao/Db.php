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

defined('DB_PATH') || define('DB_PATH', dirname(__FILE__) . '/');
/**
 * Db类
 **/
class Db
{

    /**
     * engine type
     */
    const ENGINT_PDO    = 'PDO';
    const ENGINT_MYSQLI = 'mysqli';
    const ENGINT_MYSQL  = 'mysql';
    const ENGINT_NONE   = '';

    /**
     * 数据库连接实例
     *
     * @var array
     */
    protected static $db = array(
                            'writer' => null,
                            'reader' => null,
                        );

    /**
     * 适配器对象
     *
     * @var object|resource
     */
    protected static $_adapter = null;

    /**
     * 引擎类型
     *
     * @var string
     */
    protected static $_engine = '';

    /**
     * 数据表前缀
     *
     * @var string
     */
    protected static $tablePrefix = '';

    /**
     * 日志对象
     *
     * @var object
     */
    protected static $logger = null;

    /**
     * 数据库初始化
     *
     * @param array $writerConfig config of writer
     * @param array $readerConfig config of reader
     *
     * @example
     * <pre>
     * $writerConfig|$readerConfig = array(
     *                                'host'    => 'localhost',
     *                                'user'    => 'root',
     *                                'dbname'  => 'dolrphp',
     *                                'pass'    => '123456',
     *                                'prefix'  => 'tb_',
     *                                'charset' => 'utf8',
     *                               );
     * </pre>
     *
     * @return void
     */
    public static function initialize(array $writerConfig, $engine = self::ENGINT_NONE, array $readerConfig = null)
    {
        if (self::$db['writer']) {
            return;
        }
        self::$_engine = self::getEnableEngine($engine);
        spl_autoload_register('self::_daoAutoLoader');
        try {
            //连接资源
            self::_setConnector($writerConfig, 'writer');
            if (isset($writerConfig['prefix'])) {
                self::$tablePrefix = $writerConfig['prefix'];
            }
            if (!is_array($readerConfig)) {
                self::$db['reader'] = &self::$db['writer'];
                return;
            }
            //主从分离
            self::_setConnector($readerConfig, 'reader');
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 实例化一个表
     *
     * @param string $tableName table name
     *
     * @return
     */
    public static function dispense($tableName)
    {
        $tableName = self::_getTableName($tableName);
        try {
            if (is_null(self::$_adapter)) {
                $adapter = 'DB_Adapter_' . ucfirst(self::$_engine);
                //实例化适配器
                self::$_adapter = new $adapter(self::$db['writer'], self::$db['reader']);
            }
            self::$_adapter->dispenseTable($tableName);
            return self::$_adapter;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 创建完整的表名
     *
     * @param string $tableName table name
     *
     * @return string full table name
     */
    private static function _getTableName($tableName)
    {
        if (!empty(self::$tablePrefix) && strpos($tableName, self::$tablePrefix) === 0) {
            return $tableName;
        }
        return self::$tablePrefix . $tableName;
    }


    /**
     * 设置数据库连接器
     *
     * @param array  $config  配置
     * @param string $object  设置对象(writer | reader)
     *
     * @throws Exception 无连接工具
     *
     * @return object
     */
    private static function _setConnector(array $config, $object = 'writer')
    {
        if(self::$db[$object])
            return true;
        try {
            $config['charset'] = isset($config['charset']) ? $config['charset'] : 'utf8';
            self::$db[$object] = self::_connect($config['host'], $config['user'],
                                    $config['pass'], $config['dbname'], $config['charset']);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 返回数据库连接资源
     *
     * @param string $host    主机
     * @param string $dbName  数据库名称
     * @param string $user    用户名
     * @param string $pass    密码
     * @param string $charset 字符集
     *
     * @throws Exception 无连接工具
     *
     * @return object
     */
    private static function _connect($host, $user, $pass, $dbName, $charset = 'utf8')
    {
        try {
            switch (strtolower(self::$_engine)) {
                case 'pdo': // pdo
                    return self::getPdo($host, $user, $pass, $dbName, $charset);
                    break;
                case 'mysqli': // mysqli
                    return self::getMysqli($host, $user, $pass, $dbName, $charset);
                    break;
                default:  // mysql
                    return self::getMysql($host, $user, $pass, $dbName, $charset);
                    break;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Dao autoloader
     *
     * @param string $className class name
     *
     * @return void
     */
    private static function _daoAutoLoader($className)
    {
        if (false !== stripos($className, 'Db_')) { //DB
            include DB_PATH . str_replace('_', '/', substr($className, 3)) . '.php';
        }
    }

    /**
     * 获取PDO实例
     *
     * @param string $host    主机
     * @param string $user    用户名
     * @param string $pass    密码
     * @param string $dbName  数据库名称
     * @param string $charset 字符集
     *
     * @return PDO
     */
    private static function getPdo($host, $user, $pass, $dbName, $charset)
    {
        try {
            $dsn = self::_createDSN($host, $dbName);
            $initCommond = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$charset);
            $pdo = new PDO($dsn, $user, $pass, $initCommond);
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            return $pdo;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取Mysqli实例
     *
     * @param string $host    主机
     * @param string $dbName  数据库名称
     * @param string $user    用户名
     * @param string $pass    密码
     * @param string $charset 字符集
     *
     * @return Mysqli
     */
    private static function getMysqli($host, $user, $pass, $dbName, $charset)
    {
        try {
            if (!extension_loaded('mysqlnd')) {
                throw new Exception("使用Mysqli连接方式需要启用'mysqlnd'拓展");
                return false;
            }
            $mysqli = new Mysqli($host, $user, $pass, $dbName);
            $mysqli->set_charset($charset);
            return $mysqli;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取Mysql连接对象
     *
     * @param string $host    主机
     * @param string $dbName  数据库名称
     * @param string $user    用户名
     * @param string $pass    密码
     * @param string $charset 字符集
     *
     * @return Mysqli
     */
    private static function getMysql($host, $user, $pass, $dbName, $charset)
    {
        try {
            $mysql = mysql_connect($host, $user, $pass);
            mysql_select_db($dbName, $mysql);
            mysql_query('SET NAMES '.$charset, $mysql);
            return $mysql;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 生成DSN
     * 单独做成方法的原因是为了以后拓展
     *
     * @param string $host   host address
     * @param string $dbName database name
     *
     * @return string
     */
    private static function _createDSN($host, $dbName)
    {
        return "mysql:host={$host};dbname={$dbName}";
    }

    /**
     * 获取可用的数据库引擎
     *
     * @param string $engine engine type
     *
     * @return bool|string
     */
    public static function getEnableEngine($engine)
    {
        if (!empty($engine)) {
            return $engine;
        }
        if (extension_loaded('PDO')) {
            return 'pdo';
        } else if (extension_loaded('mysqli')) {
            return 'mysqli';
        } else if (extension_loaded('mysql')) {
            return 'mysql';
        } else {
            throw new Exception('没有可用的数据库连接工具');
        }
    }

    /**
     * 将数组转换成 ActiveRecord
     *
     * @return array | object
     */
    public static function toAR($tableName, $data)
    {
        reset($data);
        if (is_array($data)) { //如果第一个元素是数组则为二维
            foreach ($data as &$val) {
                $val = new Db_ActiveRecord($tableName, $val);
            }
        }

        return $data;
    }

}
