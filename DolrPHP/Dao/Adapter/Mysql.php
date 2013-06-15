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
     * @param string $sql SQL
     *
     * @return mixed
     */
    public function exec($sql)
    {
        try {
            return mysql_query($sql);
        } catch (PDOException $e) {
            throw $e;
        }
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

    protected function escape($string)
    {
        return mysql_real_escape_string($string);
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
