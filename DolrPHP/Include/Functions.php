<?php defined('DOLR_PATH') or exit('No direct script access.');
/**
 * DolrPHP轻量级PHP开发框架 (内置函数库 )
 *
 * @package     DolrPHP.Db
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
    if (file_exists(C('CONTROLLER_PATH') . $className . '.php')) { //Controller
        include C('CONTROLLER_PATH') . $className . '.php';
    } elseif (file_exists(C('MODEL_PATH') . $className . '.php')) { //Model
        include C('MODEL_PATH') . $className . '.php';
    } elseif (file_exists(DOLR_PATH . $className . '.php')) { //BASE
        include DOLR_PATH . $className . '.php';
    } elseif ($className == 'Db') { //DB
        include DB_PATH . 'Db.php';
    } elseif (false !== stripos($className, 'Db_')) { //DB
        include DB_PATH . str_replace('_', '/', substr($className, 3)) . '.php';
    } else {
        throw new DolrException('类"' . $className . '"无法加载，文件不存在或名称错误.');
    }
    Trace::L($className, Trace::LOG_TYPE_CLASS);
}

/**
 * 获取和设置配置项
 *
 * @param string $key
 * @param mixed  $value
 *
 * @return mixed
 */
function C($key, $value = null)
{
    if (!isset(App::$config[$key])) return null;
    if ($value !== null)
        App::$config[$key] = $value;

    return App::$config[$key];
}

/**
 * 实例化模型类
 *
 * @param string $model 模型名
 *
 * @return object
 */
function M($model)
{
    $model         = ucfirst($model);
    $modelFileName = $model . C('MODEL_IDENTITY');
    if (file_exists(C('MODEL_PATH') . $model . '.php'))
        return new $modelFileName($model);
    else
        return new Model($model);
}

/**
 * 写文件
 *
 * @param string              $path        目标路径
 * @param string|array|object $content     内容
 * @param bool                $serialized  是否json格式化
 *
 * @return  void
 */
function W($path, $content, $serialized = true)
{
    if ($serialized)
        $content = data2json($content);
    if (!file_exists(dirname($path)) and false === makeDir(dirname($path)))
        trigger_error('文件写入失败：[路径"' . dirname($path) . '"不存在，且尝试创建失败]');
    else
        file_put_contents($path, $content);
}


/**
 * 读取文件
 *
 * @param string  $path       目标路径
 * @param boolean $serialized 是否json格式化
 *
 * @return string|array
 */
function G($path, $serialized = true)
{
    if (!file_exists($path) or !is_readable($path)) {
        trigger_error('文件读取失败：[路径"' . $path . '"不存在或不可读]');
    }
    if ($serialized) {
        return json_decode(file_get_contents($path), true);
    }

    return file_get_contents($path);
}

/**
 * 生成带前缀的表名
 *
 * @param string $tableName 不带前缀的表名
 *
 * @return string
 */
function T($tableName)
{
    return strtolower(C('DB_PRE') . $tableName);
}

/**
 * 不使用模板引擎（display的别名）
 *
 * @see display();{line:53}
 */
function V()
{
    call_user_func_array('display', func_get_args());
}


/**
 * 生成URL
 *
 * @param string $alias  模块名/方法名
 * @param array  $params 其它参数
 *
 * @return string
 */
function U($alias = '', $params = array())
{
    $tmp    = explode('/', $alias == '' ? 'Index/index' : $alias);
    $module = trim(array_shift($tmp));
    $action = trim(end($tmp));

    return Dispather::generateUrl($module, $action, $params);
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
function display($data = array(), $tplPath = '', $extract = true)
{
    //如果没有传入的话
    if ($tplPath == '') {
        $module  = Request::$module;
        $action  = Request::$action;
        $tplPath = strtolower("{$module}/{$action}.php");
    }
    if (!stripos($tplPath, '.php')) //没有加后缀的话
        $tplPath .= '.php';
    if ($extract) extract($data); //提取为独立变量
    Trace::L(C('VIEW_PATH') . $tplPath, Trace::LOG_TYPE_TEMPLATE);
    include C('VIEW_PATH') . $tplPath;
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
 * ================= 以下为其它辅助函数 ===================
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
function byte_format($size, $dec = 2)
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
 * 获取客户端IP地址
 *
 * @param boolean $toLong 是否转换为整型
 *
 * @return int
 */
function getIp($toLong = false)
{
    return Http::getIp($toLong);
}

/**
 * 获取当前URL
 *
 * @param boolean $array 是否以数组形式返回
 *
 * @return string
 */
function current_urli($array = false)
{
    $sys_protocal = isset($_SERVER['SERVER_PORT'])
                    && $_SERVER['SERVER_PORT'] == '443' ? 'https://'
                    : 'http://';
    $sys_port     = (($_SERVER['SERVER_PORT'] == 80)
                    or ($_SERVER['SERVER_PORT'] == 443)) ? ''
                        : ':' . $_SERVER['SERVER_PORT'];
    $php_self     = $_SERVER['PHP_SELF'] ?
                    $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info    = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url   = isset($_SERVER['REQUEST_URI']) ?
                     $_SERVER['REQUEST_URI']
                     : $php_self . (isset($_SERVER['QUERY_STRING'])
                        ? '?' . $_SERVER['QUERY_STRING']
                        : $path_info);
    $base_url     = $sys_protocal . (isset($_SERVER['HTTP_HOST']) ?
                     $_SERVER['HTTP_HOST'] : '') . $sys_port;
    if ($array)
        return array(
            'protocal'     => $sys_protocal,
            'sys_port'     => $sys_port,
            'base_url'     => $base_url,
            'base_dir_url' => trim($base_url . dirname(substr($php_self, 0,
                                strpos($php_self, '.php'))), '\/') . '/',
            'php_self'     => $php_self,
            'path_info'    => $path_info,
            'relate_url'   => $relate_url
        );

    return $base_url . $relate_url;
}

/**
 * 数据json返回
 *
 * @param  mixed $data
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
 * @param int      $start   开始位置
 * @param int      $length  截取长度
 * @param boolean  $suffix  截断显示字符
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
 * 发送HTTP状态
 *
 * @param integer $code 状态码
 *
 * @return void
 */
function sendHttpStatus($code)
{
    Http::sendHttpStatus($code);
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

/**
 * XSS过滤函数,去除代码中XSS跨站脚本
 *
 * @param  string $val 来源内容
 *
 * @return string
 */
function remove_xss($val)
{
    return Request::removeXSS($val);
}

