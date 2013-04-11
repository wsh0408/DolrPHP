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
$dirOfScriptName = dirname($_SERVER['SCRIPT_FILENAME']);
$dirOfLocal = str_replace('\\', '/', dirname(__FILE__));

if (strlen($_SERVER['DOCUMENT_ROOT']) > strlen($dirOfScriptName) + 1) {
    $dirOfScriptName = trim($_SERVER['DOCUMENT_ROOT'],'/');
}

//base
defined('APP_NAME')  or define('APP_NAME', basename($dirOfScriptName));
defined('APP_PATH')  or define('APP_PATH', $dirOfScriptName . '/');
defined('DOLR_PATH') or define('DOLR_PATH', $dirOfLocal . '/');

//dirs
define('DB_PATH', DOLR_PATH . 'Db/');       //DolrPHP 数据库驱动目录
define('EXT_PATH', DOLR_PATH . 'Ext/');     //DolrPHP 框架拓展目录
define('INC_PATH', DOLR_PATH . 'Inc/');     //DolrPHP 基础文件目录
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
Trace::start();

//init
App::initialize();

App::run();

Trace::end();

//Trace
if (C('SHOW_TRACE') and !Request::isAjax()) {
    echo Trace::traceInfo();
}
