<?php defined('DOLR_PATH') or exit('No direct script access.');
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

/**
 * 系统Trace
 *
 * @package DolrPHP
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Trace
{
    /**
     * log type
     */
    const LOG_TYPE_CLASS    = 1;
    const LOG_TYPE_TEMPLATE = 2;
    const LOG_TYPE_ERROR    = 3;
    const LOG_TYPE_SQL      = 4;

    /**
     * 初始时间
     *
     * @var float
     */
    public static $startTime;

    /**
     * 结束时间
     *
     * @var float
     */
    public static $endTime;

    /**
     * 初始内存占用值
     *
     * @var int
     */
    public static $startMemory;

    /**
     * 结束内存占用值
     *
     * @var int
     */
    public static $endMemory;

    /**
     * 运行加载的类
     *
     * @var array
     */
    public static $loadedClasses = array('App','Trace','Dispather');

    /**
     * 错误信息
     *
     * @var array
     **/
    public static $errorInfo = array();

    /**
     * 正常信息
     *
     * @var array
     **/
    public static $normalInfo = array();

    /**
     * 数据库日志
     *
     * @var array
     */
    public static $dbLog = array();

    /**
     * 当前模板名
     *
     * @var string
     */
    public static $tplName = '';

    /**
     * 日志文件
     *
     * @var string
     */
    public static $errorLogFile = '';


    public static function initialize()
    {
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
        //时区
        date_default_timezone_set('PRC');
    }

    /**
     * 开始记录
     *
     * @return void
     */
    public static function start()
    {
        self::$startTime   = microtime(true);
        self::$startMemory = memory_get_usage();
    }

    /**
     * 结束记录
     *
     * @return void
     */
    public static function end()
    {
        self::$endTime   = microtime(true);
        self::$endMemory = memory_get_usage();
    }

    /**
     * error record
     *
     * @param integer $errorType error type
     * @param string  $errorString    error info
     * @param string  $errorFile   file name
     * @param integer $errorLine   line number
     *
     * @return void
     */
    public static function error($errorType, $errorString, $errorFile, $errorLine)
    {
        $errTypes  = array(
            E_WARNING           => '运行警告',
            E_PARSE             => '语法错误',
            E_NOTICE            => '运行通知',
            E_USER_ERROR        => '运行错误',
            E_USER_WARNING      => '运行警告',
            E_USER_NOTICE       => '运行通知',
            E_STRICT            => '代码标准建议',
            E_RECOVERABLE_ERROR => '致命错误'
        );
        $errorType = array_key_exists($errorType, $errTypes) ? $errTypes[$errorType] : '未知错误';
        $msg       = vsprintf("%s: '%s'. 位置:%s:%s\n", func_get_args());
        error_log($msg);
        array_push(self::$errorInfo, $msg);
    }

    /**
     * 日志记录
     *
     * @return void
     */
    public static function L($value, $type = self::LOG_TYPE_ERROR)
    {
        switch ($type) {
            case self::LOG_TYPE_CLASS:
                array_push(self::$loadedClasses, $value);
                break;
            case self::LOG_TYPE_TEMPLATE:
                self::$tplName = $value;
                break;
            case self::LOG_TYPE_ERROR:
                array_push(self::$errorInfo, $value);
                break;
            case self::LOG_TYPE_SQL:
                array_push(self::$dbLog, $value);
                break;
        }
    }

    /**
     * Trace信息输出
     *
     * @return mixed
     */
    public static function traceInfo()
    {
        $rootLength = strlen($_SERVER['DOCUMENT_ROOT']);
        //time
        $timeUsage = round(self::$endTime - self::$startTime, 5);
        //memory
        $memUsage = byteFormat(self::$endMemory - self::$startMemory);
        //errorInfo
        $errorInfo = empty(self::$errorInfo) ? '' : '<li>ERROR:' . join('</li><li>ERROR:', array_reverse(self::$errorInfo)) . '</li>';
        //normalInfo
        $normalInfo = empty(self::$normalInfo) ? '' : '<li>' . join('</li><li>', array_reverse(self::$normalInfo)) . '</li>';
        //dbLog
        $dbLog = empty(self::$dbLog) ? '' : '<li>SQL:' . join('</li><li>SQL:', self::$dbLog) . '</li>';
        //当前页面
        $currentFile = substr($_SERVER['SCRIPT_FILENAME'], $rootLength);
        //模块目录
        $moduleDir = Config::get('CONTROLLER_PATH');
        $moduleDir = substr($moduleDir, $rootLength);
        //模板目录
        $tplDir = empty(self::$tplName) ? '未使用模板或页面出错！' : Config::get('TPL_PATH');
        $tplDir = substr($tplDir, $rootLength);
        //加载的类
        $classes  = '<ol><li>' . join('</li><li>', self::$loadedClasses) . '</li></ol>';
        $runInfo  = $errorInfo . $dbLog . $normalInfo;
        $runInfo  = empty($runInfo) ? '运行正常' : "<ul>$runInfo</ul>";
        $args     = array(
                     '[TIME_USAGE]'   => $timeUsage,
                     '[MEM_USAGE]'    => $memUsage,
                     '[CURRENT_FILE]' => $currentFile,
                     '[MODULE_DIR]'   => $moduleDir,
                     '[TEMPLATE_DIR]' => $tplDir,
                     '[CLASSES]'      => $classes,
                     '[RUN_INFO]'     => $runInfo
                    );
        $html = read(TPL_PATH . 'Trace.php', false);
        $html = str_replace(array_keys($args), $args, $html);

        return $html;
    }

    /**
     * 错误捕获方法
     *
     * @param int    $errorNo       错误号
     * @param string $errorString   错误字符串
     * @param string $errorFile     错误文件
     * @param int    $errorLine     错误行数
     *
     * @return void
     */
    public static function errorHandler($errorNo, $errorString, $errorFile, $errorLine)
    {
        self::error($errorNo, $errorString, $errorFile, $errorLine);
        $args = array(
                     '[MESSAGE]'  => $errorString,
                     '[FILENAME]' => $errorFile,
                     '[LINE]'     => $errorLine
                    );
        $html = read(TPL_PATH . 'SysError.php', false);
        $html = str_replace(array_keys($args), $args, $html);
        $ajaxArray = array(
                      'info' => '系统出错',
                      'data' => array(
                                 'info' => $errorString,
                                 'file' => $errorFile,
                                 'line' => $errorLine,
                                ),
                     );
        if (in_array($errorNo, array(E_WARNING))) {
            return;
        }
        self::_outputError($ajaxArray, $html);
    }

    /**
     * ShutDown捕获方法
     *
     * @return void
     */
    public static function shutdownHandler()
    {
        $errorInfo = error_get_last();
        if (!empty($errorInfo))
            call_user_func_array(array('Trace','errorHandler'), $errorInfo);
    }

    /**
     * 异常处理方法
     *
     * @param  object $exception 异常对象
     *
     * @return void
     */
    public static function exceptionHandler($exception)
    {
        $msg  = "出错啦！: '%s' 类出现 '%s' 异常. 位置:%s:%s\n";
        // trace
        $dbgTrace  = array_reverse($exception->getTrace());
        //格式化msg
        $msg = sprintf(
                    $msg,
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getFile()
                );
        $args = array(
                 '[MESSAGE]'  => $exception->getMessage(),
                 '[FILENAME]' => $exception->getFile(),
                 '[LINE]'     => $exception->getLine(),
                );
        error_log($msg);
        $html = read(TPL_PATH . 'Exception.php', false);
        $html = str_replace(array_keys($args), $args, $html);

        $ajaxArray = array(
                      'info' => '系统出错',
                      'data' => array(
                                 'info' => $exception->getMessage(),
                                 'file' => $exception->getFile(),
                                 'line' => $exception->getLine(),
                                ),
                     );
        self::_outputError($ajaxArray, $html);
    }

    /**
     * 输出错误或者异常信息
     *
     * @param array  $ajaxArray 当请求是ajax时的返回结果
     * @param string $html      不是ajax时返回的Html
     *
     * @return void
     */
    private static function _outputError($ajaxArray = array(), $html = '')
    {
        $debugState = Config::get('DEBUG');
        if(isset($debugState) && !$debugState) {
            return false;
        }
        if (Request::isAjax())
            exit(data2json($ajaxArray));
        exit($html);
    }

} // END class Trace

