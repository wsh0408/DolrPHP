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
    //是否初始化过DB配置
    private static $_isInitialized = false;

    public function __construct() {
        if (!self::$_isInitialized) {
            Dao::initialize(Config::get('DB_SET'), Config::get('DB_ENGINE'));
            self::$_isInitialized = true;
        }
    }

    /**
     * 实例化一个表对象
     *
     * @param string $tableName 表名（不用带前缀）
     * @return object
     */
    protected function dispense($tableName)
    {
        return Dao::getAdapter()->dispense($tableName);
    }
} // END class Model
