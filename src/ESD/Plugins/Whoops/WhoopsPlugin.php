<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Whoops;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Whoops\Aspect\WhoopsAspect;
use ESD\Plugins\Whoops\Handler\WhoopsHandler;
use Whoops\Run;

/**
 * Class WhoopsPlugin
 * @package ESD\Plugins\Whoops
 */
class WhoopsPlugin extends AbstractPlugin
{
    use GetLogger;

    /**
     * @var Run
     */
    private $whoops;

    /**
     * @var WhoopsConfig
     */
    protected $whoopsConfig;

    /**
     * WhoopsPlugin constructor.
     * @param WhoopsConfig|null $whoopsConfig
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     * @throws \DI\NotFoundException
     */
    public function __construct(?WhoopsConfig $whoopsConfig = null)
    {
        parent::__construct();
        if ($whoopsConfig == null) {
            $whoopsConfig = new WhoopsConfig();
        }
        $this->whoopsConfig = $whoopsConfig;

        //Need aop support, so load after aop
        $this->atAfter(AopPlugin::class);

        //Due to Aspect sorting issues need to be loaded before EasyRoutePlugin
        $this->atBefore("ESD\Plugins\EasyRoute\EasyRoutePlugin");
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Whoops";
    }

    /**
     * @inheritDoc
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
        $aopPlugin = $pluginInterfaceManager->getPlug(AopPlugin::class);
        if ($aopPlugin == null) {
            $pluginInterfaceManager->addPlugin(new AopPlugin());
        }
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function init(Context $context)
    {
        parent::init($context);
        $aopConfig = DIGet(AopConfig::class);
        $this->whoopsConfig->merge();
        $serverConfig = Server::$instance->getServerConfig();
        $this->whoops = new Run();
        $this->whoops->writeToOutput(false);
        $this->whoops->allowQuit(false);
        $handler = new WhoopsHandler();
        $handler->addResourcePath($serverConfig->getVendorDir() . "/filp/whoops/src/Whoops/Resources/");
        $handler->setPageTitle("出现错误了");
        $this->whoops->pushHandler($handler);
        $aopConfig->addIncludePath($serverConfig->getVendorDir() . "/esd-framework/src/ESD/");
        $aopConfig->addAspect(new WhoopsAspect($this->whoops, $this->whoopsConfig));
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        $this->whoopsConfig->merge();
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