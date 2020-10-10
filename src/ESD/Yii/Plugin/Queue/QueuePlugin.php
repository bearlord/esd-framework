<?php
/**
 * ESD Yii Queue plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Queue;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Server\Co\Server;
use ESD\Yii\Plugin\YiiPlugin;
use ESD\Yii\Plugin\Queue\Beans\QueueTask;
use ESD\Yii\Plugin\Queue\HelperQueueProcess;
use ESD\Yii\Plugin\Queue\QueueProcess;
use ESD\Yii\Queue\Drivers\Redis\Queue;
use ESD\Yii\Yii;

/**
 * Class QueuePlugin
 * @package ESD\Yii\Plugin
 */
class QueuePlugin extends AbstractPlugin
{
    const PROCESS_NAME = "helper";

    const PROCESS_GROUP_NAME = "HelperGroup";

    /**
     * PdoPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->atAfter(YiiPlugin::class);
        $this->atAfter(RedisPlugin::class);
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
        Server::$instance->addProcess(self::PROCESS_NAME, HelperQueueProcess::class, self::PROCESS_GROUP_NAME);
        //Add queue process
        $taskProcessCount = 1;
        for ($i = 0; $i < $taskProcessCount; $i++) {
            Server::$instance->addProcess("queue-$i", QueueProcess::class, QueueTask::GROUP_NAME);
        }
    }

    /**
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
        //Dev not finishied
        /** @var Queue $queue */
        $queue = Yii::createObject([
            'class' => 'ESD\Yii\Queue\Drivers\Redis\Queue'
        ]);

        $key = "default";
        $contextKey = "Queue:{$key}";

        $context->add($contextKey, $queue);

        addTimerTick(5000, function () use ($queue) {
            $queue->run(true, 500);
        });

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