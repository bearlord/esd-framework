<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\MQTT\Auth\MqttAuth;
use ESD\Plugins\Pack\PackPlugin;
use ESD\Plugins\Topic\TopicPlugin;
use ESD\Plugins\Uid\UidPlugin;

class MqttPlugin extends AbstractPlugin
{
    /**
     * @var MqttPluginConfig
     */
    private $mqttPluginConfig;

    /**
     * MqttPlugin constructor.
     * @param MqttPluginConfig|null $mqttPluginConfig
     */
    public function __construct(?MqttPluginConfig $mqttPluginConfig = null)
    {
        parent::__construct();
        $this->atBefore(PackPlugin::class);
        if ($mqttPluginConfig == null) {
            $mqttPluginConfig = new MqttPluginConfig();
        }
        $this->mqttPluginConfig = $mqttPluginConfig;
    }

    /**
     * @inheritDoc
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new UidPlugin());
        $pluginInterfaceManager->addPlugin(new TopicPlugin());
        $pluginInterfaceManager->addPlugin(new PackPlugin());
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return void
     * @throws \ReflectionException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function init(Context $context)
    {
        parent::init($context);
        $this->mqttPluginConfig->merge();
        $authRc = new \ReflectionClass($this->mqttPluginConfig->getMqttAuthClass());
        $authAmpl = $authRc->newInstance();
        $this->setToDIContainer(MqttAuth::class, $authAmpl);
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "MQTT";
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
