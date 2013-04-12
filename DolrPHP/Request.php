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
 * DolrPHP输入控制类[继承自Router]
 *
 * @package DolrPHP.Base
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Request
{
    /**
     * 初始化数据
     * @return void
     */
    static public function init() 
    {
        if(isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
            throw new Exception("数据可能非法", 1);
        }
        $_GET    = C('XSS_AUTO_FITER_ON') ? self::removeXSS($_GET) : $_GET;
        $_POST   = C('XSS_AUTO_FITER_ON') ? self::removeXSS($_POST) : $_POST;
        $_COOKIE = C('XSS_AUTO_FITER_ON') ? self::removeXSS($_COOKIE) : $_COOKIE;
    }

    /**
     * 过滤XSS代码
     *
     * @param  mixed $val 数据
     * @return mixed
     */
    static public function removeXSS($val) 
    {
        if (is_array($val)) {
            $val = array_map('remove_xss',$val); //Inc/functions.php
        } elseif (is_string($val)) {
            $val = remove_xss($value); //Inc/functions.php
        }

        return $val;
    }

    /**
     * 是否为ajax请求
     *
     * @return boolean
     */
    static public function isAjax() 
    {
        //如果自定义了AJAX请求标记
        if (C('AJAX_SIGN')) {
            return isset($_REQUEST[C('AJAX_SIGN')]) 
                    || (isset($_SERVER["HTTP_X_REQUESTED_WITH"])
                    && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest");
        }

        return isset($_SERVER["HTTP_X_REQUESTED_WITH"])
                && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
    }

    /**
     * 是否为POST请求
     *
     * @return boolean
     */
    static public function isPost() 
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * 是否为GET请求
     *
     * @return boolean
     */
    static public function isGet() 
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * 是否为机器人访问
     * 
     * @return boolean
     */
    static public function isRobot() 
    {
        static $robot = NULL;
        if (is_null($robot)) {
            $spiders  = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
            $browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
            if (preg_match("/($browsers)/", $_SERVER['HTTP_USER_AGENT'])) {
                $_obot = false;
            } elseif (preg_match("/($spiders)/", $_SERVER['HTTP_USER_AGENT'])) {
                $robot = true;
            } else {
                $robot = false;
            }
        }

        return $robot;
    }

    /**
     * 是否为代理访问
     * 
     * @return boolean
     */
    static public function isAgent() 
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] 
                || $_SERVER['HTTP_VIA']
                || $_SERVER['HTTP_PROXY_CONNECTION'] 
                || $_SERVER['HTTP_USER_AGENT_VIA'];
    }

    /**
     * 返回或者检测请求方法
     * 
     * @param  boolean $check 检测方法名
     * 
     * @return boolean|string
     */
    static public function method($check = false) 
    {
        if ($check) // = isPost,isGet
            return $_SERVER['REQUEST_METHOD'] == strtoupper($check);

        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 获取当前URL
     *
     * @param boolean $array 是否以数组形式返回
     * 
     * @return string
     */
    static public function getUrl($array = false) 
    {
        return current_urli($array);
    }
} // END class Request
?>