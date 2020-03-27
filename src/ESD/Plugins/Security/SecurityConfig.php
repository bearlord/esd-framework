<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 15:44
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