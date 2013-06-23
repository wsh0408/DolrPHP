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
 * PDO类
 **/
class Dao_Adapter_Pdo extends Dao_Adapter
{

    private $_affectedRows = 0;

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
            $stmt = $this->_connector->prepare($sql);
            $stmt->execute();
            $this->_affectedRows = $stmt->rowCount();
            return $stmt;
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

    protected function escape($string)
    {
        return addslashes($string);
    }

    protected function getInsertId()
    {
        return $this->_connector->lastInsertId();
    }

    protected function getAffectedRows()
    {
        return $this->_affectedRows;
    }

    public function close()
    {
        # code...
    }

} // END class Dao_Adapter_Pdo
