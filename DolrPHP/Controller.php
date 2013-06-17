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
 * 系统总控制器类
 *
 * @package DolrPHP
 * @author  Joychao <Joy@Joychao.cc>
 **/
class Controller
{

    /**
     * initialize
     *
     * @return void
     */
    public function __init()
    {
        # do nothing
    }

    /**
     * 操作失败提示
     *
     * @param  string $msg   消息
     * @param  string $url   跳转URL
     * @param  int    $delay 跳转延迟时间
     *
     * @return void
     */
    public function error($msg, $url = null, $delay = 2)
    {
        $this->_jump('error', $msg, $url, $delay);
        exit;
    }

    /**
     * 操作成功提示
     *
     * @param  string  $msg       消息
     * @param  string  $url       跳转URL
     * @param  int     $delay     跳转延迟时间
     *
     * @return void
     */
    public function success($msg, $url = null, $delay = 2)
    {
        $this->_jump('success', $msg, $url, $delay);
        exit;
    }

    /**
     * 跳转提示
     *
     * @param string  $type      类型
     * @param string  $msg       消息
     * @param string  $url       跳转URL
     * @param integer $delay     跳转延迟时间
     *
     * @return void
     */
    private function _jump($type, $msg, $url, $delay = 2)
    {
        if (is_null($url)) {
            $url = $_SERVER['HTTP_REFERER']; //来源页面
        }
        $tpl = Config::get('PAGE_' . strtoupper($type));
        $this->set('message', $msg);
        $this->set('url', $url);
        $this->set('delay', $delay);
        $this->display($tpl);
    }

    /**
     * AJAX返回
     *
     * @param mixed   $data 返回的数据
     * @param string  $info 消息
     *
     * @return string
     */
    public function ajax($data, $info = '')
    {
        if (!headers_sent()) {
            //header('content-type: application/json; charset=utf-8');
        }
        exit(data2json(array('info' => $info, 'data' => $data)));
    }

    /**
     * 分配变量[使用模板引擎时适用]
     *
     * @param string $varName 模板变量名
     * @param mixed  $data    变量值
     *
     * @return void
     */
    public function set($varName, $data)
    {
        App::$template_var = array_merge(App::$template_var, array($varName => $data));
    }

    /**
     * 渲染输出
     *
     * @param string $tplPath 模板路径
     * @param string $cacheId 缓存标记（缓存ID）
     *
     * @return void
     */
    public function display($tplPath = '', $data = array())
    {
        if (!Config::get('TPL_ENGINE_ON')) {
            display($tplPath, $data);
            return;
        }
        //如果没有传入的话
        if ($tplPath == '') {
            $Controller = App::$controllerName;
            $action     = App::$actionName;
            $styleSet   = Config::get('TPL_STYLE');
            $style      = empty($styleSet) ? '' : $styleSet . '/';
            $suffix     = Config::get('TPL_SUFFIX');
            $tplPath    = "{$style}{$Controller}/{$action}.{$suffix}";
        }

        $this->_log($tplPath, Trace::LOG_TYPE_TEMPLATE);
        try {
            App::$tplEngine->display($tplPath, App::$template_var);
        } catch (DolrException $e) {
            throw $e;
        }
    }

    /**
     * 重定向
     *
     * @param string $url 完整的URL
     *
     * @example
     * <pre>
     * $this->go('@Login/index');
     * $this->go('http://www.dolrphp.com');
     * </pre>
     *
     * @return void
     */
    public function go($url)
    {
        if (stripos($url, '@') === 0) {
            $arr = explode('/', trim($url, '@'));
            if (empty($arr)) {
                return false;
            }
            $url = Dispatcher::generateUrl($arr[0], $arr[1]);
        }
        if (headers_sent()) {
            echo '<script>window.location.href="' . $url . '";</script>';
        } else {
            header('Location:' . $url);
        }
    }

    /**
     * 添加模板函数
     *
     * @param string|array $function 要添加的函数，可是使用字符串或者数组
     * $this->addTplFunction(
     *                      array('url' => 'U'),
     *                      array('xxx' => 'xxxx'),
     *                       ...
     *                     );
     * 模板中的用法:
     * {% url('Actor/actorList') %}
     * 编译后:
     * echo U('Actor/actorList');
     *
     * @return boolean
     */
    public function addTplFunction($function)
    {
        return App::addTemplateFunction($function);
    }


    /**
     * 404错误
     *
     * @param string $string error info
     *
     * @return void
     */
    public function error404($string = '404 NOT FOUND!')
    {
        sendHttpStatus(404);
        if (!Config::get('TPL_ENGINE_ON')) {
            trigger_error($string);
        }
        $this->set('errorInfo', $string);
        $this->display(Config::get('PAGE_404'));
    }

    /**
     * __get
     *
     * @param string $proName property name
     *
     * @return mixed
     */
    public function __get($proName)
    {
        switch ($proName) {
            default:
                # code...
                break;
        }
    }

    /**
     * log
     *
     * @param string  $string log info
     * @param integer $type   log type
     *
     * @return void
     */
    private function _log($string, $type = Trace::LOG_TYPE_ERROR)
    {
        Trace::L($string, $type);
    }

    /**
     * 错误/异常处理
     *
     * @param string $errorInfo 错误或异常信息
     *
     * @return void
     */
    private function _error($errorInfo)
    {
        throw new DolrException($errorInfo);
    }

}// END class Controller
