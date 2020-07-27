<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\PHPUnit;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\Console\ConsoleConfig;
use ESD\Plugins\Console\ConsolePlugin;

/**
 * Class PHPUnitPlugin
 * @package ESD\Plugins\PHPUnit
 */
class PHPUnitPlugin extends AbstractPlugin
{
    const processName = "unit";
    const processGroupName = "UnitGroup";

    /**
     * PHPUnitPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->atAfter(ConsolePlugin::class);
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "PHPUnit";
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new ConsolePlugin());
        //添加一个cmd
        $console = new ConsoleConfig();
        $console->addCmdClass(TestCmd::class);
        $console->merge();
    }

    /**
     * @inheritDoc
     * @param Context $context
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
}