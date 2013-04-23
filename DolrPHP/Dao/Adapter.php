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
interface DB_Adapter
{

    /**
     * 获取表
     *
     * @param string $tableName table name
     *
     * @return array
     */
    function getTableMetaInfo($tableName);

    /**
     * 执行一个SQL查询
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return mixed
     */
    function exec($sql, $values = array());

    /**
     * 执行一个SQL查询
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return mixed
     */
    function query($sql, $values = array());

    /**
     * 添加记录
     *
     * @param array $data 关联数组[字段 => 值]
     *
     * @return int
     */
    function add($data);

    /**
     * 删除记录
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return bool
     */
    function del($sql = '', $values = array());

    /**
     * 获取一条记录 , getRow别名方法
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    function find($sql = '', $values = array());

    /**
     * 查询多条记录
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    function select($sql = '', $values = array());


    /**
     * 更新记录
     *
     * @param array  $data
     * @param string $sql
     * @param array  $values
     *
     * @return bool|mixed
     */
    function save($data = array(), $sql = '');

    /**
     * 查询一条记录，返回二维数组
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    function getRow($sql = '', $values = array());

    /**
     * 查询满足条件的所有
     * @param string $sql      SQL
     * @param array  $values   values to bind
     *
     * @return array
     */
    function getAll($sql = '', $values = array());

    /**
     * 查询一列值，返回一维数组
     * @param string $colName   field name
     * @param string $sql       SQL
     * @param array  $values    values to bind
     *
     * @return array
     */
    function getCol($colName, $sql = '', $values = array());

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
    function getCell($cellName, $sql = '', $values = array());

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    function getAssoc($sql = '', $values = array());

    /**
     * 得到一个索引数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    function getArray($sql = '', $values = array());

    /**
     * 关闭连接释放资源
     *
     * @param resource $handler database resource
     *
     * @return void
     */
    function close();

}// END class Db_Adapter