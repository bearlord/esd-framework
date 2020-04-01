<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security;

use ESD\Core\Plugins\Config\BaseConfig;

class SecurityConfig extends BaseConfig
{
    const key = "security";

    public function __construct()
    {
        parent::__construct(self::key);
    }
}