<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class SecurityConfig
 * @package ESD\Plugins\Security
 */
class SecurityConfig extends BaseConfig
{
    const KEY = "security";

    /**
     * SecurityConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }
}