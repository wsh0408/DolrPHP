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
 * DB PDO类
 **/
class Db_Adapter_Pdo extends Db_Adapter
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
            if (empty($params)) {
                $stmt->execute();
            } else {
                $stmt->execute(array_values($params));
            }
            return $this->stmt = $stmt;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    protected function fetchArray($stmt)
    {
        return $this->_fetchResult($stmt,PDO::FETCH_BOTH);
    }

    protected function fetchNum($stmt)
    {
        return $this->_fetchResult($stmt,PDO::FETCH_NUM);
    }

    protected function fetchAssoc($stmt)
    {
        return $this->_fetchResult($stmt,PDO::FETCH_ASSOC);
    }

    protected function fetchObject($stmt)
    {
        return $this->_fetchResult($stmt,PDO::FETCH_OBJ);
    }

    protected function _fetchResult($stmt, $fetchStyle = PDO::FETCH_ASSOC)
    {
        $arr = array();
        while ($row = $stmt->fetch($fetchStyle)) {
            $arr[] = $row;
        }

        return $arr;
    }

    protected function getInsertId()
    {
        return $this->_connector->lastInsertId();
    }

    protected function getAffectedRows()
    {
        return $this->stmt->rowCount();
    }

    public function close()
    {
        # code...
    }

} // END class Db_Adapter_Pdo
