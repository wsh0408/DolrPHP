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
     * @param string $sql       SQL
     * @param array  $params    values to bind
     * @param PDO    &$connector connector
     *
     * @return mixed
     */
    public function exec($sql, array $params = array())
    {
        try {
            $stmt = $this->_connector->prepare($sql);
            if (!empty($params)) {
                $this->_bindParams($stmt, $params);
            }
            $stmt->execute();
            $this->_lastInsertId = $stmt->insert_id;
            return $this->stmt = $stmt;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    protected function _bindParams($stmt, $params)
    {
        $types = str_repeat('s', count($params));
        foreach ($params as $key => &$value) {
            $value = &$value;
        }
        array_unshift($params, $types);
        call_user_func_array(array($stmt,'bind_param'), $params);
    }

    protected function fetchArray($stmt)
    {
        return $this->_fetchResult($stmt,'array');
    }

    protected function fetchNum($stmt)
    {
        return $this->_fetchResult($stmt,'num');
    }

    protected function fetchAssoc($stmt)
    {
        return $this->_fetchResult($stmt,'assoc');
    }

    protected function fetchObject($stmt)
    {
        return $this->_fetchResult($stmt,'object');
    }

    protected function _fetchResult($stmt, $fetchStyle)
    {
        if (!method_exists($stmt, 'get_result')) {
            return false;
        }
        $result = $stmt->get_result();
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
        return $this->stmt->affected_rows;
    }

    public function close()
    {
        # code...
    }

} // END class Db_Adapter_Pdo
