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
    public function __construct($tableName = '') {
        $dbConfig = C('DB_SET');
        if (empty($dbConfig['default']['name'])) {
            throw new DolrException("无数据库配置");
            return false;
        }
        $writer = !empty($dbConfig['writer']) ? $dbConfig['writer'] : $dbConfig['default'];
        $reader = !empty($dbConfig['reader']) ? $dbConfig['reader'] : $dbConfig['default'];
        // init DB
        Db::initialize($writer, $reader);
        Db::setLogger(new Trace);
        if (empty($tableName)) { // 如果用户直接 new MemberModel()的话，得取文件名为表名
            $className = get_class($this);
            $tableName = substr($className, 0, -strlen(C('MODEL_IDENTITY')));
        }

        $tableName = strtolower(preg_replace('/(\w)([A-Z])/', '\\1_\\2', $tableName));
        $tableName = strpos($tableName, $prefix) === FALSE ? $prefix . $tableName : $tableName;
        return Db::dispense($tableName);
    }
} // END class Model
