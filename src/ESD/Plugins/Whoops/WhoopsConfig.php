<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/6
 * Time: 16:32
 */

namespace ESD\Plugins\Whoops;


use ESD\Core\Plugins\Config\BaseConfig;

class WhoopsConfig extends BaseConfig
{
    const key = "whoops";
    protected $enable = true;

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