<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     Db
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * 适配器基类
 **/
abstract class DB_Adapter
{

    /**
     * 获取表
     *
     * @param string $tableName table name
     *
     * @return array
     */
    public function getTableInfo($tableName);

    /**
     * 获取最后执行的SQL
     *
     * @return string SQL
     */
    public function getSQL();

    /**
     * 执行一个SQL查询
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return mixed
     */
    public function exec($sql, $values = array());

    /**
     * 执行一个SQL查询
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return mixed
     */
    public function query($sql, $values = array());

    /**
     * 添加记录
     *
     * @param array $data 关联数组[字段 => 值]
     *
     * @return int
     */
    public function add($data);

    /**
     * 删除记录
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return bool
     */
    public function del($sql = '', $values = array());

    /**
     * 获取一条记录 , getRow别名方法
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    public function find($sql = '1 = 1', $values = array());

    /**
     * 查询多条记录
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    public function select($sql = '1 = 1', $values = array());


    /**
     * 更新记录
     *
     * @param array  $data
     * @param string $sql
     * @param array  $values
     *
     * @return bool|mixed
     */
    public function save($data = array(), $sql = '1 = 1');

    /**
     * 查询一条记录，返回二维数组
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    public function getRow($sql = '1 = 1', $values = array());

    /**
     * 查询满足条件的所有
     * @param string $sql      SQL
     * @param array  $values   values to bind
     *
     * @return array
     */
    public function getAll($sql = '1 = 1', $values = array());

    /**
     * 查询一列值，返回一维数组
     * @param string $colName   field name
     * @param string $sql       SQL
     * @param array  $values    values to bind
     *
     * @return array
     */
    public function getCol($colName, $sql = '1 = 1', $values = array());

    /**
     * 查询一条记录中单个字段的值
     * 此方法会返回一条记录中一个字段的值，常用于查询一个具体的值
     * 比如查询用户表里id = 1 的用户名（username），将会返回一个具体的string 值
     *
     * @param string $cellName cell name
     * @param string $sql      SQL
     * @param array  $values   values to bind
     *
     * @return string $singleValue value from cell
     */
    public function getCell($cellName, $sql = '1 = 1', $values = array());

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    public function getAssoc($sql = '1 = 1', $values = array());

    /**
     * 得到一个索引数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    public function getArray($sql = '1 = 1', $values = array());

}// END class Db_Adapter