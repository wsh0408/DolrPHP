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
 * 数据库连接管理器
 **/
class Db_connectingManager
{
    /**
     * 数据库连接
     * @var array
     */
    static $db = array();

    /**
     * 获取连接对象实例
     * @throws Exception 没有可用的数据库连接工具
     * @return Object
     */
    static function getInstance($host, $user, $pass, $dbname) {
        if (empty(self::$db))
            self::connect($host, $user, $pass, $dbname);
        if (isset(self::$db['pdo'])) {
            return self::$db['pdo'];
        } else if (isset(self::$db['mysqli'])) {
            return self::$db['mysqli'];
        } elseif (isset(self::$db['mysql'])) {
            return self::$db['mysql'];
        } else
            throw new Exception('没有可用的数据库连接工具');
    }

    /**
     * 设置数据库配置
     * @param  string $host   主机
     * @param  string $dbName 数据库名称
     * @param  string $user   用户名
     * @param  string $pass   密码
     * @throws Exception 无连接工具
     * @return  object
     */
    static public function connect($host, $user, $pass, $dbName) {
        $enableEngine = self::getEnableEngine();
        if (!$enableEngine) {
            throw new Exception('没有可用的数据库连接工具');

            return;
        }
        try {
            switch (strtolower($enableEngine)) {
                case 'pdo':
                    if (!isset(self::$db['pdo']) or is_null(self::$db['pdo'])) {
                        $dsn             = self::getDSN($host, $dbName);
                        self::$db['pdo'] = new PDO($dsn, $user, $pass);
                        self::$db['pdo']->setAttribute(1002, 'SET NAMES utf8');
                        self::$db['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        self::$db['pdo']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        self::$db['pdo']->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, TRUE);
                    }

                    return self::$db['pdo'];
                    break;
                case 'mysqli':
                    if (!isset(self::$db['mysqli']) or is_null(self::$db['mysqli'])) {
                        self::$db['mysqli'] = new Mysqli($host, $user, $pass, $dbName);
                        self::$db['mysqli']->set_charset('utf8');
                    }

                    return self::$db['mysqli'];
                    break;
                default: //mysql
                    if (!isset(self::$db['mysql']) or is_null(self::$db['mysql'])) {
                        self::$db['mysql'] = mysql_connect($host, $user, $pass);
                        mysql_select_db($dbName);
                        mysql_query('SET NAMES UTF8');
                    }

                    return self::$db['mysql'];
                    break;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 生成DSN
     * 单独做成方法的原因是为了以后拓展
     *
     * @param $host
     * @param $dbName
     * @return string
     */
    static public function getDSN($host, $dbName) {
        return "mysql:host={$host};dbname={$dbName}";
    }

    /**
     * 获取可用的数据库引擎
     *
     * @return bool|string
     */
    static public function getEnableEngine() {
        /* if (class_exists('PDO')) {
             return 'PDO';
         } else if (class_exists('Mysqli')) {
             return 'Mysqli';
         } else */
        if (extension_loaded('mysql')) {
            return 'mysql';
        } else {
            return FALSE;
        }
    }

} // END class Db_connectingManager
