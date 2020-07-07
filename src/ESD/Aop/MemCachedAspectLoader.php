<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Aop;


use Go\Aop\Aspect;
use Go\Core\AspectContainer;
use Go\Core\AspectLoader;
use Go\Core\AspectLoaderExtension;
use ReflectionClass;

/**
 * Class MemCachedAspectLoader
 * @package ESD\Aop
 */
class MemCachedAspectLoader extends AspectLoader
{
    /**
     * Identifier of original loader
     *
     * @var string
     */
    protected $loaderId;

    /**
     * Cached loader constructor
     *
     * @param AspectContainer $container Instance of container
     * @param string $loaderId Original loader identifier
     * @param array $options List of kernel options
     */
    public function __construct(AspectContainer $container, $loaderId, array $options = [])
    {
        $this->loaderId = $loaderId;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(Aspect $aspect)
    {
        $refAspect = new ReflectionClass($aspect);
        $loadedItems = $this->loader->load($aspect);
        return $loadedItems;
    }

    /**
     * {@inheritdoc}
     */
    public function registerLoaderExtension(AspectLoaderExtension $loader)
    {
        $this->loader->registerLoaderExtension($loader);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($name === 'loader') {
            $this->loader = $this->container->get($this->loaderId);

            return $this->loader;
        }
        throw new \RuntimeException('Not implemented');
    }
}