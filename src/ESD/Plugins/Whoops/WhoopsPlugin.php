<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/28
 * Time: 18:43
 */

namespace ESD\Plugins\Whoops;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Whoops\Aspect\WhoopsAspect;
use ESD\Plugins\Whoops\Handler\WhoopsHandler;
use Whoops\Run;

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
        //需要aop的支持，所以放在aop后加载
        $this->atAfter(AopPlugin::class);
        //由于Aspect排序问题需要在EasyRoutePlugin之前加载
        $this->atBefore("ESD\Plugins\EasyRoute\EasyRoutePlugin");
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Whoops";
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
        $aopPlugin = $pluginInterfaceManager->getPlug(AopPlugin::class);
        if ($aopPlugin == null) {
            $pluginInterfaceManager->addPlug(new AopPlugin());
        }
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
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
        $aopConfig->addIncludePath($serverConfig->getVendorDir() . "/esd/base-server");
        $aopConfig->addAspect(new WhoopsAspect($this->whoops, $this->whoopsConfig));
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        $this->whoopsConfig->merge();
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}