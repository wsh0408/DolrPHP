<?php defined('DOLR_PATH') or exit('No direct script access.');
/**
 * DolrPHP轻量级PHP开发框架
 *
 * @package     DolrPHP.Base
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

/**
 * 系统Debug信息处理类
 *
 * @package DolrPHP.Base
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Trace
{
    /**
     * 初始时间辍
     * @var float
     */
    static public $startTime;

    /**
     * 结束时间辍
     * @var float
     */
    static public $endTime;

    /**
     * 初始内存占用值
     * @var int
     */
    static public $startMemory;

    /**
     * 结束内存占用值
     * @var int
     */
    static public $endMemory;

    /**
     * 运行加载的类
     * @var array
     */
    static public $loadedClasses = array();

    /**
     * 错误信息
     *
     * @var array
     **/
    static public $errorInfo = array();

    /**
     * 正常信息
     *
     * @var array
     **/
    static public $normalInfo = array();

    /**
     * 数据库日志
     *
     * @var array
     */
    static public $dbLog = array();

    /**
     * 当前模板名
     * @var string
     */
    static public $tplName = '';

    /**
     * 开始记录
     * @return void
     */
    static public function start() {
        self::$startTime   = microtime(TRUE);
        self::$startMemory = memory_get_usage();
    }

    /**
     * 结束记录
     * @return void
     */
    static public function end() {
        self::$endTime   = microtime(TRUE);
        self::$endMemory = memory_get_usage();
    }

    static public function error($errorType, $errstr, $errfile, $errline) {
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
        $msg       = call_user_func_array('sprintf', array( "%s: '%s'. 位置:%s:%s", $errorType, $errstr, $errfile, $errline ));
        error_log($errstr);
        array_push(self::$errorInfo, $msg);
    }

    /**
     * 日志记录
     */
    static public function L($value, $type = 'error') {
        switch ($type) {
            case 'class':
                array_push(self::$loadedClasses, $value);
                break;
            case 'tpl':
                self::$tplName = $value;
                break;
            case 'error':
                array_push(self::$errorInfo, $value);
            //TODO:
        }
    }

    /**
     * 实现的DB日志方法
     */
    public function log($string, $type = 1) {
        switch ($type) {
            case 0:
                array_push(self::$errorInfo, $string); //错误
                break;
            case 1:
            case 2:
                array_push(self::$dbLog, $string);
        }
    }

    /**
     * Trace信息输出
     *
     * @return mixed
     */
    static public function traceInfo() {
        //time
        $timeUsage = round(self::$endTime - self::$startTime, 5);
        //memory
        $memUsage = byte_format(self::$endMemory - self::$startMemory);
        //errofinfo
        $errorInfo = empty(self::$errorInfo) ? '' : '<li>ERR:' . join('</li><li>ERR:', array_reverse(self::$errorInfo)) . '</li>';
        //normalInfo
        $normalInfo = empty(self::$normalInfo) ? '' : '<li>' . join('</li><li>', array_reverse(self::$normalInfo)) . '</li>';
        //dbLog
        $dbLog = empty(self::$dbLog) ? '' : '<li>DB: ' . join('</li><li>SQL:', array_reverse(self::$dbLog)) . '</li>';
        //当前页面
        $currentFile = $_SERVER['SCRIPT_FILENAME'];
        //模块目录
        $moduleDir = C('CONTROLLER_PATH') . ucfirst(MODULE_NAME) . C('CONTROLLER_IDENTITY') . '.php';
        //模板目录
        $tplDir = empty(self::$tplName) ? '未使用模板或页面出错！' : C('VIEW_PATH') . self::$tplName;
        //加载的类
        $classes = '<ol><li>' . join('</li><li>', self::$loadedClasses) . '</li></ol>';
        $runInfo = '<ul>' . $errorInfo . $dbLog . $normalInfo . '</ul>';
        $runInfo = $runInfo == '<ul></ul>' ? '运行正常' : $runInfo;
        $output  = <<<DOLR
       <div class="clearfix"></div>
       <div id="dolrTraceTool" style="display:block;" onclick="document.getElementById('DolrPHPtraceInfo').style.display='block';this.style.display='none';">TRACE</div>
       <div class="traceInfo" id="DolrPHPtraceInfo" style="display:none;">
		<div class="head"><span class="hd left">DolrPHP TRACE:</span><span class="close" onclick="var x=document.getElementById('DolrPHPtraceInfo');x.style.display='none';document.getElementById('dolrTraceTool').style.display='block';"><img style="vertical-align:top;" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw=="></span></div>
			<div class="content">
			<p><span>运行用时:</span>{$timeUsage} 秒</p>
			<p><span>内存占用:</span>{$memUsage}</p>
			<p><span>当前页面:</span>{$currentFile}</p>
			<p><span>模块目录:</span>{$moduleDir}</p>
			<p><span>模板目录:</span>{$tplDir}</p>
			<p><span>[ 加载类 ]:</span></p>
			<div class="classList">
				{$classes}
			</div>
			<p><span>[ 运行信息 ]:</span></p>
			<div class="infoList">
				{$runInfo}
			</div>
		</div>
	<style>
	.clearfix{clear:both;height:0;width:100%;}
	#dolrTraceTool{wdith:100px;position:fixed;bottom:0;right:0;background:#fff;color:#777;padding:5px 10px;font-weight:bold;font-size:16px;box-shadow:0 0 6px #555;}
	.traceInfo{ position:fixed;left:20%;bottom:5%; clear:both; width:80%; text-align:left;margin:0px auto; margin-bottom:60px;border:1px solid #f2f2f2; width:60%; border-radius: 5px;-webkit-box-shadow: 2px 5px 12px #555;box-shadow: 2px 5px 100px #777; word-break:break-all; background: #fff;font-size:12px;padding:20px; color:#000;z-index:9999999;overflow:hidden;}
	.traceInfo .red {color:red;}
	.traceInfo *{margin:0;padding:0;font-family:Consolas,Verdana,"Microsoft YaHei", Geneva, sans-serif;}
	.traceInfo .head{ padding: 0 5px 10px 10px; border-bottom: 1px solid #c0c0c0;  }
	.traceInfo .head .hd{font-size:27px;}
	.traceInfo .head span.close{ position:absolute; right:20px;top:20px; cursor:pointer; padding:0 5px; text-align:center; overflow:hidden; color:#444;font-size:22px;}
	.traceInfo .content { padding: 10px; color: #666; }
	.traceInfo .content .classList,.traceInfo .content .infoList { border: 1px dashed #ccc; padding: 5px; margin: 5px; overflow:auto; max-height:100px;}
	.traceInfo .content p { line-height: 1.5em; }
	.traceInfo .content p span { font-weight: bold; text-align: left; display: inline-block; word-spacing:2px; margin-right: 5px; }
	.traceInfo .content p span.class{ font-weight:normal;}
	.traceInfo ol{ margin-left:30px; color:#444;}
	.traceInfo ul strong.red{ color:#444;}
	.traceInfo ul li{list-style:none;padding:3px 0;}
    /* scrollbar */
    ::-webkit-scrollbar {width: 5px; height: 11px; border: none; background: #ddd !important;}
    ::-webkit-scrollbar-track-piece {border: none; position: absolute; padding: 0; box-shadow: none; background-color:#ddd; border-radius: 1px;}
    ::-webkit-scrollbar-thumb:vertical {background-color: #999; border-radius: 0px; border: none;}
    ::-webkit-scrollbar-thumb:horizontal {background-color: #999; border-radius: 0px; border: none;}
	</style>
	</div>
DOLR;

        return $output;
    }

    /**
     * Trace格式化
     *
     * @param $dbgTrace
     * @param $array
     * @return string
     */
    static function traceFormat($dbgTrace, $array = FALSE) {
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
            $result[] = "#$dbgIndex  " . $dbgInfo['file'] . " (line {$dbgInfo['line']}) -> {$dbgInfo['function']}( " . join(",", $args) . " )";
        }
        if ($array)
            return $result;

        return '<ul><li>' . join('</li><li>', $result) . '</li></ul>';
    }



    /**
     * 错误捕获方法
     * @param  int    $errno   错误号
     * @param  string $errstr  错误字符串
     * @param  string $errfile 错误文件
     * @param  int    $errline 错误行数
     * @return void
     */
    static function errorHandler($errno, $errstr, $errfile, $errline) {
        self::error($errno, $errstr, $errfile, $errline);
        if ($errno == E_RECOVERABLE_ERROR or $errno == E_PARSE or $errno == E_USER_ERROR) {
            $tmp = <<<DOLR
        <!DOCTYPE html>
        <html>
        <head>
            <title>出错啦！</title>
            <style>
            *{margin:0;padding: 0;}
            body{font: 14px/1.5em Consolas,'Microsoft YaHei',Arial,"Microsoft Sans Serif"; background: #999;color: #000;}
            #dolr-container{border-radius: 5px;-webkit-box-shadow: 2px 5px 12px #555;box-shadow: 2px 5px 12px #555;padding: 20px;width: 80%;background-color: white;color: black;line-height: 1.5em;margin: auto;max-width: 750px;min-width: 200px;margin-top: 50px;}
            .header{border-bottom: 1px solid #efefef;padding: 10px 0;}
            .footer{border-top: 1px solid #efefef;padding: 6px 0 0; color: #999;}
            .content{padding: 6px 0;}
            .content .errorString{word-break:break-all;}
            .content .errorString .t{width:50px;display: inline-block;}
            .header h1{font-size: 27px;font-weight: normal;}
            .tright{text-align: right;}
            </style>
        </head>
        <body>
            <div id="dolr-container">
                <div class="header">
                    <h1>出错啦！</h1>
                </div>
                <div class="content">
                    <div class="errorString">
                        <p><span class="t">消息：</span>{$errstr}</p>
                        <p><span class="t">文件：</span>{$errfile}</p>
                        <p><span class="t">位置：</span>第 {$errline} 行</p>
                    </div>
                </div>
                <div class="footer">
                    <div class="tright">&lt;?php define( 'DolrPHP' , 'less is more.' ); ?&gt;</div>
                </div>
            </div>
        </body>
        </html>
DOLR;
            if (Request::isAjax())
                exit(data2json(array( 'info' => '系统出错', 'data' => array( 'info' => $errstr, 'file' => $errfile, 'line' => $errline ) )));
            exit($tmp);
        }
    }

    /**
     * ShutDown捕获方法
     *
     * @return void
     */
    static function shutdownHandler() {
        $errorInfo = error_get_last();
        if (!empty($errorInfo))
            call_user_func_array(array('Trace','errorHandler'), $errorInfo);
    }

    /**
     * 异常处理方法
     * @param  object $exception 异常对象
     * @return void
     */
    static function exceptionHandler($exception) {
        //Trace模板
        $traceLine = "<li class='%s'><span>#%s</span><span title='函数'>%s(%s)</span> <span title='文件位置'>%s : %s</span></li>";
        $msg       = "出错啦！: '%s' 类出现 '%s' 异常. 位置:%s:%s\nStack trace:\n%s\n  thrown in %s on line %s";
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
        error_log($msg);
        $tmp = <<<DOLR
        <!DOCTYPE html>
        <html>
        <head>
            <title>出错啦！</title>
            <style>
            *{margin:0;padding: 0;}
            body{font: 12px/1.5em Consolas,'Microsoft YaHei',Arial,"Microsoft Sans Serif"; background: #999;color: #000;}
            #dolr-container{border-radius: 5px;-webkit-box-shadow: 2px 5px 12px #555;box-shadow: 2px 5px 12px #555;padding: 20px;width:60%;background-color: white;color: black;line-height: 1.5em;margin: auto;min-width: 200px;margin-top: 50px;}
            .header{border-bottom: 1px solid #efefef;padding: 10px 0;}
            .footer{border-top: 1px solid #efefef;padding: 6px 0 0; color: #999;}
            .content{padding: 6px 0;}
            .content .errorString{word-break:break-all;}
            .content .errorString .t{width:50px;display: inline-block;}
            .content .trace{margin:5px 0;padding: 5px 0; border-top: 1px solid #efefef;}
            .content .trace ul li{list-style: none; position: relative;}
            .content .trace ul li.even{background: #f5f5f5;}
            .content .trace ul li span{margin-right: 10px;}
            .header h1{font-size: 27px;font-weight: normal;}
            .tright{text-align: right;}
            </style>
        </head>
        <body>
            <div id="dolr-container">
                <div class="header">
                    <h1>异常!</h1>
                </div>
                <div class="content">
                    <div class="errorString">
                        <p><span class="t">消息：</span>{$exception->getMessage()}</p>
                        <p><span class="t">文件：</span>{$exception->getFile()}</p>
                        <p><span class="t">位置：</span>第 {$exception->getLine()} 行</p>
                    </div>
                    <div class="trace">
                        <ul>
                           {$traceInfo}
                        </ul>
                    </div>
                </div>
                <div class="footer">
                    <div class="tright">&lt;?php define( 'DolrPHP' , 'LESS IS MORE.' ); ?&gt;</div>
                </div>
            </div>
        </body>
        </html>
DOLR;
        if (Request::isAjax())
            exit(data2json(
                array(
                     'info' => '系统出错',
                     'data' => array(
                         'info' => $exception->getMessage(),
                         'file' => $exception->getFile(),
                         'line' => $exception->getLine()
                     )
                )
            )
            );
        exit($tmp);
    }
} // END class Trace

