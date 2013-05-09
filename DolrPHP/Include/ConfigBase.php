<?php
return array(
            'DEBUG'               => true,
            'SHOW_TRACE'          => true,
            'DIR_CHECK'           => true,     //目录自动检测
            'ROUTING_TABLE'       => array(),   //路由表
            'DB_ENGINE'           => '',
            'DB_SET'              => array(
                                      'default' => array(
                                                    'host'    => 'localhost', //数据库主机,
                                                    'dbname'    => 'dolrphp', //数据库名称,
                                                    'user'    => 'root',    //用户名
                                                    'pass'    => '',    //密码
                                                    'prefix'  => '',    //数据表前缀
                                                    'charset' => 'utf8',//字符集
                                                    ),
                                      'writer'  => array(),
                                      'reader'  => array(),
                                    ),
            'COMMON_INCLUDE_PATH' => './',  //需要加入的include_path
            'CONTROLLER_PATH'     => APP_ABS_PATH . 'Controller/',  //控制器目录
            'MODEL_PATH'          => APP_ABS_PATH . 'Model/',       //模型目录
            'TPL_PATH'            => APP_ABS_PATH . 'View/',        //模板目录
            'ASSETS_PATH'         => APP_ABS_PATH . 'Assets/',      //静态文件目录
            'RUNTIME_PATH'        => APP_ABS_PATH . 'Runtime/',     //临时文件目录
            'EXTENSION_PATH'      => APP_ABS_PATH . 'Extension/',   //拓展类目录
            'CONTROLLER_IDENTITY' => 'Controller',  //控制器文件标识（默认Controller）
            'MODEL_IDENTITY'      => 'Model',       //模型文件标识（默认Model）
            'TPL_ENGINE_ON'       => true,  //false表示不使用模板引擎
            'TPL_STYLE'           => '',    //模板风格目录（即模板目录附加目录，默认无）
            'TPL_SUFFIX'          => 'html',    //模板文件后缀（默认html）
            'TPL_CACHE'           => true, //模板缓存 0
            'TPL_COMMON_VAR'      => array(),   //模板公用变量
            'PAGE_404'            => '404.php',        //404页面 此三项都支持绝对，相对和相对模板路径
            'PAGE_SUCCESS'        => 'Success.php',    //成功消息页面,相对于模板目录
            'PAGE_ERROR'          => 'Error.php',      //失败消息页面
            'AJAX_SIGN'           => false, //AJAX表单检测依据
            'DATA_CACHE_ON'       => false, //1|0
            'DATA_CACHE_TYPE'     => 1,     //1:Phps,2:Files,3:EAccelerator,4:Xcache,5:Apc
            'COOKIE_SECRETKEY'    => '%^&DSF*k&GH)s^df52e%&3',       //cookie加密key
            'SESSION_PATH'        => APP_ABS_PATH . 'Runtime/session/', //SESSION存储路径
            'SESSION_AUTO_START'  => true,  //SESSION自动开启
            'XSS_AUTO_FITER_ON'   => false, //XSS自动过滤
        );
