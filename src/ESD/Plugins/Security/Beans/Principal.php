<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security\Beans;

/**
 * Class Principal
 * @package ESD\Plugins\Security\Beans
 */
class Principal
{
    /**
     * Roles
     * @var string[]
     */
    private $roles = [];

    /**
     * Permissions
     * @var string[]
     */
    private $permissions = [];

    /**
     * username
     * @var string
     */
    private $username = "";

    /**
     * Add role
     * @param string $role
     */
    public function addRole(string $role)
    {
        $this->roles[] = $role;
    }

    /**
     * Get roles
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Has role
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * Has any role
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles)
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->roles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add permissions
     * @param string $permissions
     */
    public function addPermissions(string $permissions)
    {
        $this->permissions[] = $permissions;
    }

    /**
     * Get permissions
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function hasPermission(string $permission)
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Get username
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

}