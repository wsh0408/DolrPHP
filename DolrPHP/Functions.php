<?php defined('DOLR_PATH') or exit('No direct script access.');
/**
 * DolrPHP轻量级PHP开发框架 (内置函数库 )
 *
 * @package     DolrPHP
 * @copyright   Copyright (c) 2012 <www.dolrphp.com>
 * @author      Joychao <Joy@Joychao.cc>
 * @license     Apache 2.0
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 * @link        http://www.dolrphp.com
 * @version     $Id: Joychao $
 **/

//自动加载函数
function dolrAutoLoader($className)
{
    $className = ucfirst($className);
    if (file_exists(Config::get('CONTROLLER_PATH') . $className . '.php')) { //Controller
        include Config::get('CONTROLLER_PATH') . $className . '.php';
    } elseif (file_exists(Config::get('MODEL_PATH') . $className . '.php')) { //Model
        include Config::get('MODEL_PATH') . $className . '.php';
    } elseif (file_exists(DOLR_PATH . $className . '.php')) { //BASE
        include DOLR_PATH . $className . '.php';
    } elseif (file_exists(EXT_PATH . $className . '.php')) { //BASE
        include EXT_PATH . $className . '.php';
    } elseif ($className == 'Dao') { //DB
        include DAO_PATH . 'Dao.php';
    } elseif (false !== stripos($className, 'Dao_Adapter_')) { //DB
        $file = DAO_PATH . str_replace('_', '/', substr($className, 3)) . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
    Trace::L($className, Trace::LOG_TYPE_CLASS);
}


/**
 * 写文件
 *
 * @param string              $path        目标路径
 * @param string|array|object $content     内容
 * @param bool                $serialize   是否json格式化
 *
 * @return  void
 */
function write($path, $content, $serialize = true)
{
    if ($serialize) {
        $content = data2json($content);
    }
    if (!file_exists(dirname($path)) and false === makeDir(dirname($path))) {
        trigger_error('文件写入失败：[路径"' . dirname($path) . '"不存在，且尝试创建失败]');
    } else {
        return file_put_contents($path, $content);
    }
    return false;
}


/**
 * 读取文件
 *
 * @param string  $path       目标路径
 * @param boolean $serialize  是否json格式化
 *
 * @return string|array
 */
function read($path, $serialize = true)
{
    if (!file_exists($path) or !is_readable($path)) {
        trigger_error('文件读取失败：[路径"' . $path . '"不存在或不可读]');
    }
    if ($serialize) {
        return json_decode(file_get_contents($path), true);
    }

    return file_get_contents($path);
}

/**
 * 生成URL
 *
 * @param string $alias  模块名/方法名
 * @param array  $params 其它参数
 *
 * @return string
 */
function url($alias = '', $params = array())
{
    $tmp    = explode('/', $alias == '' ? 'Index/index' : $alias);
    $module = trim(array_shift($tmp));
    $action = trim(end($tmp));

    return Dispatcher::generateUrl($module, $action, $params);
}

/**
 * 不使用模板引擎
 *
 * @param array   $data     数据
 * @param string  $tplPath  模板路径
 * @param boolean $extract  是否提取为独立变量
 *
 * @return void
 */
function display($tplPath = '', $data = array())
{
    //是否自动释放数组为单个变量
    $extract = Config::get('TPL_AUTO_EXTRACT_VAR');
    //如果没有传入的话
    if ($tplPath == '') {
        $module  = Request::$module;
        $action  = Request::$action;
        $tplPath = strtolower("{$module}/{$action}.php");
    }
    if (!stripos($tplPath, '.php')) //没有加后缀的话
        $tplPath .= '.php';
    if ($extract) extract($data); //提取为独立变量
    Trace::L(Config::get('TPL_PATH') . $tplPath, Trace::LOG_TYPE_TEMPLATE);
    include Config::get('TPL_PATH') . $tplPath;
    return;
}

/**
 * COOKIE操作
 *
 * @param  string  $name     名称
 * @param  string  $value    值
 * @param  integer $expire   过期时间(建议只传有效时间s,不传时间辍)
 * @param  string  $path     有效路径
 * @param  string  $domain   有效域
 * @param  boolean $secure   是否加密
 * @param  boolean $httponly 是否只允许http
 *
 * @return mixed
 */
function cookie($name = null, $value = null, $expire = 0,
    $path = '/', $domain, $secure = false, $httponly = false)
{
    $paramNum = func_num_args();
    if ($paramNum > 1) { //设置cookie
        if ($expire < time())
            $expire = time() + $expire;
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    } elseif ($paramNum == 1) { //读取指定cookie
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
    } else { //读取所有
        return $_COOKIE;
    }
}

/**
 * SESSION操作
 *
 * @param  string $name  名称
 * @param  mixed  $value 值
 *
 * @return mixed
 */
function session($name = null, $value = null)
{
    $paramNum = func_num_args();
    if ($paramNum > 1) { //设置session
        return $_SESSION[$name] = $value;
    } elseif ($paramNum == 1) {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : false;
    } else {
        return $_SESSION;
    }
}

/**
 * 格式化路径
 *
 * @param string $path 需要格式化的路径
 *
 * @return string
 */
function pathFormat($path)
{
    return str_replace('//', '/',str_replace('\\', '/', $path));
}

/**
 * ================= 以下为辅助函数 ===================
 */
/**
 * makedir
 *
 * @param  string  $path dirname
 * @param  integer $mode mode
 *
 * @return boolean
 */
function makeDir($path, $mode = 0755)
{
    if (empty($path)) {
        return false;
    }
    if (file_exists($path)) {
        return true;
    }
    return mkdir($path, $mode, true);
}

/**
 * 递归删除目录
 *
 * @param string $dir 目标目录
 *
 * @return boolean
 */
function delDir($dir)
{
    if (!file_exists($dir))
        return true;
    if (!is_dir($dir) || is_link($dir))
        return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!delDir($dir . '/' . $item)) {
            chmod($dir . '/' . $item, 0777);
            if (!delDir($dir . '/' . $item))
                return false;
        }
    }

    return rmdir($dir);
}


