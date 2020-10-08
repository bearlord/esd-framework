<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */


namespace ESD\Plugins\AutoReload;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class AutoReloadConfig
 * @package ESD\Plugins\AutoReload
 */
class AutoReloadConfig extends BaseConfig
{
    const KEY = "reload";

    /**
     * @var bool
     */
    protected $enable = true;

    /**
     * Monitor directory
     * @var string|null
     */
    protected $monitorDir;

    /**
     * AutoReloadConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
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

    /**
     * @return string|null
     */
    public function getMonitorDir(): ?string
    {
        return $this->monitorDir;
    }

    /**
     * @param string|null $monitorDir
     */
    public function setMonitorDir(?string $monitorDir): void
    {
        $this->monitorDir = $monitorDir;
    }
}