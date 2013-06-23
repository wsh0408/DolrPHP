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
 * Dao日志接口
 **/
interface Dao_Logger
{
    /**
     * 日志记录
     *
     * @param string  $string 内容
     * @param integer $type   类型[1:正常,0:错误,2:SQL]
     *
     * @return void
     */
    public function log($string, $type = 1);

} // END interface Dao_Logger

/**
 * 默认的日志类
 */
class Dao_DefAultLogger implements Dao_Logger
{
    public function log($string, $type = 1)
    {
        if (!$type)
            error_log($string);
    }
}