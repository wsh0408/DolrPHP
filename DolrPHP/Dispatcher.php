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
 * Dispatcher类
 *
 * @package DolrPHP
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Dispatcher
{
    /**
     * 默认路由表
     * @var array
     */
    public static $routingTable = array(
        'pattern'   => ":module/:action/*",
        'default'   => array( 'module' => 'Index', 'action' => 'index' ),
        'reqs'      => array( 'module' => '[a-zA-Z0-9\.\-_]+', 'action' => '[a-zA-Z0-9\.\-_]+' ),
        'varprefix' => ':',
        'delimiter' => '/',
        'postfix'   => '.html',
        'protocol'  => 'PATH_INFO', // REWRITE STANDARD
    );

    /**
     * 当前模块
     * @var string
     */
    public static $module;

    /**
     * 当前操作
     * @var string
     */
    public static $action;

    /**
     * URL参数
     *
     * @var string
     */
    public static $params;

    /**
     * 初始化
     *
     * @param array $routingTable 路由表
     * 
     * @return void
     */
    public static function initialize(array $routingTable = array()) {
        self::$routingTable = array_merge(self::$routingTable, $routingTable);
        $delimiter = self::$routingTable['delimiter'];
        $postfix   = self::$routingTable['postfix'];
        // http https
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            if (isset($_SERVER['PATH_INFO'])) {
                // 忽略后缀
                $url = $_SERVER['PATH_INFO'];
                if (stripos($_SERVER['PATH_INFO'], $postfix))
                    $url = substr($_SERVER['PATH_INFO'], 0, -strlen($postfix));
                $url = explode($delimiter, trim($url, "/"));
                self::matchingRoutingTable($url);
            } else if (isset($_SERVER["PHP_SELF"]) && isset($_SERVER["SCRIPT_NAME"])) {
                $url = str_replace($_SERVER["SCRIPT_NAME"], '', $_SERVER['PHP_SELF']);
                if (!empty($url)) {
                    // 忽略后缀
                    $url = rtrim($url, "$postfix");
                    $url = explode($delimiter, trim($url, "/"));
                    self::matchingRoutingTable($url);
                } else if (!empty($_GET)) {
                    self::$params = $_GET;
                } else {
                    self::$params['module'] = 'Index';
                    self::$params['action'] = 'index';
                }
            } else if (!empty($_GET)) {
                self::$params = $_GET;
            } else {
                self::$params['module'] = 'Index';
                self::$params['action'] = 'index';
            }
        } else {
            // CLI模式
            $i = 0;
            while ((empty($module) || empty($action)) && isset($_SERVER['argv'][$i])) {
                if (("-m" == $_SERVER['argv'][$i] || "--module" == $_SERVER['argv'][$i]) && isset($_SERVER['argv'][$i + 1])) {
                    $module = $_SERVER['argv'][$i + 1];
                } else if (("-a" == $_SERVER['argv'][$i] || "--action" == $_SERVER['argv'][$i]) && isset($_SERVER['argv'][$i + 1])) {
                    $action = $_SERVER['argv'][$i + 1];
                }
                $i++;
            }
            self::$params['module'] = $module;
            self::$params['action'] = $action;
        }
        self::$module = isset(self::$params['module']) ? self::$params['module'] : 'Index';
        self::$action = isset(self::$params['action']) ? self::$params['action'] : 'Index';
    }

    /**
     * 匹配路由表
     *
     * @param string|array $url
     * 
     * @return void
     */
    public static function matchingRoutingTable($url) {
        $ret       = self::$routingTable['default'];
        $reqs      = self::$routingTable['reqs'];
        $delimiter = self::$routingTable['delimiter'];
        $varprefix = self::$routingTable['varprefix'];
        $postfix   = self::$routingTable['postfix'];
        $pattern   = explode($delimiter, trim(self::$routingTable['pattern'], $delimiter));

        /**
         * 预处理url
         */
        if (is_string($url)) {
            $url = rtrim($url, $postfix); //忽略后缀
            $url = explode($delimiter, trim($url, $delimiter));
        }

        foreach ($pattern as $k => $v) {
            if ($v[0] == $varprefix) {
                // 变量
                $varname = substr($v, 1);
                // 匹配变量
                if (isset($url[$k])) {
                    if (isset(self::$routingTable['reqs'][$varname])) {
                        $regex = "/^" . self::$routingTable['reqs'][$varname] . "\$/i";
                        if (preg_match($regex, $url[$k])) {
                            $ret[$varname] = $url[$k];
                        }
                    }
                }
            } else if ($v[0] == '*') {
                // 通配符
                $pos = $k;
                while (isset($url[$pos]) && isset($url[$pos + 1])) {
                    $ret[$url[$pos++]] = urldecode($url[$pos]);
                    $pos++;
                }
            } else {
                // 静态
            }
        }
        self::$params = $ret;
        $_GET         = array_merge($_GET, self::$params);
    }

    /**
     * 将变量反向匹配路由表, 返回匹配后的url
     *
     * @param array $params
     * 
     * @return string
     */
    public static function reverseMatchingRoutingTable($params) {
        $url       = $params;
        $ret       = self::$routingTable['pattern'];
        $default   = self::$routingTable['default'];
        $reqs      = self::$routingTable['reqs'];
        $delimiter = self::$routingTable['delimiter'];
        $varprefix = self::$routingTable['varprefix'];
        $postfix   = self::$routingTable['postfix'];

        $pattern = explode($delimiter, trim(self::$routingTable['pattern'], $delimiter));

        foreach ($pattern as $k => $v) {
            if ($v[0] == $varprefix) {
                // 变量
                $varname = substr($v, 1);
                // 匹配变量
                if (isset($url[$varname])) {
                    $regex = "/^" . self::$routingTable['reqs'][$varname] . "\$/i";
                    if (preg_match($regex, $url[$varname])) {
                        $ret = str_replace($v, $url[$varname], $ret);
                        unset($url[$varname]);
                    }
                } else if (isset($default[$varname])) {
                    $ret = str_replace($v, $default[$varname], $ret);
                }
            } else if ($v[0] == '*') {
                // 通配符
                $tmp = '';
                foreach ($url as $key => $value) {
                    if (!isset($default[$key])) {
                        $tmp .= $key . $delimiter . rawurlencode($value) . $delimiter;
                    }
                }
                $tmp = rtrim($tmp, $delimiter);
                $ret = str_replace($v, $tmp, $ret);
                $ret = rtrim($ret, $delimiter);
            } else {
                // 静态
            }
        }
        $protocol = strtoupper(self::$routingTable['protocol']);
        if ('REWRITE' == $protocol) {
            $ret = $ret . $postfix;
        } else if ('PATH_INFO' == $protocol) {
            $ret = $_SERVER['SCRIPT_NAME'] . '/' . $ret . $postfix;
        } else {
            $ret = $ret . $postfix;
        }
        $sys_protocal = isset($_SERVER['SERVER_PORT']) 
                        && $_SERVER['SERVER_PORT'] == '443' 
                        ? 'https://' : 'http://';
        $sys_port     = (($_SERVER['SERVER_PORT'] == 80) 
                        or ($_SERVER['SERVER_PORT'] == 443)) 
                        ? '' : ':' . $_SERVER['SERVER_PORT'];

        return $sys_protocal . $_SERVER['HTTP_HOST'] 
                . $sys_port . '/' . ltrim($ret, '/');
    }

    /**
     * 生成URL
     *
     * @param string $module 模块名
     * @param string $action 方法名
     * @param array  $args   其它参数
     * 
     * @return string
     */
    public static function url($module, $action, $args = array()) {
        $args['module'] = $module;
        $args['action'] = $action;

        return self::reverseMatchingRoutingTable($args);
    }

}
