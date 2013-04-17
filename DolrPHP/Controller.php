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
     * 操作失败提示
     * @param  string $msg   消息
     * @param  string $url   跳转URL
     * @param  int    $delay 跳转延迟时间
     * @return void
     */
    public function error($msg, $url, $delay)
    {
        $this->_jump('error', $msg, $url, $delay);
    }

    /**
     * 操作成功提示
     * @param  string  $msg       消息
     * @param  string  $url       跳转URL
     * @param  int     $delay     跳转延迟时间
     * @return void
     */
    public function success($msg, $url, $delay)
    {
        $this->_jump('success', $msg, $url, $delay);
    }

    /**
     * 跳转提示
     * @param string   $type      类型
     * @param  string  $msg       消息
     * @param  string  $url       跳转URL
     * @param  int     $delay     跳转延迟时间
     * @return void
     */
    private function _jump($type, $msg, $url, $delay)
    {
        $tpl = strtolower(C('PAGE_' . strtoupper($type)));
        $this->assign('message', $msg);
        $this->assign('url', $url);
        $this->assign('delay', $delay);
        $this->display($tpl);
    }

    /**
     * AJAX返回
     *
     * @param mixed  返回的数据
     * @param string 消息
     * @return string
     */
    public function ajax($data, $info = '')
    {
        if (!headers_sent()) {
            header('content-type: application/json; charset=utf-8');
        }
        exit(data2json(array( 'info' => $info, 'data' => $data )));
    }

    /**
     * 分配变量[使用模板引擎时适用]
     *
     * @param  string     $varName     模板变量名
     * @param  mixed      $data        变量值
     *
     * @return void
     */
    public function set($varName, $data)
    {
        App::$tplEngine->set($varName, $data);
    }

    /**
     * 渲染输出
     *
     * @param string $tplPath 模板路径
     * @param string $cacheId 缓存标记（缓存ID）
     *
     * @return void
     */
    public function display($tplPath = '', $cacheId = null)
    {
        if (!C('VIEW_ENGINE_ON')) {
            $this->_error('未开启DolrView模板引擎，如需使用请配置"DOLRVIEW" => true',1);
        }
        $suffix = strval(C('VIEW_SUFFIX'));
        //如果没有传入的话
        if ($tplPath == '') {
            $Controller  = App::$ControllerName;
            $action  = App::$actionName;
            $tplPath = strtolower("{$Controller}/{$action}.{$suffix}");
        }

        //如果不是一个具体的文件路径，加上后缀
        if (!file_exists($tplPath)) {
            if (!stripos($tplPath, ".{$suffix}")) { //没有加后缀的话
                $tplPath .= ".{$suffix}";
            }
            $tplPath = C('VIEW_STYLE') . $tplPath;
        }

        if (!file_exists($tplPath)) {
            throw new DolrException("模板文件'{$tplPath}'不存在！");
        }
        $this->_log($tplPath, Trace::LOG_TYPE_TEMPLATE);
        try {
            App::$tplEngine->display($tplPath, $cacheId = null);
        } catch (DolrException $e) {
            throw $e;
        }
    }

    /**
     * 重定向
     *
     * @param string $url 完整的URL
     *
     * @return void
     */
    public function go($url)
    {
        if (headers_sent()) {
            echo '<script>window.location.href="' . $url . '";</script>';
        } else {
            header('Location:' . $url);
        }
    }


    /**
     * 404错误
     *
     * @param string $string error info
     *
     * @return void
     */
    public function error404($string = '')
    {
        send_http_status(404);
        if (!C('VIEW_ENGINE_ON')) {
            trigger_error($string);
        }
        $this->set('errorInfo', $string);
        $this->display(C('PAGE_404'));
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
