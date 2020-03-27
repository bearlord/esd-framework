<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/7
 * Time: 16:27
 */

namespace ESD\Plugins\Session;


trait GetSession
{
    public function getSession(): HttpSession
    {
        $session = getDeepContextValueByClassName(HttpSession::class);
        if ($session == null) {
            $session = new HttpSession();
        }
        return $session;
    }
}