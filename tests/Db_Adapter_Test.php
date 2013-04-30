<?php
include_once 'TestConf.php';
class Db_Adapter_Test extends PHPUnit_Framework_TestCase
{
    public $db;
    public $dbconfig;
    public $data = array(
                 'user_login'          => 'testUser',
                 'user_pass'           => '123465',
                 'user_nicename'       => 'testUserNiceName',
                 'user_email'          => '44294631@qq.com',
                 'user_url'            => 'http://www.joychao.cc',
                 'user_registered'     => '1',
                 'user_activation_key' => '213456457',
                 'user_status'         => '1',
                 'display_name'        => 'haha',
                );

    public function init($config, $type)
    {
        $this->db = null;
        Db::initialize($config, $type);
        $this->db = Db::dispense('users');
    }

    public function testQuery()
    {
        $res = $this->db->query('select * from wp_users');
        $this->assertArrayHasKey('0', $res);
        $this->assertArrayHasKey('user_login', $res[0]);
    }

    public function testAdd()
    {
        $res = $this->db->add($this->data);
        $this->assertEquals('string', gettype($res));
    }

    public function testDel()
    {
        $res = $this->db->add($this->data);
        $deleteResult = $this->db->del('where ID = ?',array($res));
        $this->assertEquals('integer', gettype($deleteResult));
    }

    public function testFind()
    {
        $res = $this->db->find();
        $this->assertArrayHasKey('user_login', $res);
    }

    public function testSelect()
    {
        $res = $this->db->select();
        $this->assertArrayHasKey('user_login', $res[0]);
        if (count($res) > 1) {
            $this->assertArrayHasKey('user_login', $res[1]);
        }
    }

    public function testSave()
    {
        $data = array('user_login' => 'adminSaved');
        $res = $this->db->save($data, 'where user_login = "admin"');
        $this->assertEquals('integer', gettype($res));
    }

    public function testGetRow()
    {
        $res = $this->db->getRow('where user_login = ?', array('adminSaved'));
        $this->assertArrayHasKey('user_login', $res);
        $this->assertEquals('adminSaved', $res['user_login']);
    }

    public function testGetAll()
    {
        $res = $this->db->getAll();
        $this->assertArrayHasKey('user_login', $res[0]);
        if (count($res) > 1) {
            $this->assertArrayHasKey('user_login', $res[1]);
        }
    }

    public function testGetCol()
    {
        $res = $this->db->getCol('user_login');
        $this->assertArrayHasKey('user_login', $res[0]);
        $this->assertEquals(1, count($res[0]));
        if (count($res) > 1) {
            $this->assertArrayHasKey('user_login', $res[1]);
        }
    }

    public function testGetCell()
    {
        $res = $this->db->getCell('user_login');
        $this->assertEquals('string', gettype($res));
    }

    public function testGetAssoc()
    {
        $res = $this->db->getAssoc('where user_login = ?', array('adminSaved'));
        $this->assertArrayHasKey('ID', $res);
        $this->assertArrayHasKey('user_nicename', $res);
        $this->assertEquals('string', gettype($res['ID']));
    }

    public function testGetObject()
    {
        $res = $this->db->getObject('where user_login = ?', array('adminSaved'));
        $this->assertEquals('object', gettype($res));
        $this->assertObjectHasAttribute('ID', $res);
        $this->assertObjectHasAttribute('user_nicename', $res);
    }

    public function testGetObjects()
    {
        $res = $this->db->getObjects();
        $this->assertEquals('object', gettype($res[0]));
        $this->assertObjectHasAttribute('ID', $res[0]);
        $this->assertObjectHasAttribute('user_nicename', $res[0]);
    }

    public function testGetCount()
    {
        $res = $this->db->getCount();
        $this->assertEquals('string', gettype($res));
        $this->assertEquals(true, $res > 0);
    }

    public function testGetByUserLogin()
    {
        $res = $this->db->getByUserLogin('adminSaved');
        $this->assertArrayHasKey('ID', $res);
        $this->assertArrayHasKey('user_nicename', $res);
        $this->assertEquals('string', gettype($res['ID']));
    }

}