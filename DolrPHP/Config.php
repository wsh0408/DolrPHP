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
 * Config
 *
 * @package default
 * @author <joy@joychao.cc>
 **/
class Config
{

    /**
     * 应用配置
     *
     * @var array
     */
    private static $_config = array();

    /**
     * 初始化应用配置
     *
     * @param array $appConfig 应用配置
     *
     * @return void
     */
    public static function initialize(array $appConfig = array())
    {
        $defaultConfig = self::_getDefaultConfig();
        self::$_config = array_merge($defaultConfig, $appConfig);
    }

    /**
     * 读取配置
     *
     * @param string $key 选项名
     *
     * @return mixed 对应的值
     */
    public static function get($key)
    {
        if (!isset(self::$_config[$key])) {
            return null;
        }
        return self::$_config[$key];
    }

    /**
     * 修改配置
     *
     * @param string $key   选项名称
     * @param mixed  $value 修改值
     */
    public static function set($key, $value)
    {
        if (!isset(self::$_config[$key])) {
            return null;
        }
        if ($value !== null) {
            self::$_config[$key] = $value;
        }
        return self::$_config[$key] = $value;
    }

    /**
     * 获取默认配置
     *
     * @return array
     */
    public static function _getDefaultConfig()
    {
        return array(
            'DEBUG'               => true,
            'SHOW_TRACE'          => true,
            'DIR_CHECK'           => true,     //目录自动检测
            'ROUTING_TABLE'       => array(),   //路由表
            'DB_ENGINE'           => 'pdo',
            'DB_SET'              => array(
                                      'default' => array(
                                                    'host'    => 'localhost', //数据库主机,
                                                    'dbname'    => 'dolrphp', //数据库名称,
                                                    'user'    => 'root',    //用户名
                                                    'pass'    => '',    //密码
                                                    'prefix'  => '',    //数据表前缀
                                                    'charset' => 'utf8',//字符集
                                                    ),
                                      'writer'  => array(),
                                      'reader'  => array(),
                                    ),
            'COMMON_INCLUDE_PATH' => './',  //需要加入的include_path
            'CONTROLLER_PATH'     => APP_ABS_PATH . 'Controller/',  //控制器目录
            'MODEL_PATH'          => APP_ABS_PATH . 'Model/',       //模型目录
            'TPL_PATH'            => APP_ABS_PATH . 'View/',        //模板目录
            'ASSETS_PATH'         => APP_ABS_PATH . 'Assets/',      //静态文件目录
            'RUNTIME_PATH'        => APP_ABS_PATH . 'Runtime/',     //临时文件目录
            'EXTENSION_PATH'      => APP_ABS_PATH . 'Extension/',   //拓展类目录
            'CONTROLLER_IDENTITY' => 'Controller',  //控制器文件标识（默认Controller）
            'MODEL_IDENTITY'      => 'Model',       //模型文件标识（默认Model）
            'TPL_ENGINE_ON'       => true,  //false表示不使用模板引擎
            'TPL_STYLE'           => '',    //模板风格目录（即模板目录附加目录，默认无）
            'TPL_SUFFIX'          => 'html',    //模板文件后缀（默认html）
            'TPL_CACHE'           => true, //模板缓存 0
            'TPL_COMMON_VAR'      => array(),   //模板公用变量
            'PAGE_404'            => '404.php',        //404页面 此三项都支持绝对，相对和相对模板路径
            'PAGE_SUCCESS'        => 'Success.php',    //成功消息页面,相对于模板目录
            'PAGE_ERROR'          => 'Error.php',      //失败消息页面
            'AJAX_SIGN'           => false, //AJAX表单检测依据
            'DATA_CACHE_ON'       => false, //1|0
            'DATA_CACHE_TYPE'     => 1,     //1:Phps,2:Files,3:EAccelerator,4:Xcache,5:Apc
            'COOKIE_SECRETKEY'    => '%^&DSF*k&GH)s^df52e%&3',       //cookie加密key
            'SESSION_PATH'        => APP_ABS_PATH . 'Runtime/session/', //SESSION存储路径
            'SESSION_AUTO_START'  => true,  //SESSION自动开启
            'XSS_AUTO_FITER_ON'   => false, //XSS自动过滤
        );
    }


} // END class Config