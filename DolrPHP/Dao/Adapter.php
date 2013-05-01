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
     * FETCH TYPE
     */
    const FETCH_TYPE_ARRAY  = 'array';
    const FETCH_TYPE_NUM    = 'num';
    const FETCH_TYPE_ASSOC  = 'assoc';
    const FETCH_TYPE_OBJECT = 'object';

    /**
     * SQL TYPE
     */
    const SQL_TYPE_INSERT = 'insert';
    const SQL_TYPE_DELETE = 'delete';
    const SQL_TYPE_UPDATE = 'update';
    const SQL_TYPE_SELECT = 'select';

    /**
     * log type
     */
    const LOG_TYPE_SQL = 'sql';
    const LOG_TYPE_ERROR = 'error';

    /**
     * adapter type
     */
    const WRITER = 'writer';
    const READER = 'reader';

    /**
     * writer
     *
     * @var resource
     */
    protected $_writer;


     /**
     * reader
     *
     * @var resource
     */
    protected $_reader;

    /**
     * 连接器
     *
     * @var object
     */
    protected $_connector;

    /**
     * 表结构
     *
     * @var array
     */
    protected $_tableMeta;


    /**
     * 最后插入的数据ID
     *
     * @var integer
     **/
    protected $_lastInsertId;

    /**
     * 上次查询的SQL
     *
     * @var string
     */
    protected $_lastSql;


    /**
     * construtor
     *
     * @param PDO $writer pdo instance to write data
     * @param PDO $reader pdo instance to read data
     */
    public function __construct($writer, $reader = null)
    {
        if (!is_null($this->_writer)) {
            return;
        }
        $this->_writer = $writer;
        if ($reader) {
            $this->_reader = $reader;
        } else {
            $this->_reader = $writer;
        }
    }

    /**
     * 实例化一个表
     *
     * @param string $tableName table name
     *
     * @return
     */
    public function dispenseTable($tableName)
    {
        $tableMeta = $this->_getTableMetaInfo($tableName);
        if (!$tableMeta || empty($tableMeta)) {
            throw new Exception("数据表 '{$tableName}' 读取失败", 1);
        }
        $this->_tableMeta = $tableMeta;

        return $this;
    }

    /**
     * 获取表
     *
     * @param string $tableName table name
     *
     * @return array
     */
    protected function _getTableMetaInfo($tableName)
    {
        $tableInfo = $this->query("SHOW COLUMNS FROM `$tableName`");
        $data = array();
        $data['_name']   = $tableName;
        $data['_fields'] = array();
        if (empty($tableInfo)) {
            $this->_log('查询错误: 表"' . $tableName . '"不存在', self::LOG_TYPE_ERROR);
            return false;
        }
        foreach ($tableInfo as $value) {
            $data['_fields'][] = $value['Field'];
            if ($value['Key'] == 'PRI') {
                $data['_pk'] = $value['Field'];
            }
        }
        // 没有主键的话默认第一个字段为主键，谁让你建个表这么不科学！
        if (!isset($data['pk'])) {
            reset($data['_fields']);
            $key = key($data['_fields']);
            $data['_pk'] = $data['_fields'][$key];
        }

        return $data;
    }

    /**
     * 过滤输入数据
     *
     * @param array  $data   input data
     * @param string $action insert|update
     *
     * @return array
     */
    protected function _filterFields($data, $action = 'insert')
    {
        if (empty($this->_tableMeta)) {
            throw new Exception("未选择目标数据表[{$action}]", 1);
        }
        //清空values
        $output = array();
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->_tableMeta['_fields'])) {
                continue;
            }
            $output[$key] = $value;
        }

        return $output;
    }

    /**
     * 执行一个完整SQL查询
     *
     * @param string $sql       SQL
     * @param array  $params    values to bind
     * @param array  $fetchType fetch type (assoc|num|array|object)
     *
     * @return mixed
     */
    public function query($sql, array $params = array(), $fetchType = self::FETCH_TYPE_ASSOC)
    {
        $sqlType = preg_match('/([a-z]+)\s+/i', $sql, $matches);
        $sql = rtrim($sql, ';');
        switch (strtoupper($matches[1])) {
            case 'INSERT':
                $this->_connector = &$this->_writer;
                $this->exec($sql, $params);
                $ret = $this->getInsertId();
                break;
            case 'DELETE':
            case 'UPDATE':
                $this->_connector = &$this->_writer;
                $res = $this->exec($sql, $params);
                $ret = $this->getAffectedRows($res);
                break;
            default:
                $this->_connector = &$this->_reader;
                $res = $this->exec($sql, $params);
                $ret = $this->fetch($res, $fetchType);
                break;
        }
        $this->_setLastSql($sql, $params);

        return $ret;
    }

    /**
     * 设置最后一次查询的SQL
     *
     * @param string $sql    SQL
     * @param array  $params values to bind
     *
     * @return void
     */
    protected function _setLastSql($sql, $params)
    {
        // 拼装一个完整的SQL用于调试
        foreach ($params as $key => &$value) {
            if (!is_numeric($value) && mb_strlen($value) > 10) {
                $value = addcslashes(mb_substr($value, 0, 10), "'");
                $value = "{$value}...";//不用显示全部
            }
            $value = "'{$value}'";
        }
        $sqlFormat = str_replace('?', "%s", $sql);
        array_unshift($params, $sqlFormat);
        $this->_lastSql = call_user_func_array('sprintf', $params);
    }

    /**
     * 提取结果集
     *
     * @param resource $resource  query resource
     * @param string   $fetchType fetch type [array, num, assoc, object]
     *
     * @return array or boolean
     */
    public function fetch($resource, $fetchType)
    {
        if (!is_resource($resource) && !is_object($resource)) {
            return $resource;
        }
        switch ($fetchType) {
            case self::FETCH_TYPE_ASSOC:
                $res = $this->fetchAssoc($resource);
                break;
            case self::FETCH_TYPE_NUM:
                $res = $this->fetchNum($resource);
                break;
            case self::FETCH_TYPE_OBJECT:
                $res = $this->fetchObject($resource);
                break;
            case self::FETCH_TYPE_ARRAY:
                $res = $this->fetchArray($resource);
                break;
            default:
                $res = false;
                break;
        }
        if (false === $res) {
            return false;
        }

        return $res;
    }

    /**
     * 提取数组中的第一个元素
     *
     * @param array $array 结果数组
     * @return mixed
     */
    public function fetchOne($array)
    {
        if (empty($array)) {
            return $array;
        }
        return array_shift($array);
    }

    /**
     * 添加记录
     *
     * @param array $data 关联数组[字段 => 值]
     *
     * @example
     * <pre>
     * $data = array('username' => 'hello', 'password' => '236a6');
     * $insertId = $dbObj->add($data);
     * </pre>
     *
     * @return int
     */
    public function add(array $data)
    {
        $sql = $this->_createSql(self::SQL_TYPE_INSERT, '', $data);
        $res = $this->query($sql,$data);
        if (!$res) {
            return false;
        }
        return $this->_lastInsertId = $res;
    }

    /**
     * 删除记录
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return bool
     */
    public function del($sql, array $values = array())
    {
        $sql = $this->_createSql(self::SQL_TYPE_DELETE, $sql, $values);
        return $this->query($sql, $values);
    }

    /**
     * 获取一条记录 , getRow别名方法
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     * @param array  $fetchType fetch type (assoc|num|array|object)
     * @return array
     */
    public function find($sql = '', array $values = array(), $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql);
        $sql .= " LIMIT 1";
        return $this->fetchOne($this->query($sql, $values, $fetchStyle));
    }

    /**
     * 查询多条记录
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     * @param array  $fetchType fetch type (assoc|num|array|object)
     * @return array
     */
    public function select($sql = '', array $values = array(), $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql);
        return $this->query($sql, $values, $fetchStyle);
    }


    /**
     * 更新记录
     *
     * @param array  $data
     * @param string $sql
     * @param array  $values
     *
     * @return bool|mixed
     */
    public function save(array $data, $sql = '')
    {
        $data = $this->_filterFields($data);
        $sql = $this->_createSql(self::SQL_TYPE_UPDATE, $sql, $data);
        $res = $this->query($sql,$data);
        if (false === $res) {
            return false;
        }

        return $res;
    }

    /**
     * 查询一条记录，返回二维数组
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array
     */
    public function getRow($sql = '', array $values = array())
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values);
        $sql .= " LIMIT 1";
        return $this->fetchOne($this->query($sql, $values));
    }

    /**
     * 查询满足条件的所有
     * @param string $sql      SQL
     * @param array  $values   values to bind
     *
     * @return array
     */
    public function getAll($sql = '', array $values = array(), $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values);
        return $this->query($sql, $values, $fetchStyle);
    }

    /**
     * 查询一列值，返回一维数组
     *
     * @param string  $colName      field name
     * @param boolean $convertTo2D  trans the result to Two-dimension array
     * @param string  $sql          SQL
     * @param array   $values       values to bind
     *
     * @example
     * <pre>
     *  if set $converTo2D false(default)
     *  for example $colName = 'user';
     *  output like below:
     *  array(
     *      0 => array('user' => 'userA'),
     *      1 => array('user' => 'userB'),
     *      2 => array('user' => 'userC'),
     *      3 => array('user' => 'userD'),
     *     );
     *  else if $convetTo2D is true:
     *  array('userA', 'userB', 'userC', 'userD',);
     * </pre>
     * @return array
     */
    public function getCol($colName, $convertTo2D = false, $sql = '', array $values = array())
    {
        if (!in_array($colName, $this->_tableMeta['_fields'])) {
            $this->_log("表'{$this->_tableMeta['_name']}'不存在字段'{$colName}'");
            return false;
        }
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values);
        $result = $this->query($sql, $values);
        $cols = array();
        $cols2D = array();
        foreach ($result as $value) {
            if (isset($value[$colName])) {
                $cols[][$colName] = $value[$colName];
                $cols2D[] = $value[$colName];
            }
        }

        return $convertTo2D ? $cols2D : $cols;
    }

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
    public function getCell($cellName, $sql = '', array $values = array())
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values, "`$cellName`");
        $sql .= "LIMIT 1";
        $res = $this->fetchOne($this->query($sql, $values));
        if ($res && isset($res[$cellName])) {
            return $res[$cellName];
        }

        return false;
    }

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    public function getAssoc($sql = '', array $values = array())
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values);
        $sql .= "LIMIT 1";
        return $this->fetchOne($this->query($sql, $values, self::FETCH_TYPE_ASSOC));
    }

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    public function getObject($sql = '', array $values = array())
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values);
        $sql .= "LIMIT 1";
        return $this->fetchOne($this->query($sql, $values, self::FETCH_TYPE_OBJECT));
    }

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于多条记录，单条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    public function getObjects($sql = '', array $values = array())
    {
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values);
        return $this->query($sql, $values, self::FETCH_TYPE_OBJECT);
    }

    /**
     * 查询总条数
     *
     * @param string $field  field name
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return int amount of records
     */
    public function getCount($field = '', $sql = '', array $values = array())
    {
        if (empty($field) || !is_array($field, $this->_tableMeta['_fields'])) {
            $field = $this->_tableMeta['_pk'];
        }
        $sql = $this->_createSql(self::SQL_TYPE_SELECT, $sql, $values, "COUNT(`{$field}`) AS `count`");
        $sql .= "LIMIT 1";
        $res = $this->fetchOne($this->query($sql, $values));
        if ($res) {
            return $res['count'];
        }
    }

    /**
     * 返回完整的SQL语句
     *
     * @param  string $sqlType   sql type
     * @param  string $sql       sql width '?'
     * @param  array  $data      data to bind
     * @param  array  $fieldArea field set,default is *
     * @return string
     */
    protected function _createSql($sqlType, $sql, $data = array(), $fieldArea = '*')
    {
        if (!empty($data)) {
            $data = $this->_filterFields($data);
            $fields = array_keys($data);
        }
        switch ($sqlType) {
            case self::SQL_TYPE_SELECT:
                $sql = "SELECT {$fieldArea} FROM `{$this->_tableMeta['_name']}` {$sql} ";
                break;
            case self::SQL_TYPE_DELETE:
                $sql = "DELETE FROM `{$this->_tableMeta['_name']}` {$sql} ";
                break;
            case self::SQL_TYPE_UPDATE:
                $arr = array();
                foreach ($fields as $field) {
                    $arr[] = "`{$field}` = ? ";
                }
                $setString = join(',', $arr);
                $sql = "UPDATE `{$this->_tableMeta['_name']}` SET {$setString} {$sql} ";
                break;
            case self::SQL_TYPE_INSERT:
                $keys = join(',', $fields);
                $valuesFlag = join(',', array_fill(0, count($fields), '?'));
                $sql = "INSERT INTO `{$this->_tableMeta['_name']}`({$keys}) VALUES({$valuesFlag}) ";
                break;
            default:
                break;
        }

        return $sql;
    }

    /**
     * 属性名称
     *
     * @param string $proName property name
     * @return mixed
     */
    public function __get($proName)
    {
        switch (strtolower($proName)) {
            case 'lastsql':
                return $this->_lastSql;
                break;
            case 'insertid':
                return $this->_lastInsertId;
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * 魔术方法
     * 只适用于单条记录
     * getBy+首字母大写的字段名
     *
     * @example
     * <pre>
     * $obj->getByUsername('admin')
     * $obj->getById(5)
     * $obj->getByFIldName('hello');
     * ...
     * </pre>
     *
     * @param string $methodName 字段名
     * @param mx  $
     *
     * @return mixed
     */
    public function __call($methodName, $args)
    {
        //实现假继承
        if (method_exists($this, $methodName)) {
            return call_user_func_array(array($this, $methodName), $args);
        }

        //getByUsername
        if (false === strpos($methodName, 'getBy') or empty($args)) {
            return false;
        }

        //取字段名:getByUserName =>user_name,getByPassword => password
        $field = strtolower(preg_replace('/(\w)([A-Z])/', '\\1_\\2', substr($methodName, 5)));
        $sql   = "WHERE `{$field}` = ?";
        $value = array_shift($args);
        return $this->getRow($sql, array($value));
    }

    /**
     * log
     *
     * @param string $string log info
     * @param string $type   error | sql
     * @return void
     */
    protected function _log($string, $type = 'error')
    {
        error_log($string);
    }

    abstract protected function fetchArray($resource);
    abstract protected function fetchNum($resource);
    abstract protected function fetchAssoc($resource);
    abstract protected function fetchObject($resource);
    abstract protected function exec($sql, array $values = array());
    abstract protected function getInsertId();
    abstract protected function getAffectedRows();
    abstract protected function close();

}// END class Db_Adapter