/**
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 *
 * @param int $size byte 大小
 * @param int $dec  保留小数位
 *
 * @return string
 */
function byteFormat($size, $dec = 2)
{
    $a   = array( "B", "KB", "MB", "GB", "TB", "PB" );
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1024;
        $pos++;
    }

    return round($size, $dec) . " " . $a[$pos];
}

/**
 * get the ip address
 *
 * @param boolean $toLong 是否转换为整型
 *
 * @return string | int
 */
function getIp($toLong = false)
{
    $ip = null;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
           unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // IP地址合法验证
    return (false !== ip2long($ip)) ? (bool )$tolong ? ip2long($ip) : $ip : '0.0.0.0';
}

/**
 * 发送HTTP状态
 *
 * @param integer $code 状态码
 *
 * @return void
 */
function sendHttpStatus($code)
{
    static $status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    if (isset($status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $status[$code]);
    }
}

/**
 * 数据json返回
 *
 * @param  mixed $data 数据
 *
 * @return string
 */
function data2json($data)
{
    return urldecode(json_encode(murlencode($data)));
}

/**
 * URL编码数据
 *
 * @param  mixed $data 数据
 *
 * @return mixed
 **/
function murlencode($data)
{
    if (is_array($data) || is_object($data)) {
        foreach ($data as $k => $v) {
            if (is_scalar($v)) {
                $v = addslashes($v);
                if (is_array($data)) {
                    $data[$k] = urlencode($v);
                } else if (is_object($data)) {
                    $data->$k = urlencode($v);
                }
            } else if (is_array($data)) {
                $data[$k] = murlencode($v); //递归调用该函数
            } else if (is_object($data)) {
                $data->$k = murlencode($v);
            }
        }
    }

    return $data;
}

/**
 * 字符串截取，支持中文和其他编码
 *
 * @param string   $str     需要转换的字符串
 * @param integer  $start   开始位置
 * @param integer  $length  截取长度
 * @param boolean  $suffix  显示省略符
 * @param string   $charset 编码格式
 *
 * @return string
 */
function msubstr($str, $start = 0, $length = 100, $suffix = true, $charset = "utf-8")
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe] )/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }

    return $suffix ? $slice . '...' : $slice;
}


/**
 * 去除代码中的空白和注释
 *
 * @param string $content 代码内容
 *
 * @return string
 */
function stripWhitespace($content)
{
    $stripStr = '';
    //分析php源码
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<DOLR\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "DOLR;\n";
                    for ($k = $i + 1; $k < $j; $k++) {
                        if (is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if ($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }

    return $stripStr;
}