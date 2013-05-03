<?php
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     DolrPHP
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/
version_compare(PHP_VERSION, '5.3.0', '>') or die ('require PHP > 5.3.0 !');
defined('APP_PATH') || exit("<pre>请先定义应用目录常量'APP_PATH'(绝对路径),
                            eg:define('APP_PATH', __DIR__ . '/Home/')</pre>");
$dirOfLocal = str_replace('\\', '/', dirname(__FILE__));
$dirOfFile  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
//base
defined('APP_ROOT') || define('APP_ROOT', $dirOfFile . basename(trim(APP_PATH, '/')) . '/');
defined('DOLR_PATH') || define('DOLR_PATH', $dirOfLocal . '/');
defined('APP_ABS_PATH') || define('APP_ABS_PATH', realpath(APP_PATH) . '/');
//dirs
define('DB_PATH', DOLR_PATH . 'Dao/');              //DolrPHP 数据库驱动目录
define('EXT_PATH', DOLR_PATH . 'Extension/');       //DolrPHP 框架拓展目录
define('INC_PATH', DOLR_PATH . 'Include/');         //DolrPHP 基础文件目录
define('TPL_PATH', DOLR_PATH . 'Template/');        //DolrPHP 基础文件目录
define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

//init more
include DOLR_PATH . 'App.php';

//关闭错误输出
error_reporting(0);
//设置错误处理函数
set_error_handler(array('Trace','errorHandler'));
//设置致命错误处理函数
register_shutdown_function(array('Trace','shutdownHandler'));
//设置异常处理函数
set_exception_handler(array('Trace','exceptionHandler'));
//强制输出编码为UTF-8
header('Content-Type: text/html; charset=utf-8');
//自动转义
define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc());
//关闭自动转义
ini_set('magic_quotes_runtime', 0);

//run & trace
Trace::initialize();
Trace::start();

//init
App::initialize();

App::run();

Trace::end();

//Trace
if (C('SHOW_TRACE') and !Request::isAjax()) {
    echo Trace::traceInfo();
}
