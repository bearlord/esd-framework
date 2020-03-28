<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Whoops;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class WhoopsConfig
 * @package ESD\Plugins\Whoops
 */
class WhoopsConfig extends BaseConfig
{
    const key = "whoops";

    /**
     * @var bool
     */
    protected $enable = true;

    /**
     * WhoopsConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }
}