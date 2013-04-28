<?php
include_once 'Db_Adapter_Test.php';
class Db_Adapter_Pdo_Test extends Db_Adapter_Test
{
    public function __construct()
    {
        global $dbconfig;
        parent::init($dbconfig, 'pdo');
        $this->dbconfig = $dbconfig;
    }

}