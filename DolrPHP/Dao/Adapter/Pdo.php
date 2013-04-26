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
     * @param string $sql    SQL
     * @param array  $values values to bind
     * @param PDO    $values connector 
     *
     * @return mixed
     */
    public function exec($sql, $values = array(), $connector)
    {
        try {
            $stmt = $connector->prepare($sql);
            $stmt->execute($values);
            return $stmt;
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function fetchArray($stmt)
    {
        return $stmt->fetch(PDO::FETCH_BOTH);
    }

    protected function fetchNum($stmt)
    {
        return $stmt->fetch(PDO::FETCH_NUM);
    }

    protected function fetchAssoc($stmt)
    {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function fetchObject($stmt)
    {
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * 关闭连接释放资源
     *
     * @param resource $handler database resource
     *
     * @return void
     */
    public function close()
    {
        # code...
    }

} // END class Db_Adapter_Pdo
