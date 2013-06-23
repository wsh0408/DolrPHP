<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     Dao
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

defined('DAO_PATH') || define('DAO_PATH', dirname(__FILE__) . '/');
/**
 * Dao类
 **/
class Dao
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
    protected static $_tablePrefix = '';

    /**
     * 日志对象
     *
     * @var object
     */
    protected static $_logs = '';

    protected static $_configSample = array(
                                        'host'    => 'localhost', //数据库主机,
                                        'dbname'  => 'dolrphp', //数据库名称,
                                        'user'    => 'root',    //用户名
                                        'pass'    => '',    //密码
                                        'prefix'  => '',    //数据表前缀
                                        'charset' => 'utf8',//字符集
                                        );

    /**
     * 数据库初始化
     *
     * @param array  $dbConfig 数据库配置
     * @param string $engine   引擎（PDO|Mysqli|Mysql）
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
    public static function initialize(array $dbConfig, $engine = 'PDO')
    {
        if (empty($dbConfig['default']['dbname']) && empty($dbConfig['writer']['dbname'])) {
            throw new Exception("无数据库配置");
        }
        $writer = !empty($dbConfig['writer']) ? $dbConfig['writer'] : $dbConfig['default'];
        $reader = !empty($dbConfig['reader']) ? $dbConfig['reader'] : $dbConfig['default'];
        if (self::$db['writer']) {
            return;
        }
        self::$_engine = self::getEnableEngine($engine);
        spl_autoload_register('self::_daoAutoLoader');
        try {
            //连接资源
            self::_setConnector($writer, 'writer');
            if (isset($writer['prefix'])) {
                self::$_tablePrefix = $writer['prefix'];
            }
            if (!is_array($reader)) {
                self::$db['reader'] = &self::$db['writer'];
                return;
            }
            //主从分离
            self::_setConnector($reader, 'reader');
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取adapter对象
     *
     * @return
     */
    public static function getAdapter()
    {
        if (is_null(self::$_engine)) {
            throw new Exception("请先初始化数据库配置：Dao::initialize()");
        }
        try {
            if (is_null(self::$_adapter)) {
                $adapter = 'Dao_Adapter_' . ucfirst(strtolower(self::$_engine));
                if (!class_exists($adapter)) {
                    throw new Exception("适配器'{$adapter}'不存在!", 1);
                }
                //实例化适配器
                self::$_adapter = new $adapter(self::$db['writer'], self::$db['reader'], self::$_tablePrefix);
            }
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
        $tableName = strtolower(preg_replace('/(\w)([A-Z])/', '\\1_\\2', trim($tableName)));
        if (!empty(self::$_tablePrefix) && strpos($tableName, self::$_tablePrefix) === 0) {
            return $tableName;
        }
        return self::$_tablePrefix . $tableName;
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
            $config = array_merge(self::$_configSample, $config);
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
        if (false !== stripos($className, 'Dao_')) { //DB
            include DAO_PATH . str_replace('_', '/', substr($className, 3)) . '.php';
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
    public static function getPdo($host, $user, $pass, $dbName, $charset)
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
    public static function getMysqli($host, $user, $pass, $dbName, $charset)
    {
        try {
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
    public static function getMysql($host, $user, $pass, $dbName, $charset)
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

}
