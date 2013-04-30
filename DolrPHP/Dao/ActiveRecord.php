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
 * ActiveRecord 类
 * 能让用户把当前对象当成一个数组一样操作
 **/
class Db_ActiveRecord implements ArrayAccess, Iterator, Countable
{
    /**
     * 单条记录数据
     *
     * @var array
     */
    protected $data = array();

    /**
     * 当前数据所属表信息
     *
     * @var array
     **/
    public $table = array();

    /**
     * 数据库连接对象
     *
     * @var object
     */
    protected static $adapter = null;

    /**
     * 实例化对象
     *
     * @param array $tableName 当前数据所属表名
     * @param array $data      数据
     *
     * @return void
     */
    public function __construct($tableName, $data)
    {
        $this->data = $data;
        if (is_null(self::$adapter)) {
            $engine        = Db::$adapterType;
            $adapter       = 'Db_Adapter_' . ucfirst($engine);
            self::$adapter = new $adapter($tableName);
        }
        $this->table = self::$adapter->getTableInfo($tableName);
    }

    /**
     * 更新当前记录[只限单条记录形式]
     *
     * @return boolean
     */
    public function save()
    {
        $data = $this->data;

        return self::$adapter->save($data, "`{$this->table['pk']}` = ?", array($data[$this->table['pk']]));
    }

    /**
     * 删除当前记录
     *
     * @return boolean
     */
    public function del()
    {
        self::$adapter->del($this->table['pk'] . ' = ?', array($this->data[$this->table['pk']]));
    }

    /**
     * 导出为关联数组
     *
     * @return array associative Array
     */
    public function export()
    {
        return $this->data;
    }

    /**
     * 是否为单条导出
     *
     * @return array
     */
    public function toArray()
    {
        return array_values($this->export());
    }

    /**
     * 将当前数据导出一条关联数组
     *
     * @return array associative Array
     */
    public function toAssoc()
    {
        return $this->export();
    }

    /**
     * 模拟继承Adapter
     *
     * @param $methodName
     * @param $args
     *
     * @return void
     */
    public function __call($methodName, $args)
    {
        if (!method_exists($this, $methodName) and method_exists(self::$adapter, $methodName)) {
            call_user_func_array(array(self::$adapter, $methodName ), $args);
        }
    }

    /**
     * 访问属性
     *
     * @param string $proName property name
     *
     * @return mixed
     */
    public function &__get($proName)
    {
        if (isset($this->data[$proName])) {
            return $this->data[$proName];
        }
    }

    /**
     * 设置属性
     *
     * @param string $proName  property name
     * @param mixed  $proValue value
     *
     * @return mixed
     */
    public function __set($proName, $proValue)
    {
        if (isset($this->data[$proName])) {
            return $this->data[$proName] = $proValue;
        }
    }

    //以下是实现数组访问对象方法
    public function offsetSet($offset, $value) { $this->data[$offset] = $value; }
    public function offsetExists($offset) { return isset($this->data[$offset]); }
    public function offsetUnset($offset) { unset($this->data[$offset]); }
    public function offsetGet($offset) { return isset($this->data[$offset]) ? $this->data[$offset] : NULL; }
    public function current() { return current($this->data); }
    public function key() { return key($this->data); }
    public function next() { return next($this->data); }
    public function valid() { return ($this->current() !== FALSE); }
    public function rewind() { return reset($this->data); }
    public function count() { return count($this->data); }

} // END class Db_ActiveRecord
