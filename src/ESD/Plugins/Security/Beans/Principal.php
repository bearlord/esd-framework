<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 16:03
 */

namespace ESD\Plugins\Security\Beans;
class Principal
{
    /**
     * 角色
     * @var string[]
     */
    private $roles = [];

    /**
     * 权限
     * @var string[]
     */
    private $permissions = [];

    /**
     * 用户姓名
     * @var string
     */
    private $username = "";

    public function addRole(string $role)
    {
        $this->roles[] = $role;
    }

    public function addPermissions(string $permissions)
    {
        $this->permissions[] = $permissions;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function hasRole(string $role)
    {
        return in_array($role, $this->roles);
    }

    public function hasAnyRole(array $roles)
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->roles)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $permission)
    {
        return in_array($permission, $this->permissions);
    }
}