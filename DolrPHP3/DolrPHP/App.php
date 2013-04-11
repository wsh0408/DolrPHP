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

//include class trace
include DOLR_PATH . 'Trace.php';
//include functions
include INC_PATH . 'Functions.php';
//dispatcher
include DOLR_PATH . 'Dispatcher.php';
/**
 * DolrPHP应用基础类
 *
 * @package DolrPHP
 * @author  Joychao <Joy@Joychao.cc>
 **/
class App
{
    /**
     * 应用名称
     *
     * @var string
     */
    public static $name;

    /**
     * 应用的url
     *
     * @var string
     */
    public static $url;

    /**
     * 应用控制器
     * @var object
     */
    public static $controller;

    /**
     * 控制器名称
     * @var string
     */
    public static $ControllerName;

    /**
     * 应用的操作名
     * @var string
     */
    public static $actionName;

    /**
     * 应用的配置
     *
     * @var array
     */
    public static $config = array();

    /**
     * 模板引擎实例
     *
     * @var object
     **/
    public static $tplEngine = null;

    /**
     * initialize app
     * 
     * @return void
     */
    public static function initialize()
    {
        //注册自动加载函数
        spl_autoload_register('dolrAutoLoader');

        //应用目录
        if (!file_exists(APP_PATH)
            && false === make_dir(APP_PATH, 0777)) {
            throw new DolrException('应用目录"' . APP_PATH . '"不存在,尝试创建失败！');
        }

        //目录检测
        self::_initAppDir();

        //加载应用配置文件
        $appConfig = APP_PATH . 'config.php';
        if (!file_exists($appConfig)) {
            $tmp = file_get_contents(INC_PATH . 'ConfigSample.php');
            W($appConfig, $tmp, false);
        }

        //初始化配置
        self::_initAppConfig(include $appConfig);

        //开启session
        if (C('SESSION_AUTO_START')) {
            session_start();
        }

        Dispatcher::initialize(C('ROUTING_TABLE'));

        //获取应用当前的URL并定义为常量
        $appUrlInfo = current_urli(true);
        self::$url = $appUrlInfo['base_url'];

        //将框架拓展目录加载到包含目录
        set_include_path(INC_PATH . PATH_SEPARATOR . get_include_path());

        //包含应用全局调用文件public.php
        self::_includeAppPublicFile();

        //如果使用模板引擎则实例化模板引擎
        if (C('VIEW_ENGINE_ON')) {
            self::_initViewEngine();
        }

        $controller = ucfirst($controller . C('CONTROLLER_IDENTITY'));
        if (!class_exists($controller)) {
            self::$controller = new Controller();
            throw new DolrException("控制器 '{$controller}' 文件不存在！", 1);
        }
        
        self::$controller = new $controller();
        self::$actionName = $action;
    }

    /**
     * 运行App
     *
     * @throws Exception 控制器不存在
     *
     * @return void
     */
    public static function run()
    {
        $action = self::$actionName;
        //call initialize
        if (is_callable(array(self::$controller, 'initialize')))
            self::$controller->initialize();
        //call action
        if (is_callable(array(self::$controller, $action))) {
            self::$controller->$action();
        } else {
            self::$controller->error404();
        }
    }

    /**
     * 实例化模板引擎对象
     *
     * @return object
     */
    private static function _initViewEngine()
    {
        $options = array(
                    'template_dir'    => C('VIEW_PATH'), //模板目录
                    'compile_dir'     => C('RUNTIME_PATH') . 'compile/', //编译目录
                    'cache_dir'       => C('RUNTIME_PATH') . 'cache/', //缓存目录
                    'caching'         => (bool)C('VIEW_CACHE'), //是否缓存
                    'cache_lifetime'  => C('VIEW_CACHE_LIFETIME'), //缓存有效期
                    'tpl_suffix'      => C('VIEW_SUFFIX'), //模板后缀
                    'left_delimiter'  => C('VIEW_LDELIM'), //模板左定界符
                    'right_delimiter' => C('VIEW_RDELIM'), //模板右定界符
                   );
        try {
            $tplEngine = DolrView::getInstance($options);
            $tplEngine->replace = C('VIEW_REPLACEMENT'); //注册替换变量数据
        } catch (DolrException $e) {
            throw $e;
        }
        self::$tplEngine = $tplEngine;
    }

    /**
     * initialize the dirs of App
     *
     * @return void
     */
    private function _initAppDir()
    {
        //设置控制器标识
        $controllerIdentity = C('CONTROLLER_IDENTITY');
        //目录检测与创建
        if (C('DIR_CHECK') or isset($_GET['init_dir'])) {
            $appDirs = array(
                        C('CONTROLLER_PATH'),
                        C('MODEL_PATH'),
                        C('VIEW_PATH'),
                        C('PUBLIC_PATH'),
                        C('RUNTIME_PATH'),
                        C('EXTENSION_PATH'),
                       );
            foreach ($appDirs as $dir) {
                if (!file_exists($dir) && false === make_dir($dir, 0777))
                    throw new DolrException('应用目录"' . $dir . '"不存在,尝试创建失败！');
                if ($dir == C('CONTROLLER_PATH')) {
                    //写入默认控制器
                    W($dir . 'Index' . C('CONTROLLER_IDENTITY'), G(INC_PATH . 'ControllerSample.php'));
                }
            }
        }
    }

    /**
     * 写入默认控制器
     *
     * @return void
     */
    private function _writeDefaultController() {
        $controllerIdentity = C('CONTROLLER_IDENTITY');
        $controller         = C('CONTROLLER_PATH') . 'Index'
                                . C('CONTROLLER_IDENTITY') . '.php';
        if (file_exists($controller))
            return;
        $content = file_get_contents(INC_PATH . 'ControllerSample.php');
        W($controller, $content, false);
    }

    /**
     * 初始化配置
     *
     * @param  array  $appConfig 应用配置
     *
     * @return array
     */
    private static function _initAppConfig(array $appConfig)
    {
        $defaultConfig = include INC_PATH . 'ConfigBase.php';
        self::$config = array_merge($defaultConfig, $appConfig);
        //写入配置文件到缓存
        $configFile = self::$config['RUNTIME_PATH'].'config/config.php';
        if(!file_exists($configFile)
            or filemtime(APP_PATH . 'config.php') > filemtime($configFile)) {
            $content = "<?php\n \$_appConfig = "
                        .var_export(self::$config, true) . ';';
            W($configFile, $content, false);
        }
        include $configFile;
    }

    /**
     * 包含应用全局文件
     *
     * @return void
     */
    private static function _includeAppPublicFile()
    {
        $appCommon = APP_PATH . 'public.php';
        if (!file_exists($appCommon)) {
            $tmp = "<?php\n//此文件为应用共用文件，会在应用全局引用，可以放置公用函数和全局变量";
            W($appCommon, $tmp, false);
        }
        include $appCommon;
    }

    /**
     * log
     * 
     * @param string $string log info
     * @param string $type   log type
     * 
     * @return void
     */
    private static function _log($string, $type = 'error')
    {
        Trace::L($string, $type);
    }

} // END class Dolr