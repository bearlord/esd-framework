<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */


namespace ESD\Plugins\Blade;


use ESD\Core\Plugins\Config\BaseConfig;

class BladeNamespaceConfig extends BaseConfig
{
    const KEY = "blade.namespace";

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * BladeNamespaceConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}