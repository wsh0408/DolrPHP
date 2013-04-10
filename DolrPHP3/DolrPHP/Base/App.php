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
 * DolrPHP应用基础类
 *
 * @package DolrPHP.Base
 * @author  Joychao <Joy@Joychao.cc>
 **/
class App
{
    /**
     * 应用名称
     * 
     * @var string
     */
    private static $name;

    /**
     * 应用的url
     * 
     * @var string
     */
    private static $url;

    /**
     * 应用的配置
     * 
     * @var array
     */
    private static $config = array();

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
            && false === mkdir(APP_PATH, 0777, true)) {
            throw new Exception('应用目录"' . APP_PATH . '"不存在,尝试创建失败！');
        }
        
        //加载应用配置文件
        $appConfig = APP_PATH . 'config.php';
        if (!file_exists($_appConfig)) {
            $tmp = file_get_contents(EXT_PATH . 'configSample.php');
            W($appConfig, $tmp, false);
        }

        //初始化配置
        self::initAppConfig($appConfig);

        //开启session
        if (C('SESSION_AUTO_START')) {
            session_start();
        }

        //获取应用当前的URL并定义为常量
        $appUrlInfo = current_urli(true);
        self::$url = $appUrlInfo['base_url'];

        //将框架拓展目录加载到包含目录
        set_include_path(EXT_PATH . PATH_SEPARATOR . get_include_path());

        //包含应用全局调用文件public.php
        self::includeAppPublicFile();
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
        $module = ucfirst(MODULE_NAME . C('CONTROLLER_IDENTITY'));
        $action = ACTION_NAME;
        if (!class_exists($module)) {
            throw new DolrException("控制器 '{$module}' 文件不存在！", 1);
        }
        $controller = new $module();
        //call initialize
        if (is_callable(array($controller, 'initialize')))
            $controller->initialize();
        //call action
        if (is_callable(array($controller, $action))) {
            $controller->$action();
        } else {
            $controller = new Controller();
            $controller->_404();
        }
    }

    /**
     * 实例化模板引擎对象
     * 
     * @return object
     */
    private public static function getEngine() 
    {
        $options           = array(
            'template_dir'    => C('VIEW_PATH'), //模板目录
            'compile_dir'     => C('RUNTIME_PATH') . 'compile/', //编译目录
            'cache_dir'       => C('RUNTIME_PATH') . 'cache/', //缓存目录
            'caching'         => (bool)C('VIEW_CACHE'), //是否缓存
            'cache_lifetime'  => intval(C('VIEW_CACHE_LIFETIME')), //缓存有效期
            'tpl_suffix'      => strval(C('VIEW_SUFFIX')), //模板后缀
            'left_delimiter'  => strval(C('VIEW_LDELIM')), //模板左定界符
            'right_delimiter' => strval(C('VIEW_RDELIM')), //模板右定界符
        );
        $tplEngine          = DolrView::getInstance($options);
        $tplEngine->replace = C('VIEW_REPLACEMENT'); //注册替换变量数据

        return $tplEngine;
    }

    /**
     * initialize the dirs of App
     * 
     * @return void
     */
    private function initAppDir()
    {
        //设置控制器标识
        $controllerIdentity = C('CONTROLLER_IDENTITY'));
        //目录检测与创建
        if (C('DIR_CHECK') or isset($_GET['dir_init'])) {
            $appDirs = array(
                        C('CONTROLLER_PATH'),
                        C('MODEL_PATH'),
                        C('VIEW_PATH'),
                        C('PUBLIC_PATH'),
                        C('RUNTIME_PATH'),
                        C('EXTENSION_PATH'),
                       );
            foreach ($appDirs as $dir) {
                if (!file_exists($dir) and false === mkdir($dir, 0777, true))
                    throw new DolrException('应用目录"' . $dir . '"不存在,尝试创建失败！');
                if ($dir == C('CONTROLLER_PATH'))
                    //写入默认控制器
                    writeDefaultController();
            }
        }
    }

    /**
     * 写入默认控制器
     * 
     * @return void 
     */
    private function writeDefaultController() {
        $controllerIdentity = C('CONTROLLER_IDENTITY');
        $controller         = C('CONTROLLER_PATH') . 'Index' 
                                . C('CONTROLLER_IDENTITY') . '.php';
        if (file_exists($controller))
            return;
        $content = file_get_contents(EXT_PATH . 'controllerSample.php');
        W($controller, $content, false);
    }

    /**
     * 初始化配置
     * 
     * @param  array  $appConfig 应用配置
     * 
     * @return array
     */
    public static function initAppConfig($appConfig = array()) 
    {
        $defaultConfig = include EXT_PATH . 'configSample.php';
        $this->config = array_merge($defaultConfig, $_appConfig);
        //写入配置文件到缓存
        $configFile = self::config['RUNTIME_PATH'].'config/config.php';
        if(!file_exists($configFile) 
            or filemtime(APP_PATH . 'config.php') > filemtime($configFile))
        $content = "<?php\n \$_appConfig = "
                    .var_export(self::config, true) . ';';
        W($configFile, $content, false);
        include $configFile;
    }

    /**
     * 包含应用全局文件
     * 
     * @return void
     */
    private function includeAppPublicFile()
    {
        $appCommon = APP_PATH . 'public.php';
        if (!file_exists($appCommon)) {
            $tmp = "<?php\n//此文件为应用共用文件，会在应用全局引用，可以放置公用函数和全局变量";
            W($appCommon, $tmp, false);
        }
        include $appCommon;
    }

} // END class Dolr