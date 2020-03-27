<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 16:31
 */

use ESD\Core\Server\Beans\Request;
use ESD\Plugins\Security\Beans\Principal;
use ESD\Plugins\Session\HttpSession;

/**
 * 当前账户有指定角色时返回true
 * @param string $role
 * @return bool
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function hasRole(string $role)
{
    $session = getDeepContextValueByClassName(HttpSession::class);
    if ($session == null) {
        $session = new HttpSession();
    }
    $principal = $session->getAttribute("Principal");
    if ($principal instanceof Principal) {
        return $principal->hasRole($role);
    } else {
        return false;
    }
}

/**
 * 当前账户有指定角色中的任意一个时返回true
 * @param array $roles
 * @return bool
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function hasAnyRole(array $roles)
{
    $session = getDeepContextValueByClassName(HttpSession::class);
    if ($session == null) {
        $session = new HttpSession();
    }
    $principal = $session->getAttribute("Principal");
    if ($principal instanceof Principal) {
        return $principal->hasAnyRole($roles);
    } else {
        return false;
    }
}

/**
 * 允许所有
 * @return bool
 */
function permitAll()
{
    return true;
}

/**
 * 拒绝所有
 * @return bool
 */
function denyAll()
{
    return false;
}

/**
 * 是否已经登录
 * @return bool
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function isAuthenticated()
{
    $session = getDeepContextValueByClassName(HttpSession::class);
    if ($session == null) {
        $session = new HttpSession();
    }
    $principal = $session->getAttribute("Principal");
    if ($principal == null) {
        return false;
    } else {
        return true;
    }
}

/**
 * 是否拥有权限
 * @param string $permission
 * @return bool
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function hasPermission(string $permission)
{
    $session = getDeepContextValueByClassName(HttpSession::class);
    if ($session == null) {
        $session = new HttpSession();
    }
    $principal = $session->getAttribute("Principal");
    if ($principal instanceof Principal) {
        return $principal->hasPermission($permission);
    } else {
        return false;
    }
}

/**
 * IP地址是否符合支持10.0.0.0/16这种
 * @param array $ips
 * @return bool
 */
function hasIpAddress($ips)
{
    $request = getDeepContextValueByClassName(Request::class);
    if ($request instanceof Request) {
        $ip = $request->getServer(Request::SERVER_REMOTE_ADDR);
        if (is_array($ips)) {
            foreach ($ips as $oneip) {
                if ($oneip == $ip) return true;
                $exip = explode("/", $oneip);
                $mask = $exip[1] ?? null;
                if ($mask != null) {
                    if (netMatch($ip, $exip[0], $mask)) return true;
                }
            }
            return false;
        } elseif (is_string($ips)) {
            if ($ips == $ip) return true;
            $exip = explode("/", $ips);
            $mask = $exip[1] ?? null;
            if ($mask != null) {
                return netMatch($ip, $exip[0], $mask);
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }

}

function netMatch($client_ip, $server_ip, $mask)
{
    $mask1 = 32 - $mask;
    return ((ip2long($client_ip) >> $mask1) == (ip2long($server_ip) >> $mask1));
}