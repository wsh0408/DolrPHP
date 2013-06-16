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
 * DolrPHP RBAC
 *
 * 本文件依赖DolrPHP框架的Model
 */
class Rbac
{
    private $_user;
    private $_role;
    private $_perm;
    private $_rolePermRel;


    public function __construct($config)
    {
        $tableConfig['user'] = array(
                                'table'       => 'user',
                                'userIdField' => 'id',
                                'roleIdField' => 'role_id',
                               );
        $tableConfig['role'] = array(
                                'table'          => 'role',
                                'roleIdField'    => 'id',
                                'roleNameField'  => 'role_name',
                                'roleLevelField' => 'role_level',
                               );
        $tableConfig['perm'] = array(
                                'table'         => 'perm',
                                'permIdField'   => 'id',
                                'permNameField' => 'perm_name',
                                'permNameDesc'  => 'perm_desc',
                               );
        $tableConfig['role_perm'] = array(
                                     'table'       => 'role_perm',
                                     'roleIdField' => 'role_id',
                                     'permIdField' => 'perm_id',
                                    );
        foreach (array('user', 'role', 'perm', 'role_perm') as $table) {
            if (isset($config[$table]) {
                $tableConfig[$table] = array_merge($tableConfig[$table], $config[$table]);
            }
        }

        $this->_user = new Rbac_User($tableConfig['user']);
        $this->_role = new Rbac_Role($tableConfig['role']);
        $this->_perm = new Rbac_Permissions($tableConfig['perm']);
        $this->_rolePermRel = new Rbac_RolePermRel($tableConfig['role_perm']);
    }

    /**
     * 用户是否有权限
     *
     * @param integer $userId   用户ID
     * @param string  $permName 权限名称
     *
     * @return boolean
     */
    public function hasPerm($userId, $permName)
    {
        if ($this->_hasSession($userId)) {
            return $this->_permInSession($permName, $userId);
        }
        $userRoleId = $this->_user->getRole($userId);
        $permId = $this->_perm->getIdByName($permName);
        $res = $this->_rolePermRel->hasPerm($userRoleId, $permId);
        return $res;
    }

    /**
     * 为用户分配权限
     *
     * @param integer $userId 用户ID
     * @param integer $roleId 角色ID
     *
     * @return boolean
     */
    public function assignUserRole($userId, $roleId)
    {
        return $this->_user->setUserRole($userId, $roleId);
    }

    /**
     * session中是否存在某个权限
     *
     * @param string  $permName 权限名称
     * @param integer $userId   用户ID
     *
     * @return boolean
     */
    private function _permInSession($permName, $userId)
    {
        return in_array($permName, $_SESSION['__RBAC_USER_' . $userId]);
    }

    /**
     * 是否存在用户Session
     *
     * @param integer $userId 用户ID
     *
     * @return boolean
     */
    private function _hasSession($userId)
    {
        return isset($_SESSION['__RBAC_USER_' . $userId]);
    }

    /**
     * 将用户权限写入session
     *
     * @param integer $userId 用户ID
     * @param array   $perms  权限列表
     *
     * @return void
     */
    private function _setSession($userId, $perms)
    {
        $_SESSION['__RBAC_USER_' . $userId] = $perms;
    }

}

class Rbac_User extends Model
{
    private $_table;
    private $_dao;

    public function __construct($table)
    {
        $this->_table = $table;
        $this->_dao = $this->dispense($table['table']);
    }

    /**
     * 分配用户角色
     *
     * @param integer $userId 用户ID
     * @param integer $roleId 角色ID
     *
     * @return boolean
     */
    public function setUserRole($userId, $roleId)
    {
        $condition = array($this->_table['userIdField'] => $userId);
        $data = array($this->_table['roleIdField'] => $roleId);
        if (!$this->_dao->has($condition)) {
            return false;
        }
        $res = $this->_dao->where($condition)->save($data);
        return false !== $res;
    }

    /**
     * 获取用户角色ID
     *
     * @param integer $userId 用户ID
     *
     * @return integer
     */
    public function getUserRole($userId)
    {
        $condition = array($this->_table['userIdField'] => $userId);
        if (!$this->_dao->has($condition)) {
            return false;
        }
        $userRoleId = $this->_dao->where($condition)->getCell($this->_table['roleIdField']);
        return $userRoleId;
    }
}

class Rbac_Role extends Model
{
    private $_table;
    private $_dao;

    public function __construct($table)
    {
        $this->_table = $table;
        $this->_dao = $this->dispense($table['table']);
    }

