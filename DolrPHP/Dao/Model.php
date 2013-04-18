<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Joychao
 * Date: 12-12-10
 * Time: 上午8:30
 * To change this template use File | Settings | File Templates.
 */

/**
 * 数据库模型基类
 */
class Db_Model
{
    /**
     * 适配器对象
     * 
     * @var object
     */
    public $adapter;

    public function __construct($tableName = '') 
    {
        $adapter       = 'DB_Adapter_' . Db::$adapterType;
        $this->adapter = new $adapter($tableName); //实例化适配器
    }

    /**
     * 魔术方法
     * 只适用于单条记录
     * getBy+首字母大写的字段名
     * $obj->getByUsername('admin'),$obj->getById(5)...
     *
     * @param $methodName
     * @param $args
     * 
     * @return mixed
     */
    public function __call($methodName, $args) 
    {
        //getByUsername
        if (false !== strpos($methodName, 'getBy')) {
            //取字段名:getByUserName =>user_name,getByPassword => password
            $field = strtolower(preg_replace('/(\w)([A-Z])/', '\\1_\\2', substr($methodName, 5)));
            $sql   = "`{$field}` = ?";

            return $this->adapter->getRow($sql, $args);
            
        //实现假继承
        } elseif (is_callable(array( $this->adapter, $methodName ))) {
            return call_user_func_array(array( $this->adapter, $methodName ), $args);
        }

        return false;
    }
}