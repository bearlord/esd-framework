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
     * @var int
     */
    protected $taskProcessCount = 1;

    /**
     * @var array
     */
    protected $config;


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
        $this->config = Server::$instance->getConfigContext()->get("yii.queue");
        
        Server::$instance->addProcess(self::PROCESS_NAME, HelperQueueProcess::class, self::PROCESS_GROUP_NAME);
        //Add queue process
        for ($i = 0; $i < $this->taskProcessCount; $i++) {
            Server::$instance->addProcess("queue-$i", QueueProcess::class, QueueTask::GROUP_NAME);
        }
    }

    /**
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
        if (empty($this->config)) {
            $this->warn(Yii::t('esd', '{name} configuration not found', [
                'name' => 'Queue'
            ]));
            return false;
        }

        $pools = new QueuePools();

        foreach ($this->config as $key => $config) {
            if (empty($config['minIntervalTime']) || $config['minIntervalTime'] < 1000) {
                $config['minIntervalTime'] = 1000;
            }

            $pool = new QueuePool($key, $config);
            $pools->addPool($key, $pool);

            $queue = $pool->handle();
            //Help process
            if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::PROCESS_NAME) {
                addTimerTick($config['minIntervalTime'], function () use ($queue) {
                    $queue->listen();
                });
            }
        }

        $context->add("QueuePools", $pools);
        $this->setToDIContainer(QueuePools::class, $pools);
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