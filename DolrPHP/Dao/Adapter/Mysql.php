<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     Db.Adapter
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * DB Mysql类
 **/
class Db_Adapter_Mysql extends Db_Adapter
{

    /**
     * 执行一个SQL查询,返回结果集
     *
     * @param string $sql       SQL
     * @param array  $params    values to bind
     * @param PDO    &$connector connector
     *
     * @return mixed
     */
    public function exec($sql, array $params = array())
    {
        try {
            $sql = $this->_buildSql($sql, $params);
            return mysql_query($sql);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    protected function _buildSql($sql, array $params)
    {
        $params = array_map('mysql_real_escape_string', array_values($params));
        $sqlFormat = str_replace('?', "'%s'", $sql);
        array_unshift($params, $sqlFormat);
        return $this->_lastSql = call_user_func_array('sprintf', $params);
    }

    protected function fetchArray($resource)
    {
        return $this->_fetchResult($resource,'array');
    }

    protected function fetchNum($resource)
    {
        return $this->_fetchResult($resource,'row');
    }

    protected function fetchAssoc($resource)
    {
        return $this->_fetchResult($resource,'assoc');
    }

    protected function fetchObject($resource)
    {
        return $this->_fetchResult($resource,'object');
    }

    protected function _fetchResult($resource, $fetchStyle)
    {
        $fetchFunc = 'mysql_fetch_' . strtolower($fetchStyle);
        if (!function_exists($fetchFunc)) {
            return false;
        }
        $arr = array();
        while ($row = $fetchFunc($resource)) {
            $arr[] = $row;
        }

        return $arr;
    }

    protected function getInsertId()
    {
        return mysql_insert_id();
    }

    protected function getAffectedRows()
    {
        return mysql_affected_rows();
    }

    public function close()
    {
        # code...
    }

} // END class Db_Adapter_Pdo
