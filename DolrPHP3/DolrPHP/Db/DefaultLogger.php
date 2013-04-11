<?php
class Db_DefAultLogger implements Db_Logger
{
    public function log($string, $type = 1) {
        if (!$type)
            error_log($string);
    }
}