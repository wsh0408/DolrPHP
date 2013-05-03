<?php
return array(
        'MODEL_PATH'     => __DIR__ . '/Model/',       //模型目录
        'EXTENSION_PATH' => __DIR__ . '/Extension/',   //拓展类目录
        'DB_ENGINE'      => 'PDO',
        'DB_SET'         => array(
                          'default' => array(
                                        'host'    => 'localhost', //数据库主机,
                                        'name'    => 'dolrphp', //数据库名称,
                                        'user'    => 'root',    //用户名
                                        'pass'    => '',    //密码
                                        'prefix'  => '',    //数据表前缀
                                        'charset' => 'utf8',//字符集
                                        ),
                          'writer'  => array(),
                          'reader'  => array(),
                        ),
     );