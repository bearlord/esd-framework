<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack;

use ESD\Core\Exception;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Pack\Aspect\PackAspect;

/**
 * Class PackPlugin
 * @package ESD\Plugins\Pack
 */
class PackPlugin extends AbstractPlugin
{
    /**
     * @var PackConfig[]
     */
    private $packConfigs = [];

    /**
     * @var PackAspect
     */
    private $packAspect;

    /**
     * EasyRoutePlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        //Need aop support, so load after aop
        $this->atAfter(AopPlugin::class);
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Pack";
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function init(Context $context)
    {
        parent::init($context);
        $configs = Server::$instance->getConfigContext()->get(PortConfig::key);
        foreach ($configs as $key => $value) {
            $packConfig = new PackConfig();
            $packConfig->setName($key);
            $packConfig->buildFromConfig($value);
            //Handling packtool
            if($packConfig->getPackTool()!=null){
                $class = $packConfig->getPackTool();
                if(class_exists($class)){
                    $class::changePortConfig($packConfig);
                }else{
                    throw new Exception("$class pack class was not found");
                    exit(-1);
                }
            }
            $packConfig->merge();
            $this->packConfigs[$packConfig->getPort()] = $packConfig;
        }
        $serverConfig = Server::$instance->getServerConfig();
        /** @var AopConfig $aopConfig */
        $aopConfig = DIget(AopConfig::class);
        $aopConfig->addIncludePath($serverConfig->getVendorDir() . "/esd/base-server");
        $this->packAspect = new PackAspect($this->packConfigs);
        $aopConfig->addAspect($this->packAspect);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AopPlugin());
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context)
    {
        return;
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
     * @return PackAspect
     */
    public function getPackAspect(): PackAspect
    {
        return $this->packAspect;
    }
}