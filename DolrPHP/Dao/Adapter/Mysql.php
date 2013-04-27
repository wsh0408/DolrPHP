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
 * DB Mysql类
 **/
class Db_Adapter_Mysql implements Db_Adapter
{
    /**
     * 数据库连接对象
     *
     * @var object
     **/
    public $db = NULL;

    /**
     * 获取最后执行的SQL
     *
     * @var string
     */
    public $lastSql;

    /**
     * 当前表信息
     *
     * @var array
     **/
    public $table = array();


    /**
     * 构造函数
     * @param string $tableName 表名
     * @throws Exception
     */
    public function __construct($tableName = '') {
        $this->db = Db::getDatabase();
        if ($tableName) {
            $this->table = $this->getTableInfo($tableName);
        }
    }

    /**
     * 获取表结构
     * @param  string $tableName table name
     * @return array
     */
    public function getTableInfo($tableName) {
        $info           = mysql_query("SHOW COLUMNS FROM `$tableName`");
        $data           = array();
        $data['name']   = $tableName;
        $data['fields'] = array();
        if (empty($info)) {
            $logger = Db::getLogger();
            $logger->log('查询错误: 表"' . $tableName . '"不存在', 0);
        }
        while ($value = mysql_fetch_assoc($info)) {
            $data['fields'][] = $value['Field'];
            if ($value['Key'] == 'PRI') {
                $data['pk'] = $value['Field'];
            }
        }
        if (!isset($data['pk'])) //没有主键的话默认第一个字段为主键咯，谁让你建个表这么不科学！
            reset($data['fields']);
        $key        = key($data['fields']);
        $data['pk'] = $data['fields'][$key];

        return $data;
    }

    /**
     * 获取最后执行的SQL
     *
     * @return string $SQLString SQLString
     */
    public function getSQL() {
        return $this->lastSql;
    }


    /**
     * 转义SQL并返回
     *
     * @param string $sqlValue value
     * @return mixed
     */
    public function escape($sqlValue) {
        if (is_array($sqlValue)) {
            $sqlValue = array_map('mysql_real_escape_string', $sqlValue);
        } elseif (!is_numeric($sqlValue)) {
            $sqlValue = mysql_real_escape_string($sqlValue);
        }

        return $sqlValue;
    }

    /**
     * 执行一个SQL查询
     *
     * @param string  $sql     SQL
     * @param array   $values  values
     * @param         object
     * @return mixed
     */
    public function exec($sql, $values = array()) {
        $logger = Db::getLogger();
        if (preg_match('/[\'"]/', $sql))
            $logger->log('SQL语句非法！SQL中不能直接传值，请使用第二个参数数组值绑定形式传递[SQL: ' . $sql . ']', 0);
        if (!empty($values)) {
            //检测 ? 号个数
            preg_match_all('/\?/', $sql, $matches);
            $logString = ' [sql:' . $sql . ',values:(' . join(',', $values) . ')]';
            if (count($matches[0]) != count($values))
                $logger->log('绑定的值与SQL中点位符个数不一致:' . $logString, 0);
            $values = $this->escape($values);
            $sql    = str_replace('?', '"%s"', $sql);
            array_unshift($values, $sql);
            $sql = call_user_func_array('sprintf', $values);
        }
        $this->lastSql = $sql;
        $logger->log($sql, 2); //2:sql
        $res = mysql_query($sql, $this->db);
        if (gettype($res) == 'boolean') { //update,delete...
            if (FALSE === $res)
                $logger->log('查询出错: ' . $sql, 0);
            else {
                $logger->log('影响行数:' . mysql_affected_rows() . '行(' . $sql . ')'); //1
                if (FALSE !== stripos($sql, 'insert')) //insert
                    return $this->getInsertID();

                return TRUE;
            }
        } else { //select,explain,show...
            $ret = array();
            $logger->log('结果集:' . mysql_affected_rows() . '行 [ ' . $sql . ' ]'); //1
            while ($row = mysql_fetch_assoc($res)) {
                $ret[] = $row;
            }
            if ($this->table['name'])
                //mysql不能使用预处理，所以在插入前已经转义了，取出来同样得转回来嘛... 0.0
                $ret = Db::convertToActiveRecord($this->table['name'], $ret); //转换成ActiveRecord
            return $ret;
        }

        return TRUE;
    }

    /**
     * 执行一个SQL查询
     *
     * @param string  $sql     SQL
     * @param array   $values  values
     * @param         object
     * @return mixed
     */
    public function query($sql, $values = array()) {
        return $this->exec($sql, $values);
    }

    /**
     * 添加记录
     *
     * @param $data 关联数组[字段 => 值]
     * @return int
     */
    public function add($data) {
        $fields = array();
        $values = array();
        foreach ($data as $field => $value) {
            if (in_array($field, $this->table['fields'])) {
                $fields["`{$field}`"] = '?';
                $values[]             = $value;
            }
        }
        $sql = "INSERT INTO `{$this->table['name']}`(" . join(' , ', array_keys($fields)) . ") VALUES( " . join(',', array_values($fields)) . " )"; // table(a,b,c,d,e) VALUES(?,?,?,?)
        return $this->exec($sql, $values);
    }

