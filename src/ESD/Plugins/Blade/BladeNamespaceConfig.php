<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/17
 * Time: 15:02
 */

namespace ESD\Plugins\Blade;


use ESD\Core\Plugins\Config\BaseConfig;

class BladeNamespaceConfig extends BaseConfig
{
    const key = "blade.namespace";
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;

    public function __construct()
    {
        parent::__construct(self::key);
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