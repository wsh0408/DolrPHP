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
 * 文件上传类，只支持单文件上传
 */
class Upload{


    //目标保存路径
    public $savePath = './Public/uploads/files/';

    //最大尺寸(kb)
    public $maxSize = 4096;

    //允许的文件后缀
    public $allowSuffix = array('jpg','png','jpeg','bmp','gif');

    //是否启用Mime检测
    public $_checkMime = false;

    //允许的MIME类型
    public $allowMime = array(
                         'image/jpeg',
                         'image/jpg',
                         'image/png',
                         'image/x-png',
                         'image/wbmp',
                         'image/gif'
                        );

    //是否启用随机文件名
    public $randName = true;

    //随机文件名前缀
    public $filePrefix;

    //保存的文件名
    protected $fileName;

    /**
     * 构造函数
     *
     * @param string $savePath 文件存储路径
     */
    public function __construct($savePath = ''){
        if (empty($savePath)){
            $this->_log('文件保存路径未设置,采用默认路径:./Public/uploads/');
        }else{
            $this->savePath = rtrim($savePath,'/').'/';
        }
        if (!file_exists($this->savePath)) {
            mkdir($this->savePath) || $this->_log($this->savePath . '不存在');
        }

    }

    /**
     * 上传文件
     *
     * @param string $formName 表单名称（input[type=file]）
     * @param string $dstName  存储名称（相对于存储路径）
     */
    public function uploadFile($formName, $dstName = ''){
        if (empty($_FILES) || !isset($_FILES[$formName])) {
            $this->_log('没有选择上传文件!');
            return false;
        }
        $name    = $_FILES[$formName]['name'];
        $type    = $_FILES[$formName]['type'];
        $size    = $_FILES[$formName]['size'];
        $tmpName = $_FILES[$formName]['tmp_name'];
        $error   = $_FILES[$formName]['error'];
        if ($error) {
            $this->info[] = '文件[' . $name . ']错误:' . $this->_getError($error);
            return false;
        }
        //文件后缀
        $arr = explode('.', $name);
        $suffix = array_pop($arr);
        //检测后缀
        if (!in_array($suffix, $this->allowSuffix)){
            $this->_log('文件[' . $name . ']的后缀' . $suffix . '不允许上传!');
            return false;
        }
        //检测大小
        if (!$this->_checkSize($size)) {
            $this->_log('文件[' . $name . ']的大小超过限制:' . $this->maxSize . '(单位:M)');
            return false;
        }
        //检测MIME
        if ($this->_checkMime && !$this->_checkMime($type)) {
            $this->_log('文件[' . $name . ']的Mime类型不允许上传!');
            return false;
        }
        //获取保存文件名
        if (empty($dstName)) {
            $dstName = $this->_getSaveName($name, $suffix);
        }
        //移动,保存相关信息
        if ($this->_save($tmpName, $dstName)) {
            $this->fileName = basename($dstName);
            return $this->fileName;
        } else {
            return false;
        }
    }

    /**
     * 保存文件
     *
     * @param string $tmp_name    临时文件名
     * @param string $destination 目标路径
     *
     * @return boolean
     */
    protected function _save($tmp_name, $destination)
    {
        return move_uploaded_file($tmp_name, $destination);
    }

    //设置文件名
    protected function _getSaveName($fileName, $suffix)
    {
        if ($this->randName) {//随机文件名:public/uploads/files/pre_2424551223.jpg
            return $this->savePath . $this->filePrefix . uniqid() . '.' . $suffix;
        } else {//不启用随机文件名:public/uploads/files/pre_a.jpg
            return $this->savePath . $this->filePrefix . $fileName;
        }
    }

    //检测尺寸
    protected function _checkSize($size)
    {
        return ($size / 1024) < $this->maxSize;
    }

    //检测Mime
    protected function _checkMime($mime)
    {
        return in_array($mime, $this->allowMime);
    }

    //检测后缀
    protected function _checkSuffix($suffix)
    {
        return in_array($suffix, $this->allowSuffix);
    }

    //设置属性
    public function __set($proName, $value)
    {
        if (isset($this->$proName)) {
            $this->$proName=$value;
        } else {
            $this->_log('Upload类中不存在属性:'.$proName.'!');
        }
    }

    //获取上传成功后的文件名
    public function __get($proName)
    {
        if (strtolower($proName)=='filename') {
            return $this->fileName;
        } elseif (strtolower($proName)=='info') {
            return $this->info;
        }
    }

    protected function _log($string)
    {
        error_log($string);
        if (class_exists('Trace')) {
            Trace::L($string, Trace::LOG_TYPE_ERROR);
        }
    }

    /**
     * 错误处理
     * @param number $errorNo 错误号
     */
    protected function _getError($errorNo)
    {
        switch ($errorNo) {
            case 1:
                return '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                return '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                return '文件只有部分被上传';
                break;
            case 4:
                return '没有文件被上传';
                break;
            case 6:
                return '找不到临时文件夹';
                break;
            case 7:
                return '文件写入失败';
                break;
            default:
                return '未知上传错误！';
        }
    }

}