<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/17
 * Time: 14:52
 */

namespace ESD\Plugins\Blade;


use ESD\Core\Plugins\Config\BaseConfig;

class BladeConfig extends BaseConfig
{
    const key = "blade";
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var BladeNamespaceConfig[]
     */
    protected $namespace = [];

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return string
     */
    public function getCachePath(): ?string
    {
        return $this->cachePath;
    }

    /**
     * @param string $cachePath
     */
    public function setCachePath(?string $cachePath): void
    {
        $this->cachePath = $cachePath;
    }

    /**
     * @return BladeNamespaceConfig[]
     */
    public function getNamespace(): array
    {
        return $this->namespace;
    }

    /**
     * @param BladeNamespaceConfig[] $namespace
     * @throws \ReflectionException
     */
    public function setNamespace(array $namespace): void
    {
        foreach ($namespace as $key=>$value){
            if($value instanceof BladeNamespaceConfig){
                $this->addNamespace($value);
            }else{
                $bladeNamespace = new BladeNamespaceConfig();
                $bladeNamespace->buildFromConfig($value);
                $bladeNamespace->setName($key);
                $this->addNamespace($bladeNamespace);
            }
        }
    }

    public function addNamespace(BladeNamespaceConfig $bladeNamespaceConfig)
    {
        $this->namespace[$bladeNamespaceConfig->getName()] = $bladeNamespaceConfig;
    }
}
