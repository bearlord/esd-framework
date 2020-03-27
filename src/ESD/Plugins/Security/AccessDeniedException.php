<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 16:23
 */

namespace ESD\Plugins\Security;

use ESD\Core\Exception;

class AccessDeniedException extends Exception
{
    public function __construct()
    {
        parent::__construct("没有相应权限", 0, null);
        $this->setTrace(false);
    }
}