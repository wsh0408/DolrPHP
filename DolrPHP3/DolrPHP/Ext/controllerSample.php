<?php
//这是一个demo 控制器
class Index{$_controllerIdentity} extends Controller
{
    //此方法会在执行前调用
    public function init(){
        //code...
    }

    public function index() {
        echo '欢迎使用DolrPHP';
    }
}