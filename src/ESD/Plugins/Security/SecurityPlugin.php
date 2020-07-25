<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Security\Aspect\SecurityAspect;
use ESD\Plugins\Session\SessionPlugin;

class SecurityPlugin extends AbstractPlugin
{
    /**
     * @var SecurityConfig|null
     */
    private $securityConfig;

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Security";
    }

    /**
     * CachePlugin constructor.
     * @param SecurityConfig|null $securityConfig
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     * @throws \DI\NotFoundException
     */
    public function __construct(?SecurityConfig $securityConfig = null)
    {
        parent::__construct();
        $this->atAfter(AopPlugin::class);
        $this->atAfter(SessionPlugin::class);
        if ($securityConfig == null) {
            $securityConfig = new SecurityConfig();
        }
        $this->securityConfig = $securityConfig;
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new AopPlugin());
        $pluginInterfaceManager->addPlugin(new SessionPlugin());
    }


    /**
     * @param Context $context
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function init(Context $context)
    {
        parent::init($context);
        $this->securityConfig->merge();
        $aopConfig = DIget(AopConfig::class);
        $aopConfig->addAspect(new SecurityAspect());
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        $this->securityConfig->merge();
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}