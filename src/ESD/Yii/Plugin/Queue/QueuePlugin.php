<?php
/**
 * ESD Yii Queue plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Queue;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Amqp\AmqpPlugin;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Helpers\Json;
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
    use GetLogger;

    const PROCESS_NAME = "helper";

    const PROCESS_GROUP_NAME = "HelperGroup";

    const PROCESS_QUEUE_PREFIX  = 'queue-';

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
        $this->atAfter(AmqpPlugin::class);
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
        if (empty($this->config)) {
            $this->warn(Yii::t('esd', '{name} configuration not found', [
                'name' => 'Queue'
            ]));
            return false;
        }
        
        Server::$instance->addProcess(self::PROCESS_NAME, HelperQueueProcess::class, self::PROCESS_GROUP_NAME);

        //Add custom queue process
        $index = 0;
        foreach ($this->config as $key => $config) {
            Server::$instance->addProcess(self::PROCESS_QUEUE_PREFIX . $index, QueueProcess::class, QueueTask::GROUP_NAME);
            $index++;
        }
    }

    /**
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
        $pools = new QueuePools();

        if (empty($this->config)) {
            return false;
        }

        $index = 0;
        foreach ($this->config as $key => $config) {
            if (empty($config['minIntervalTime']) || $config['minIntervalTime'] < 1000) {
                $config['minIntervalTime'] = 1000;
            }

            $pool = new QueuePool($key, $config);
            $pools->addPool($key, $pool);

            /** @var Queue $queue */
            $queue = $pool->handle();

            //Custom process
            if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::PROCESS_QUEUE_PREFIX . $index) {
                addTimerTick($config['minIntervalTime'], function () use ($queue) {
                    $queue->listen();
                });
            }
            $index++;
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