    /**
     * 删除记录
     * @param string $sql
     * @param array  $values
     * @return bool
     */
    public function del($sql = '', $values = array()) {
        if ($sql == '') {
            $logger = Db::getLogger();
            $logger->log('为了防止误删除，del操作必须传WHERE条件', 0);

            return FALSE;
        }
        $sql = "DELETE FROM `{$this->table['name']}` WHERE ( {$sql} )";

        return $this->exec($sql, $values);
    }

    /**
     * 获取一条记录
     * getRow别名方法
     *
     * @param string $sql
     * @param array  $values
     * @return array
     */
    public function find($sql = '1 = 1', $values = array()) {
        return $this->getRow($sql, $values);
    }

    /**
     * 查询多条记录
     * @param string $sql
     * @param array  $values
     * @return array
     */
    public function select($sql = '1 = 1', $values = array()) {
        return $this->getAll($sql, $values);
    }


    /**
     * 更新记录
     *
     * @param array  $data
     * @param string $sql
     * @param array  $values
     * @return bool|mixed
     */
    public function save($data = array(), $sql = '1 = 1', $values = array()) {
        if (empty($data)) //不传数据直接FALSE
            return FALSE;
        $change = array();
        $data   = $this->escape($data);
        foreach ($data as $field => $value) {
            if (in_array($field, $this->table['fields'])) {
                $change[] = "`{$field}` = '{$value}'";
            }
        }
        $change = join(',', $change);
        $sql    = "UPDATE `{$this->table['name']}` SET {$change} WHERE ( {$sql} )";

        return $this->exec($sql, $values);
    }

    /**
     * 查询一条记录，返回二维数组
     *
     * @param string $sql      SQL
     * @param array  $values   values to bind
     *
     * @return array
     */
    public function getRow($sql = '1 = 1', $values = array()) {
        $sql = "SELECT * FROM {$this->table['name']} WHERE ( {$sql} ) LIMIT 1";
        $res = $this->exec($sql, $values);

        return array_shift($res);
    }

    /**
     * 查询满足条件的所有
     * @param string $sql      SQL
     * @param array  $values   values to bind
     *
     * @return array
     */
    public function getAll($sql = '1 = 1', $values = array()) {
        $sql = "SELECT * FROM `{$this->table['name']}` WHERE ( {$sql} )";

        return $this->exec($sql, $values);
    }

    /**
     * 查询一列值，返回一维数组
     * @param string $colName   field name
     * @param string $sql       SQL
     * @param array  $values    values to bind
     *
     * @return array
     */
    public function getCol($colName, $sql = '1 = 1', $values = array()) {
        $sql = "SELECT * FROM `{$this->table['name']}` WHERE ( {$sql} )";
        $res = $this->exec($sql, $values);
        $ret = array();
        if (!empty($res)) {
            foreach ($res as &$val) {
                $ret[] = $val[$colName];
            }
        }

        return $ret;
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
    public function getCell($cellName, $sql = '1 = 1', $values = array()) {
        $res = $this->getRow($sql, $values);

        return empty($res) ? FALSE : $res[$cellName];
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
    public function getAssoc($sql = '1 = 1', $values = array()) {
        return $this->getRow($sql, $values);
    }

    /**
     * 得到一个索引数组结果集
     * 此方法只适用于单条记录，多条记录不适用
     *
     * @param string $sql    SQL
     * @param array  $values values to bind
     *
     * @return array $associativeArray associative array result set
     */
    public function getArray($sql = '1 = 1', $values = array()) {
        return array_values($this->getRow($sql, $values));
    }


    /**
     * 返回最后插入的ID
     *
     * @return integer $id primary key ID
     */
    public function getInsertID() {
        return mysql_insert_id();
    }

    /**
     * 返回受影响的行数
     * 返回一个 update 操作影响的行数
     *
     * @return integer $count number of rows affected
     */
    public function getAffectedRows() {
        return mysql_affected_rows();
    }

    /**
     * 开启事务
     * Starts a transaction.
     */
    public function startTransaction() {
        mysql_query("BEGIN"); //开始一个事务
        mysql_query("SET AUTOCOMMIT=0"); //设置事务不自动commit
    }

    /**
     * 提交一个事务
     * Commits the transaction.
     */
    public function commit() {
        mysql_query("COMMIT"); //非autocommit模式，必须手动执行COMMIT使操作生效
    }

    /**
     * 回滚事务
     * Rolls back the transaction.
     */
    public function rollback() {
        mysql_query("ROLLBACK"); //非autocommit模式，执行ROLLBACK使事务操作无效
    }

    /**
     * 关闭连接
     */
    public function close() {
        mysql_query("SET AUTOCOMMIT=1"); //恢复autocommit模式
        mysdl_close();
    }
} // END class Db_Adapter_Mysql
