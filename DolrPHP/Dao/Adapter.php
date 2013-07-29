<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     Dao
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
abstract class Dao_Adapter
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
    const SQL_TYPE_HAS    = 'has';

    /**
     * log type
     */
    const LOG_TYPE_SQL   = 'sql';
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
     * 表前缀
     *
     * @var string
     */
    protected $tablePrefix;

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
     * 影响的行数
     *
     * @var integer
     */
    protected $_affectdRows = 0;

    /**
     * 上次查询的SQL
     *
     * @var string
     */
    protected $_lastSql;

    /**
     * SQL 结构（用于连贯操作）
     *
     * @var array
     */
    protected $_sqlStructure = array();
    /**
     * 用于insert或者update的数据
     *
     * @var array
     */
    public $_data = array();


    /**
     * construtor
     *
     * @param PDO $writer      pdo instance to write data
     * @param PDO $reader      pdo instance to read data
     * @param PDO $tablePrefix prefix of data table
     *
     */
    public function __construct($writer, $reader = null, $tablePrefix = '')
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
        $this->tablePrefix = $tablePrefix;
        $this->_sqlStructure = $this->_resetSqlStructure();
    }

    /**
     * 实例化一个表
     *
     * @param string $tableName table name
     *
     * @return
     */
    public function dispense($tableName)
    {
        if (strpos($tableName, $this->tablePrefix) === false) {
            $tableName = $this->tablePrefix . $tableName;
        }
        $tableMeta = $this->_getTableMetaInfo($tableName);
        if (!$tableMeta || empty($tableMeta)) {
            throw new Exception("数据表 '{$tableName}' 不存在或读取失败", 1);
        }
        $this->_tableMeta = $tableMeta;

        return $this;
    }

    /**
     * 执行一个完整SQL查询
     *
     * @param string $sql       SQL
     * @param array  $fetchType fetch type (assoc|num|array|object)
     *
     * @return mixed
     */
    public function query($sql, $fetchType = self::FETCH_TYPE_ASSOC)
    {
        if (empty($sql)) {
            throw new Exception("empty sql");
        }
        preg_match('/^([a-z]+)\s+/i', $sql, $matches);
        $sql = rtrim($sql, ';');
        $this->_connector = &$this->_writer;
        switch (strtoupper($matches[1])) {
            case 'INSERT':
                $res = $this->exec($sql);
                $ret = $this->getInsertId();
                break;
            case 'DELETE':
            case 'UPDATE':
                $res = $this->exec($sql);
                $ret = $this->getAffectedRows($res);
                break;
            default:
                $this->_connector = &$this->_reader;
                $res = $this->exec($sql);
                $ret = $this->_fetch($res, $fetchType);
                break;
        }
        $this->_lastSql = $sql;
        $this->_affectdRows = $this->getAffectedRows($res);
        $this->_sqlStructure = $this->_resetSqlStructure();
        $this->_log('[rows:' . $this->_affectdRows . ']' . $sql, self::LOG_TYPE_SQL);
        return $ret;
    }

    /**
     * 添加记录
     *
     * @param array   $data        关联数组[字段 => 值]
     * @param boolean $multiInsert 多条插入
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
        if (empty($data)) {
            return false;
        }
        $this->_data = $this->_filterData($data);
        $sql = $this->_getSql(self::SQL_TYPE_INSERT);
        $res = $this->query($sql);
        if (!$res) {
            return false;
        }
        return $this->_lastInsertId = $res;
    }

    /**
     * 删除记录
     *
     * @param string $condition  'where' of sql
     *
     * @return bool
     */
    public function del($condition = '')
    {
        if (!empty($condition)) {
            $this->_sqlStructure['WHERE'] = $condition;
        }
        $sql = $this->_getSql(self::SQL_TYPE_DELETE);
        return $this->query($sql);
    }

    /**
     * 判断一条记录存在与否
     *
     * @param string/array $condition  'where' of sql
     *
     * @return array
     */
    public function has($condition)
    {
        $this->_sqlStructure['LIMIT'] = '1';
        if (!empty($condition)) {
            $this->_sqlStructure['WHERE'] = $condition;
        }
        $sql = $this->_getSql(self::SQL_TYPE_HAS);
        $res = $this->query($sql);
        $res = array_pop($res);

        return (boolean)$res['result'];
    }

    /**
     * 获取一条记录 , getRow别名方法
     *
     * @param string $condition  'where' of sql
     * @param array  $fetchType fetch type (assoc|num|array|object)
     *
     * @return array
     */
    public function find($condition = '', $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        $this->_sqlStructure['LIMIT'] = '1';
        $result = $this->select($condition, $fetchStyle);

        return array_shift($result);
    }

    /**
     * 查询多条记录
     *
     * @param string $condition  'where' of sql
     * @param array  $fetchStyle fetch type (assoc|num|array|object)
     * @return array
     */
    public function select($condition = '', $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        if (!empty($condition)) {
            $this->_sqlStructure['WHERE'] = $condition;
        }
        $sql = $this->_getSql(self::SQL_TYPE_SELECT);

        return $this->query($sql, $fetchStyle);
    }

    /**
     * 更新记录
     *
     * @param array  $data      data to insert
     * @param string $condition where condition
     *
     * @return bool|mixed
     */
    public function save(array $data, $condition = '')
    {
        if (empty($data)) {
            return false;
        }
        $this->_data = $this->_filterData($data);
        if (!empty($condition)) {
            $this->_sqlStructure['WHERE'] = $condition;
        }
        $sql = $this->_getSql(self::SQL_TYPE_UPDATE);
        $res = $this->query($sql);
        if (false === $res) {
            return false;
        }

        return $res;
    }

    /**
     * 查询一条记录，返回二维数组
     *
     * @param string $condition 'where' of sql
     * @param array  $fetchType fetch type (assoc|num|array|object)
     *
     * @return array
     */
    public function getRow($condition = '', $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        return $this->find($condition, $fetchStyle);
    }

    /**
     * 查询满足条件的所有记录
     *
     * @param string $condition 'where' of sql
     * @param array  $fetchType fetch type (assoc|num|array|object)
     *
     * @return array
     */
    public function getAll($condition = '', $fetchStyle = self::FETCH_TYPE_ASSOC)
    {
        return $this->select($condition, $fetchStyle);
    }

    /**
     * 查询一列值，返回一维数组
     *
     * @param string  $colName        field name
     * @param boolean $toOneDimension trans the result to one-dimension array
     * @param string  $condition      'where' of sql
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
     *  else if $convetTo1D is true:
     *  array('userA', 'userB', 'userC', 'userD',);
     * </pre>
     * @return array
     */
    public function getCol($colName, $condition = '',$toOneDimension = true)
    {
        if (empty($this->_tableMeta)) {
            throw new Exception("未初始化目标数据表");
        }
        if (!in_array($colName, $this->_tableMeta['_fields'])) {
            $this->_log("表'{$this->_tableMeta['_name']}'不存在字段'{$colName}'");
            return false;
        }
        $this->_sqlStructure['FIELDS'] = $colName;
        $result = $this->select($condition);
        $cols = array();
        $cols1D = array();
        foreach ($result as $value) {
            if (isset($value[$colName])) {
                $cols[][$colName] = $value[$colName];
                $cols1D[] = $value[$colName];
            }
        }

        return $toOneDimension ? $cols1D : $cols;
    }

    /**
     * 查询一条记录中单个字段的值
     * 此方法会返回一条记录中一个字段的值，常用于查询一个具体的值
     * 比如查询用户表里id = 1 的用户名（username），将会返回一个具体的string 值
     *
     * @param string $cellName  cell name
     * @param string $condition 'where' of sql
     *
     * @return string $singleValue value from cell
     */
    public function getCell($cellName, $condition = '')
    {
        if (!in_array($cellName, $this->_tableMeta['_fields'])) {
            $this->_log("表'{$this->_tableMeta['_name']}'不存在字段'{$cellName}'");
            return false;
        }
        $res = $this->find($condition);
        if ($res && isset($res[$cellName])) {
            return $res[$cellName];
        }

        return false;
    }

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $condition  'where' of sql
     *
     * @return array $associativeArray associative array result set
     */
    public function getAssoc($condition = '')
    {
        return $this->find($condition);
    }

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $condition  'where' of sql
     *
     * @return array $associativeArray associative array result set
     */
    public function getObject($condition = '')
    {
        return $this->find($condition, self::FETCH_TYPE_OBJECT);
    }

    /**
     * 得到一个关联数组结果集
     * 此方法只适用于多条记录，单条记录不适用
     *
     * @param string $condition  'where' of sql
     *
     * @return array $associativeArray associative array result set
     */
    public function getObjects($condition = '')
    {
        return $this->select($condition, self::FETCH_TYPE_OBJECT);
    }

    /**
     * 查询总条数
     *
     * @param string $field  field name
     * @param string $condition  'where' of sql
     *
     * @return int amount of records
     */
    public function getCount($field = '', $condition = '')
    {
        if (empty($this->_tableMeta)) {
            throw new Exception("未初始化目标数据表");
        }
        if (empty($field) || !is_array($field, $this->_tableMeta['_fields'])) {
            $field = $this->_tableMeta['_pk'];
        }
        $this->_sqlStructure['FIELDS'] = "COUNT(`{$field}`) AS `count`";
        $res = $this->find($condition);
        if ($res) {
            return $res['count'];
        }

        return 0;
    }

    /**
     * 返回完整的SQL语句
     *
     * @param  string $sqlType   sql type
     * @param  string $sql       sql width '?'
     * @param  array  $data      data to bind
     * @param  array  $fieldArea field set,default is *
     *
     * @return string
     */
    protected function _getSql($sqlType, $data = array())
    {
        if (empty($this->_tableMeta)) {
            throw new Exception("未初始化目标数据表");
        }
        $tableName = $this->_tableMeta['_name'];
        if (is_array($this->_sqlStructure['WHERE'])) {
            $this->_sqlStructure['WHERE'] = $this->array2Where($this->_sqlStructure['WHERE']);
        }
        $fields = empty($this->_sqlStructure['FIELDS']) ? '*' : $this->_sqlStructure['FIELDS'];
        $from   = empty($this->_sqlStructure['FROM']) ? " FROM `{$tableName}`" : " FROM {$this->_sqlStructure['FROM']}";
        $join   = empty($this->_sqlStructure['JOIN']) ? '' : " JOIN {$this->_sqlStructure['JOIN']}";
        $on     = empty($this->_sqlStructure['ON']) ? '' : " ON {$this->_sqlStructure['ON']}";
        $where  = empty($this->_sqlStructure['WHERE']) ? '' : " WHERE {$this->_sqlStructure['WHERE']}";
        $order  = empty($this->_sqlStructure['ORDER']) ? '' : " ORDER BY {$this->_sqlStructure['ORDER']}";
        $limit  = empty($this->_sqlStructure['LIMIT']) ? '' : " LIMIT {$this->_sqlStructure['LIMIT']}";
        $sql = '';
        $this->_data = array_map(array($this,'escape'), $this->_data);
        switch ($sqlType) {
            case self::SQL_TYPE_SELECT:
                $sql = "SELECT {$fields}{$from}{$join}{$on}{$where}{$order}{$limit}";
                break;
            case self::SQL_TYPE_DELETE:
                $sql = "DELETE {$from}{$join}{$on}{$where}{$order}{$limit}";
                break;
            case self::SQL_TYPE_UPDATE:
                if (empty($this->_data)) {
                    return false;
                }
                $arr = array();
                foreach ($this->_data as $field => $value) {
                    $arr[] = "`{$field}` = '{$value}'";
                }
                $setString = join(',', $arr);
                $sql = "UPDATE `{$tableName}` SET {$setString}{$where}{$order}{$limit} ";
                break;
            case self::SQL_TYPE_INSERT:
                if (empty($this->_data)) {
                    return false;
                }
                $keys   = join(',', array_keys($this->_data));
                $value = '"' . join('","', $this->_data) . '"';
                $sql    = "INSERT INTO `{$tableName}`({$keys}) VALUES({$value})";
                break;
            case self::SQL_TYPE_HAS:
                $sql = "SELECT EXISTS(SELECT 1{$from}{$join}{$on}{$where}) AS `result`";
                break;
            default:
                break;
        }
        $this->_multiInsert = false;

        return trim($sql);
    }

    /**
     * 生成where条件
     *
     * @param array $where 关联数组形式的where
     *
     * @return string
     */
    public function array2Where($where)
    {
        $tmp = array();
        $allowModes = array('=', '!=', '>=', '<=', '><', '>', '<', 'in');
        foreach ($where as $key => $value) {
            $mode = '=';
            if (preg_match('/\[(.*?)\]/', $key, $matchs)) {
                $key = strstr($key, '[', true); //去除[xxx]
                if (in_array($matchs[1], $allowModes)) {
                    $mode = $matchs[1];
                }
            }
            if ($mode == '><') {//between and
                if (is_array($value) && count($value) == 2) {
                    $value = array_values($value);
                    $tmp[] = "`{$key}` BETWEEN '{$value[0]}' AND '{$value[1]}";
                } else {
                    $mode = '=';
                }
            } else {
                $tmp[] = "`{$key}` {$mode} ('{$value}')";
            }
        }

        return join(' AND ', $tmp);
    }

    /**
     * getter
     *
     * @param string $proName property name
     *
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
     * setter
     *
     * @param string $proName  property name
     * @param string $proValue value of property
     *
     * @return mixed
     */
    public function __set($proName, $value)
    {
        switch (strtolower($proName)) {
            case 'data':
                return $this->_data = $value;
                break;
        }
    }

    /**
     * 连贯操作
     * and
     * getBy+首字母大写的字段名
     *
     * @param string $methodName 字段名
     * @param mixed  $args       参数
     *
     *
     * @return mixed
     */
    public function __call($methodName, $args)
    {
        $value = array_shift($args);
        //getByXxx
        if (0 === strpos($methodName, 'getBy') && !empty($value)) {
            //取字段名:getByUserName =>user_name,getByPassword => password
            $field = strtolower(preg_replace('/(\w)([A-Z])/', '\\1_\\2', substr($methodName, 5)));
            if (is_array($value)) {
                $where = "`{$field}` IN('".join("','", $value)."')";
                return $this->where($where)->getAll();// 多条
            } elseif (is_scalar($value)) {
                $where = "`{$field}` = '{$value}'";
                return $this->where($where)->getRow();// 单条
            }
        }
        if (strtolower($methodName) == 'data') {
            $this->_data = $value;
        }
        //连贯操作
        if (array_key_exists(strtoupper($methodName), $this->_sqlStructure)) {
            $this->_sqlStructure[strtoupper($methodName)] = $value;
            return $this;
        }
        return "method '{$methodName}' not exists!";
    }

    /**
     * log
     *
     * @param string $string log info
     * @param string $type   error | sql
     * @return void
     */
    protected function _log($string, $type = self::LOG_TYPE_ERROR)
    {
        if ($type == 'error') {
            error_log($string);
        }
        if (class_exists('Trace') && $type == self::LOG_TYPE_SQL) {
            Trace::L($string, Trace::LOG_TYPE_SQL);
        }
    }

     /**
     * 重置SQL结构
     *
     * @return array
     */
    public function _resetSqlStructure()
    {
        return array(
                'FIELDS' => '*',
                'FROM'   => '',
                'JOIN'   => '',
                'ON'     => '',
                'WHERE'  => '',
                'ORDER'  => '',
                'LIMIT'  => '',
               );
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
     * @param array $data input data
     *
     * @return array
     */
    protected function _filterData($data)
    {
        if (empty($this->_tableMeta)) {
            throw new Exception("未初始化目标数据表");
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
     * 提取结果集
     *
     * @param resource $resource  query resource
     * @param string   $fetchType fetch type [array, num, assoc, object]
     *
     * @return array or boolean
     */
    protected function _fetch($resource, $fetchType)
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

    abstract protected function fetchArray($resource);
    abstract protected function fetchNum($resource);
    abstract protected function fetchAssoc($resource);
    abstract protected function fetchObject($resource);
    abstract protected function escape($string);
    abstract protected function exec($sql);
    abstract protected function getInsertId();
    abstract protected function getAffectedRows();
    abstract protected function close();

}// END class Db_Adapter