    /**
     * 添加角色
     *
     * @param string  $roleName  角色名称
     * @param integer $roleLevel 角色级别
     *
     * @return boolean|integer
     */
    public function addRole($roleName, $roleLevel)
    {
        $condition = array($this->_table['roleNameField'] => $roleName);
        $data = array(
                 $this->_table['roleNameField'] => $roleName,
                 $this->_table['roleLevelField'] => $roleLevel,
                );
        if ($this->_dao->has($condition)) {
            return true;
        }
        $res = $this->_dao->add($data);
        return $res;
    }

    /**
     * 删除角色
     *
     * @param integer $roleId 角色ID
     *
     * @return boolean
     */
    public function delRole($roleId)
    {
        $condition = array($this->_table['roleIdField'] => $roleId);
        if (!$this->_dao->has($condition)) {
            return true;
        }
        $res = $this->_dao->where($condition)->del();
        return false !== $res;
    }

}

class Rbac_Permissions extends Model
{
    private $_table;
    private $_dao;

    public function __construct($table)
    {
        $this->_table = $table;
        $this->_dao = $this->dispense($table['table']);
    }

    /**
     * 添加权限节点
     *
     * @param string $permName 操作名称
     * @param string $permDesc 操作说明
     *
     * @return boolean|integer
     */
    public function addPerm($permName, $permDesc)
    {
        $condition = array($this->_table['permNameField'] => $permName);
        $data = array(
                 $this->_table['permNameField'] => $roleName,
                 $this->_table['permDescField'] => $roleLevel,
                );
        if ($this->_dao->has($condition)) {
            return true;
        }
        $res = $this->_dao->add($data);
        return $res;
    }

    /**
     * 删除权限
     *
     * @param integer $roleId 权限ID
     *
     * @return boolean
     */
    public function delPerm($permId)
    {
        $condition = array($this->_table['permIdField'] => $permId);
        if (!$this->_dao->has($condition)) {
            return true;
        }
        $res = $this->_dao->where($condition)->del();
        return false !== $res;
    }

    /**
     * 判断是否存在权限
     *
     * @param string $permName 权限名称
     *
     * @return boolean
     */
    public function hasPerm($permName)
    {
        $condition = array($this->_table['permNameField'] => $permName);
        $res = $this->_dao->has($condition);
        return $res;
    }

    /**
     * 获取多个权限
     *
     * @param array $permIds 权限ID数组
     *
     * @return array
     */
    public function getPermsByIds($permIds)
    {
        $condition = array($this->_table['permIdField'] . '[in]' => join(',', $permIds));
        $perms = $this->_dao->where($condition)->getCell($this->_table['permNameField']);
        return $perms;
    }

    /**
     * 获取权限ID
     *
     * @param string $permName 权限名称
     *
     * @return integer
     */
    public function getIdByName($permName)
    {
        $condition = array($this->_table['permNameField'] => $permName);
        if (!$this->_dao->has($condition)) {
            return false;
        }
        $permId = $this->_dao->where($condition)->getCell($this->_table['permIdField']);
        return $permId;
    }
}


class Rbac_RolePermRel extends Model
{
    private $_table;
    private $_dao;

    public function __construct($table)
    {
        $this->_table = $table;
        $this->_dao = $this->dispense($table['table']);
    }

    /**
     * 添加角色权限对应关系
     *
     * @param integer $roleId 角色ID
     * @param integer $permId 权限ID
     *
     * @return boolean|integer
     */
    public function addRolePerm($roleId, $permId)
    {
        $data = array(
                 $this->_table['roleIdField'] => $roleId,
                 $this->_table['permIdField'] => $permId,
                );
        if ($this->_dao->has($data)) {
            return true;
        }
        $res = $this->_dao->add($data);
        return $res;
    }

    /**
     * 删除角色权限
     *
     * @param integer $roleId 角色ID
     * @param integer $permId 权限ID
     *
     * @return boolean
     */
    public function deleteRolePerm($roleId, $permId)
    {
        $data = array(
                 $this->_table['roleIdField'] => $roleId,
                 $this->_table['permIdField'] => $permId,
                );
        if (!$this->_dao->has($data)) {
            return true;
        }
        $res = $this->_dao->where($data)->del();
        return false !== $res;
    }

    /**
     * 获取角色权限ID
     *
     * @param integer $roleId 角色ID
     *
     * @return array
     */
    public function getRolePermIds($roleId)
    {
        $condition = array($this->_table['roleIdField'] => $roleId);
        $permIds = $this->_dao->getCol($this->_table['permIdField'], true, $condition);
        return $permIds;
    }

    /**
     * 角色是否含有权限
     *
     * @param integer  $roleId  角色ID
     * @param integer $permId 权限ID
     *
     * @return boolean
     */
    public function hasPerm($roleId, $permId)
    {
        $data = array(
                 $this->_table['roleIdField'] => $roleId,
                 $this->_table['permIdField'] => $permId,
                );
        $res = $this->_dao->has($data);
        return $res;
    }
}
