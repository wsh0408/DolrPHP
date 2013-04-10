<?php

/**
 * 自动加载类
 * @param  string $className 类名
 * @return void  boolean
 */
function dolrAutoLoader($className) {
    $className = ucfirst($className);
    if (file_exists(BASE_PATH . $className . '.php')) { //BASE
        include BASE_PATH . $className . '.php';
    } elseif ($className == 'Db') { //DB
        include DB_PATH . 'Db.php';
    } elseif (FALSE !== stripos($className, 'Db_')) { //DB
        include DB_PATH . str_replace('_', '/', substr($className, 3)) . '.php';
    } elseif (file_exists(C('CONTROLLER_PATH') . $className . '.php')) { //Controller
        include C('CONTROLLER_PATH') . $className . '.php';
    } elseif (file_exists(C('MODEL_PATH') . $className . '.php')) { //Model
        include C('MODEL_PATH') . $className . '.php';
    } else {
        trigger_error('类"' . $className . '"无法加载，文件不存在或名称错误.');

        return FALSE;
    }
    Trace::L($className, 'class');
}