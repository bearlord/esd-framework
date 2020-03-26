<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Config;


class ConfigConfig
{
    /**
     * @var string
     */
    protected $configDir;

    /**
     * ConfigConfig constructor.
     * @param string $configDir
     */
    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
        if (!is_dir($configDir)) {
            echo "RES_DIR不合法，将不加载配置文件\n";
        }
    }

    /**
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * @param string $configDir
     */
    public function setConfigDir(string $configDir): void
    {
        $this->configDir = $configDir;
    }
}