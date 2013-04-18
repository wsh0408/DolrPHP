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


    public static function initialize()
    {
        # code...
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
        $msg       = vsprintf("%s: '%s'. 位置:%s:%s", func_get_args());
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
            //TODO:
        }
    }

    /**
     * Trace信息输出
     *
     * @return mixed
     */
    public static function traceInfo()
    {
        //time
        $timeUsage = round(self::$endTime - self::$startTime, 5);
        //memory
        $memUsage = byte_format(self::$endMemory - self::$startMemory);
        //errofinfo
        $errorInfo = empty(self::$errorInfo) ? '' : '<li>ERROR:' . join('</li><li>ERROR:', array_reverse(self::$errorInfo)) . '</li>';
        //normalInfo
        $normalInfo = empty(self::$normalInfo) ? '' : '<li>' . join('</li><li>', array_reverse(self::$normalInfo)) . '</li>';
        //dbLog
        $dbLog = empty(self::$dbLog) ? '' : '<li>DB: ' . join('</li><li>SQL:', array_reverse(self::$dbLog)) . '</li>';
        //当前页面
        $currentFile = $_SERVER['SCRIPT_FILENAME'];
        //模块目录
        $moduleDir = C('CONTROLLER_PATH') . ucfirst(App::$controllerName) . C('CONTROLLER_IDENTITY') . '.php';
        //模板目录
        $tplDir = empty(self::$tplName) ? '未使用模板或页面出错！' : C('VIEW_PATH') . self::$tplName;

        //加载的类
        $classes  = '<ol><li>' . join('</li><li>', self::$loadedClasses) . '</li></ol>';
        $runInfo  = '<ul>' . $errorInfo . $dbLog . $normalInfo . '</ul>';
        $runInfo  = ($runInfo == '<ul></ul>') ? '运行正常' : $runInfo;
        $args     = array(
                     '[TIME_USAGE]'   => $timeUsage,
                     '[MEM_USAGE]'    => $memUsage,
                     '[CURRENT_FILE]' => $currentFile,
                     '[MODULE_DIR]'   => $moduleDir,
                     '[TEMPLATE_DIR]' => $tplDir,
                     '[CLASSES]'      => $classes,
                     '[RUN_INFO]'     => $runInfo
                    );
        $html = G(C('PAGE_TRACE'), false);
        $html = str_replace(array_keys($args), $args, $html);

        return $html;
    }

    /**
     * Trace格式化
     *
     * @param array $dbgTrace trace info
     * @param array $retArray    return array
     *
     * @return string
     */
    public static function traceFormat($dbgTrace, $retArray = FALSE) {
        $result = array();
        foreach ($dbgTrace as $dbgIndex => $dbgInfo) {
            $args = array();
            foreach ($dbgInfo['args'] as $arg) {
                if (is_array($arg)) {
                    array_push($args, 'Array');
                } elseif (is_bool($arg)) {
                    array_push($args, (bool)$arg);
                } elseif (is_object($arg)) {
                    array_push($args, 'Object');
                } elseif (is_string($arg)) {
                    array_push($args, $arg);
                } else {
                    array_push($args, gettype($arg));
                }
            }
            $result[] = "#{$dbgIndex} " . $dbgInfo['file']
                        . " (line {$dbgInfo['line']}) -> {$dbgInfo['function']}( "
                        . join(",", $args) . " )";
        }
        if ($array)
            return $result;

        return '<ul><li>' . join('</li><li>', $result) . '</li></ul>';
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
        if ($errorNo == E_RECOVERABLE_ERROR or $errorNo == E_PARSE or $errorNo == E_USER_ERROR) {
            $args = array(
                     '[MESSAGE]'  => $errorString,
                     '[FILENAME]' => $errorFile,
                     '[LINE]'     => $errorLine
                    );
            $html = G(C('PAGE_ERROR'), false);
            $html = str_replace(array_keys($args), $args, $html);
            if (Request::isAjax())
                exit(data2json(array(
                                'info' => '系统出错',
                                'data' => array(
                                           'info' => $errorString,
                                           'file' => $errorFile,
                                           'line' => $errorLine,
                                          )
                               )));
            exit($html);
        }
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
        //Trace模板
        $traceLine = "<li class='%s'><span>#%s</span><span title='函数'>%s(%s)</span>
                        <span title='文件位置'>%s : %s</span></li>";
        $msg       = "出错啦！: '%s' 类出现 '%s' 异常.
                        位置:%s:%s\nStack trace:\n%s\n  thrown in %s on line %s";
        // trace
        $dbgTrace  = array_reverse($exception->getTrace());
        $traceInfo = self::traceFormat($dbgTrace);
        //格式化msg
        $msg = sprintf(
                    $msg,
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    join("\n", $traceInfo),
                    $exception->getFile(),
                    $exception->getLine()
                );
        $args = array(
                 '[MESSAGE]'  => $exception->getMessage(),
                 '[FILENAME]' => $exception->getFile(),
                 '[LINE]'     => $exception->getLine(),
                 '[TRACE]'    => $traceInfo,
                );
        error_log($msg);
        $html = G(C('PAGE_EXCEPTION'), false);
        $html = str_replace(array_keys($args), $args, $html);
        if (Request::isAjax())
            exit(data2json(
                array(
                 'info' => '系统出错',
                 'data' => array(
                            'info' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine()
                           )
                )));
        exit($html);
    }

} // END class Trace
