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
 * DB Mysqli类
 **/
class Db_Adapter_Mysqli implements Db_Adapter
{
    /**
     * 数据库连接对象
     *
     * @var object
     **/
    public $instance = NULL;

    /**
     * 最后插入的数据ID
     *
     * @var integer
     **/
    public $lastInertId;

    /**
     * 当前表信息
     *
     * @var array
     **/
    public $table = array();


} // END class Db_Adapter_Mysqli
