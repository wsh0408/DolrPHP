<?php
include_once 'Db_Adapter_Test.php';
class Db_Adapter_Mysqli_Test extends Db_Adapter_Test
{
    public function __construct()
    {
        global $dbconfig;
        parent::init($dbconfig, 'mysqli');
        $this->dbconfig = $dbconfig;
    }

}