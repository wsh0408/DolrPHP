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
//base
defined('APP_NAME')  or define('APP_NAME', basename($dirOfScriptName)); 
defined('APP_PATH')  or define('APP_PATH', $dirOfScriptName . '/');
defined('DOLR_PATH') or define('DOLR_PATH', $dirOfLocal . '/');
//dirs
define('BASE_PATH', DOLR_PATH . 'Base/');   //DolrPHP 基础文件目录
define('DB_PATH', DOLR_PATH . 'Db/');       //DolrPHP 数据库驱动目录
define('EXT_PATH', DOLR_PATH . 'Ext/');     //DolrPHP 框架拓展目录
define('INC_PATH', DOLR_PATH . 'Inc/');     //DolrPHP 基础文件目录
define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);


//include class trace
include BASE_PATH . 'Trace.php';
//include functions
include INC_PATH . 'Functions.php';

//init more
include BASE_PATH . 'Dolr.php';
Dolr::initialize();

//run & trace
Trace::start();
Dolr::run();
Trace::end();

//Trace
if (C('SHOW_TRACE') and !Request::isAjax()) {
    echo Trace::traceInfo();
}
