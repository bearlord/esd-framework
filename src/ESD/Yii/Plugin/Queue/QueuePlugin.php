<?php
/**
 * ESD Yii Queue plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Queue;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Yii\Base\Application;
use ESD\Yii\Plugin\YiiPlugin;
use ESD\Yii\Yii;

/**
 * Class QueuePlugin
 * @package ESD\Yii\Plugin
 */
class QueuePlugin extends AbstractPlugin
{
    /**
     * PdoPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->atAfter(YiiPlugin::class);
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Queue';
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
        //Dev not fishied
        $queue = Yii::createObject([
            'class' => 'ESD\Yii\Queue\Drivers\Redis\Queue'
        ]);

        $key = "default";
        $contextKey = "Queue:{$key}";

        $context->add($contextKey, $queue);
        $this->ready();
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
    }
}