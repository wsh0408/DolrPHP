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
defined('APP_PATH') || exit("请先定义应用目录常量'APP_PATH'(绝对路径),且目录存在,
                            eg:define('APP_PATH', __DIR__ . '/Home/')");

$dirOfLocal = str_replace('\\', '/', dirname(__FILE__));
echo $dirOfFile  = str_replace(array('\\','//'), '/', dirname($_SERVER['SCRIPT_NAME']). '/') ;

//base
defined('APP_ROOT') || define('APP_ROOT', $dirOfFile . basename(trim(APP_PATH, '/')) . '/');
defined('DOLR_PATH') || define('DOLR_PATH', $dirOfLocal . '/');

//dirs
define('DB_PATH',  DOLR_PATH . 'Dao/');             //DolrPHP 数据库驱动目录
define('EXT_PATH', DOLR_PATH . 'Extension/');       //DolrPHP 拓展目录
define('TPL_PATH', DOLR_PATH . 'Template/');        //DolrPHP 基础文件目录
define('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('IS_CLI', PHP_SAPI == 'cli' ? 1 : 0);

//DOLRPHP_VERSION
define('DOLRPHP_VERSION', '3.0.0');

//init more
include DOLR_PATH . 'App.php';
//run & trace
Trace::initialize();
Trace::start();
//init
App::initialize();
App::run();
Trace::end();
//Trace
if (Config::get('SHOW_TRACE') and !Request::isAjax()) {
    echo Trace::traceInfo();
}
