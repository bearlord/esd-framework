<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache;

use DI\DependencyException;
use DI\NotFoundException;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Context\Context;
use ESD\Core\Exception;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Cache\Aspect\CachingAspect;
use ESD\Plugins\Redis\RedisPlugin;
use ReflectionException;

class CachePlugin extends AbstractPlugin
{

    /**
     * @var CacheConfig
     */
    private $cacheConfig;

    /**
     * @var CacheStorage
     */
    protected $cacheStorage;


    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Cache";
    }

    /**
     * CachePlugin constructor.
     * @param CacheConfig|null $cacheConfig
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function __construct(?CacheConfig $cacheConfig = null)
    {
        parent::__construct();
        $this->atAfter(RedisPlugin::class);
        $this->atAfter(AopPlugin::class);
        if ($cacheConfig == null) {
            $cacheConfig = new CacheConfig();
        }
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws DependencyException
     * @throws Exception
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new RedisPlugin());
        $pluginInterfaceManager->addPlugin(new AopPlugin());
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ConfigException
     * @throws \Exception
     */
    public function init(Context $context)
    {
        parent::init($context);
        $this->cacheConfig->merge();
        $class = $this->cacheConfig->getCacheStorageClass();
        $this->cacheStorage = new $class($this->cacheConfig);
        $this->setToDIContainer(CacheStorage::class, $this->cacheStorage);
        $aopConfig = DIget(AopConfig::class);
        $aopConfig->addAspect(new CachingAspect($this->cacheStorage));
    }

    /**
     * @inheritDoc
     * @param Context $context
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * @inheritDoc
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return CacheStorage
     */
    public function getCacheStorage(): CacheStorage
    {
        return $this->cacheStorage;
    }
}