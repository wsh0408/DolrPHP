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
 * DB Mysqli类
 **/
class Db_Adapter_Mysqli extends Db_Adapter
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
            $this->_lastInsertId = $res->insert_id;
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
        if (!method_exists($res, 'get_result')) {
            return false;
        }
        $result = $res->get_result();
        $fetchFunc = 'fetch_' . strtolower($fetchStyle);
        if (!method_exists($result, $fetchFunc)) {
            return false;
        }
        $arr = array();
        while ($row = $result->$fetchFunc()) {
            $arr[] = $row;
        }

        return $arr;
    }

    protected function getInsertId()
    {
        return $this->lastInsertId;
    }

    protected function getAffectedRows()
    {
        return $this->res->affected_rows;
    }

    public function close()
    {
        # code...
    }

} // END class Db_Adapter_Pdo
