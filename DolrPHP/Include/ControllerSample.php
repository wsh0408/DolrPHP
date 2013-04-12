<?php
//这是一个demo 控制器
class Index__IDENTITY__ extends Controller
{
    //此方法会在执行前调用
    public function initialize(){
        //code...
    }

    public function index() {
        $this->display(TPL_PATH . 'welcomeTemplate.php');
    }
}