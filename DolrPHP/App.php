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

// include class trace
include DOLR_PATH . 'Trace.php';
// include config
include DOLR_PATH . 'Config.php';
// include functions
include DOLR_PATH . 'Functions.php';
// include dispatcher
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
    public static $controllerName;

    /**
     * 应用的操作名
     * @var string
     */
    public static $actionName;

    /**
     * 模板引擎实例
     *
     * @var object
     **/
    public static $tplEngine = null;

    /**
     * 模板变量
     *
     * @var array
     */
    public static $template_var = array();

    /**
     * App 运行结果
     * @var string
     */
    private static $_appRunContent = null;

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
        self::_checkAppPath();

        //初始化配置
        $appConfig = self::_getAppConfig();
        Config::initialize($appConfig);

        //设置错误日志文件路径
        self::_setErrorLogPath();

         //初始化路由
        Dispatcher::initialize((array)Config::get('ROUTING_TABLE'));
        $controllerName       = Dispatcher::$module;
        $action               = Dispatcher::$action;

        //目录检测
        self::_initAppDir();

        //开启session
        if (Config::get('SESSION_AUTO_START')) {
            session_start();
        }

        //获取应用当前的URL并定义为常量
        self::$url = Dispatcher::generateUrl($controllerName, $action);

        //包含应用全局调用文件public.php
        self::_includeAppPublicFile();

        //初始化控制器
        $controller = ucfirst($controllerName . Config::get('CONTROLLER_IDENTITY'));
        if (!class_exists($controller)) {
            self::$controller = new Controller();
            throw new DolrException("控制器 '{$controller}' 文件不存在！");
        }
        self::$controller = new $controller();
        self::$controllerName = $controllerName;
        self::$actionName     = $action;

        //如果使用模板引擎则实例化模板引擎
        if (Config::get('TPL_ENGINE_ON')) {
            self::_initViewEngine();
            self::_setTemplateCommonVar();
            self::_setTemplateCommonFunction();
        }
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
        if (is_callable(array(self::$controller, 'init')))
            self::$controller->init();
        //call action
        if (is_callable(array(self::$controller, $action))) {
            try {
                ob_start();
                self::$controller->$action();
                self::$_appRunContent = ob_get_contents();
                ob_end_clean();
            } catch (DolrException $e) {
                throw $e;
            }
            self::_response();
        } else {
            self::$controller->error404();
        }
    }

    /**
     * 输出APP运行结果
     *
     * @return void
     */
    private static function _response()
    {
        if (self::$_appRunContent)
            echo self::$_appRunContent;
    }

    /**
     * 检测应用目录并尝试创建
     *
     * @return void
     */
    private static function _checkAppPath()
    {
        if (!file_exists(APP_PATH)
            && false === makeDir(APP_PATH, 0777)) {
            throw new DolrException('应用目录"' . APP_PATH . '"不存在,尝试创建失败！');
        }
        defined('APP_ABS_PATH') || define('APP_ABS_PATH',
                                    str_replace('\\', '/', realpath(APP_PATH) . '/'));
    }

    /**
     * 实例化模板引擎对象
     *
     * @return object
     */
    private static function _initViewEngine()
    {
        $config = array('debug' => Config::get('DEBUG'));
        if ((bool)Config::get('TPL_CACHE')) {
            $config = array_merge($config, array('cache' => Config::get('RUNTIME_PATH') . 'cache/'));
        }
        try {
            require_once DOLR_PATH . '/Twig/Autoloader.php';
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem(array(TPL_PATH, Config::get('TPL_PATH')));
            $twig   = new Twig_Environment($loader, $config);
            self::$tplEngine = $twig;
        } catch (DolrException $e) {
            throw $e;
        }
    }

    /**
     * 设置模板全局公用变量
     *
     * @example
     * <pre>
     * array(
     *    'IMG_PATH' => '/assets/images/',
     *    'CSS_PATH' => '/assets/css/',
     *    'JS_PATH'  => '/assets/js/',
     * );
     * </pre>
     *
     * @return void
     */
    private static function _setTemplateCommonVar()
    {
        $baseVar = array(
                    'APP_ROOT'        => APP_ROOT,
                    'CURRENT_URL'     => self::$url,
                    'ACTION_NAME'     => self::$actionName,
                    'CONTROLLER_NAME' => self::$controllerName,
                    //TODO: other var
                   );
        self::$template_var = array_merge(
                                self::$template_var, $baseVar,
                                Config::get('TPL_COMMON_VAR')
                              );
    }

    /**
     * 设置模板通用函数
     *
     * @return void
     */
    private static function _setTemplateCommonFunction()
    {
        $functionArray = array(
                          'url'         => 'createUrl',
                          'cookie'      => 'cookie',
                          'session'     => 'session',
                          'byte_format' => 'byteFormat',
                          'msubstr'     => 'msubstr',
                         );
        self::addTemplateFunction($functionArray);
    }

    /**
     * 添加模板函数
     *
     * @param string|array $function 要添加的函数，可是使用字符串或者数组
     *
     * @example
     * <pre>
     *
     * App::addTemplateFunction(
     *                      array('url' => 'U'),
     *                      array('xxx' => 'xxxx'),
     *                       ...
     *                     );
     * in template:
     * {% url('Actor/actorList') %}
     * act as:
     * echo U('Actor/actorList');
     * ---------------- or --------------------------
     * App::addTemplateFunction('url');
     * in template:
     * {% url('Actor/actorList') %}
     * act as:
     * echo url('Actor/actorList');
     * </pre>
     *
     * @return boolean
     */
    public static function addTemplateFunction($function)
    {
        if (is_array($function)) {
            foreach ($function as $alias => $functionName) {
                    self::$tplEngine->addFunction(
                        new Twig_SimpleFunction($alias, function() use ($functionName){
                            echo call_user_func_array($functionName, func_get_args());
                        }));
            }

            return true;
        } elseif (is_string($function)) {
            self::$tplEngine->addFunction(
                        new Twig_SimpleFunction($function, function() use ($functionName){
                            echo call_user_func_array($function, func_get_args());
                        }));

            return true;
        }

        return false;
    }

    /**
     * initialize the dirs of App
     *
     * @return void
     */
    private static function _initAppDir()
    {
        //目录检测与创建
        if (Config::get('DIR_CHECK')) {
            $appDirs = array(
                        Config::get('CONTROLLER_PATH'),
                        Config::get('MODEL_PATH'),
                        Config::get('TPL_PATH'),
                        Config::get('ASSETS_PATH'),
                        Config::get('RUNTIME_PATH'),
                        Config::get('EXTENSION_PATH'),
                       );
            foreach ($appDirs as $dir) {
                if (!file_exists($dir) && false === makeDir($dir, 0777)) {
                    throw new DolrException('应用目录"' . $dir . '"不存在,尝试创建失败！');
                }
                if ($dir == Config::get('CONTROLLER_PATH')) {
                    //设置控制器标识
                    self::_writeDefaultController();
                }
            }
        }
    }

    /**
     * 写入默认控制器
     *
     * @return void
     */
    private static function _writeDefaultController() {
        $controller = Config::get('CONTROLLER_PATH') . 'Index' . Config::get('CONTROLLER_IDENTITY') . '.php';
        if (file_exists($controller)) {
            return;
        }
        $demo = "<?php\n//这是一个demo 控制器\nclass Index__IDENTITY__ extends Controller\n{\n
            \tpublic function index()\n\t{\n\t\t\$this->display('welcome.php');\n\t}\n}";
        //写入默认控制器
        $content = str_replace('__IDENTITY__', Config::get('CONTROLLER_IDENTITY'), $demo);
        write($controller, $content, false);
    }

    /**
     * 读取应用配置
     *
     * @return array
     */
    private static function _getAppConfig()
    {
        try {
            //加载应用配置文件
            $appConfigPath = APP_PATH . 'config.php';
            if (!file_exists($appConfigPath)) {
                $demo = "<?php\nreturn array(\n\t\t//'设置项1' => '设置值1',
                    \n\t\t//'设置项2' => '设置值2',\n\t\t// ... \n\t );";
                write($appConfigPath, $demo, false);
            }
            return include $appConfigPath;
        } catch (Exception $e) {
            throw new Exception("配置文件有语法错误");

        }

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
            write($appCommon, $tmp, false);
        }
        include $appCommon;
    }

    /**
     * log
     *
     * @param string $string log info
     * @param int    $type   log type
     *
     * @return void
     */
    private static function _log($string, $type = Trace::LOG_TYPE_ERROR)
    {
        Trace::L($string, $type);
    }

    /**
     * 设置日志位置
     *
     * @return void
     */
    private static function _setErrorLogPath()
    {
        ini_set('log_errors', true);
        ini_set('error_log', Config::get('RUNTIME_PATH') . 'error.log');
    }

} // END class Dolr
