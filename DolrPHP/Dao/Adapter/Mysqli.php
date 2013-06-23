<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     Dao.Adapter
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * Mysqli类
 **/
class Dao_Adapter_Mysqli extends Dao_Adapter
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
            $res = $this->_connector->query($sql);
            return $res;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    protected function fetchArray($res)
    {
        return $this->_fetchResult($res,'array');
    }

    protected function fetchNum($res)
    {
        return $this->_fetchResult($res,'num');
    }

    protected function fetchAssoc($res)
    {
        return $this->_fetchResult($res,'assoc');
    }

    protected function fetchObject($res)
    {
        return $this->_fetchResult($res,'object');
    }

    protected function _fetchResult($res, $fetchStyle)
    {
        $fetchFunc = 'fetch_' . strtolower($fetchStyle);
        if (!method_exists($res, $fetchFunc)) {
            return false;
        }
        $arr = array();
        while ($row = $res->$fetchFunc()) {
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
        return $this->_connector->insert_id;
    }

    protected function getAffectedRows()
    {
        return $this->_connector->affected_rows;
    }

    public function close()
    {
        # code...
    }

} // END class Dao_Adapter_Pdo
