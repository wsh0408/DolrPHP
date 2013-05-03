<?php defined('DOLR_PATH') or exit('No direct script access.');
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     DolrPHP
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * 系统公共模型类
 *
 * @package DolrPHP
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Model
{
    public $tablePrefix = '';

    public function __construct() {
        $dbConfig = C('DB_SET');
        if (empty($dbConfig['default']['dbname'])) {
            throw new DolrException("无数据库配置");
            return false;
        }
        $writer = !empty($dbConfig['writer']) ? $dbConfig['writer'] : $dbConfig['default'];
        $reader = !empty($dbConfig['reader']) ? $dbConfig['reader'] : $dbConfig['default'];
        $this->tablePrefix = isset($dbConfig['writer']['prefix']) ? $dbConfig['writer']['prefix'] : '';
        // init DB
        Db::initialize($writer, C('DB_ENGINE'), $reader);
        //Db::setLogger(new Trace);
    }

    /**
     * 实例化一个表对象
     *
     * @param string $tableName 表名（不用带前缀）
     * @return object
     */
    public function dispense($tableName)
    {
        $tableName = strtolower(preg_replace('/(\w)([A-Z])/', '\\1_\\2', trim($tableName)));
        if (!empty($this->tablePrefix) && strpos($tableName, $this->tablePrefix) !== 0 ) {
            $tableName = $this->tablePrefix . $tableName;
        }
        return Db::dispense($tableName);
    }
} // END class Model
