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
 * DolrPHP 输入类
 *
 * @package DolrPHP
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Request
{
    /**
     * 初始化数据
     *
     * @return void
     */
    public static function initialize()
    {
        if (isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
            throw new DolrException("数据可能非法", 1);
        }
        $_GET    = Config::get('XSS_AUTO_FITER_ON') ? self::filterData($_GET) : $_GET;
        $_POST   = Config::get('XSS_AUTO_FITER_ON') ? self::filterData($_POST) : $_POST;
        $_COOKIE = Config::get('XSS_AUTO_FITER_ON') ? self::filterData($_COOKIE) : $_COOKIE;
    }

    /**
     * 过滤XSS代码
     *
     * @param mixed $val 数据
     *
     * @return mixed
     */
    public static function filterData($val)
    {
        if (is_array($val)) {
            $val = array_map('self::removeXSS',$val);
        } elseif (is_string($val)) {
            $val = self::filterData($value);
        }

        return $val;
    }

    /**
     * 是否为ajax请求
     *
     * @return boolean
     */
    public static function isAjax()
    {
        //如果自定义了AJAX请求标记
        if (Config::get('AJAX_SIGN')) {
            return isset($_REQUEST[Config::get('AJAX_SIGN')])
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
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * 是否为GET请求
     *
     * @return boolean
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * 是否为机器人访问
     *
     * @return boolean
     */
    public static function isRobot()
    {
        static $robot = NULL;
        if (is_null($robot)) {
            $spiders  = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
            $browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
            if (preg_match("/($browsers)/", $_SERVER['HTTP_USER_AGENT'])) {
                $robot = false;
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
    public static function isAgent()
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
    public static function method($check = false)
    {
        if ($check) // = isPost,isGet
            return $_SERVER['REQUEST_METHOD'] == strtoupper($check);

        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * XSS过滤,去除代码中XSS跨站脚本
     *
     * @param  string $val 来源内容
     *
     * @return string
     */
    public static function removeXSS($val)
    {
        $val    = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }
        $ra1 = array(
                'javascript',
                'vbscript',
                'expression',
                'applet',
                'meta',
                'xml',
                'blink',
                'link',
                'style',
                'script',
                'embed',
                'object',
                'iframe',
                'frame',
                'frameset',
                'ilayer',
                'layer',
                'bgsound',
                'title',
                'base'
               );
        $ra2 = array(
                'onabort',
                'onactivate',
                'onafterprint',
                'onafterupdate',
                'onbeforeactivate',
                'onbeforecopy',
                'onbeforecut',
                'onbeforedeactivate',
                'onbeforeeditfocus',
                'onbeforepaste',
                'onbeforeprint',
                'onbeforeunload',
                'onbeforeupdate',
                'onblur',
                'onbounce',
                'oncellchange',
                'onchange',
                'onclick',
                'oncontextmenu',
                'oncontrolselect',
                'oncopy',
                'oncut',
                'ondataavailable',
                'ondatasetchanged',
                'ondatasetcomplete',
                'ondblclick',
                'ondeactivate',
                'ondrag',
                'ondragend',
                'ondragenter',
                'ondragleave',
                'ondragover',
                'ondragstart',
                'ondrop',
                'onerror',
                'onerrorupdate',
                'onfilterchange',
                'onfinish',
                'onfocus',
                'onfocusin',
                'onfocusout',
                'onhelp',
                'onkeydown',
                'onkeypress',
                'onkeyup',
                'onlayoutcomplete',
                'onload',
                'onlosecapture',
                'onmousedown',
                'onmouseenter',
                'onmouseleave',
                'onmousemove',
                'onmouseout',
                'onmouseover',
                'onmouseup',
                'onmousewheel',
                'onmove',
                'onmoveend',
                'onmovestart',
                'onpaste',
                'onpropertychange',
                'onreadystatechange',
                'onreset',
                'onresize',
                'onresizeend',
                'onresizestart',
                'onrowenter',
                'onrowexit',
                'onrowsdelete',
                'onrowsinserted',
                'onscroll',
                'onselect',
                'onselectionchange',
                'onselectstart',
                'onstart',
                'onstop',
                'onsubmit',
                'onunload'
               );
        $ra  = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                // add in <> to nerf the tag
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
                // filter out the hex tags
                $val = preg_replace($pattern, $replacement, $val);
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }

        return $val;
    }

} // END class Request
