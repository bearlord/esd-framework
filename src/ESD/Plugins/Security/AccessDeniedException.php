<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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