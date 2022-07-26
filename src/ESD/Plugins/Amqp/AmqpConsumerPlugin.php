<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Context\Context;
use ESD\Core\DI\DI;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Coroutine\Coroutine;
use ESD\Plugins\Amqp\Message\ConsumerMessage;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\HelperScheduledProcess;
use ESD\Plugins\Scheduled\ScheduledProcess;
use ESD\Server\Coroutine\Server;

class AmqpConsumerPlugin extends AbstractPlugin
{
    const PROCESS_NAME = "amqp-consumer";
    const PROCESS_GROUP_NAME = "HelperGroup";

    use GetLogger;

    use GetAmqp;


    public function __construct()
    {
        parent::__construct();

        $this->atAfter(AnnotationsScanPlugin::class);
        $this->atAfter(AmqpPlugin::class);
    }

    /**
     * @inheritDoc
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new AnnotationsScanPlugin());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "AmqpConsumer";
    }

    /**
     * Before server start
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        Server::$instance->addProcess(self::PROCESS_NAME, AmqpProcess::class, self::PROCESS_GROUP_NAME);
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPEnvelopeException
     * @throws \AMQPExchangeException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function beforeProcessStart(Context $context)
    {
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::PROCESS_NAME) {
            //Scan annotation
            $scanClass = Server::$instance->getContainer()->get(ScanClass::class);
            $reflectionClasses = $scanClass->findClassesByAnn(\ESD\Plugins\Amqp\Annotation\Consumer::class);

            foreach ($reflectionClasses as $reflectionClass) {
                /** @var ConsumerMessage $instance */
                $instance = new $reflectionClass->name;
                $annotation = $scanClass->getClassAndInterfaceAnnotation($reflectionClass, \ESD\Plugins\Amqp\Annotation\Consumer::class);
                if (!empty($annotation->exchange)) {
                    $instance->setExchange($annotation->exchange);
                }
                if (!empty($annotation->routingKey)) {
                    $instance->setRoutingKey($annotation->routingKey);
                }
                if (!empty($annotation->queue)) {
                    $instance->setQueue($annotation->queue);
                }
                if (!is_null($annotation->enable)) {
                    $instance->setEnable($annotation->enable);
                }
                if (!empty($annotation->maxConsumption)) {
                    $instance->setMaxConsumption($annotation->maxConsumption);
                }

                goWithContext(function () use ($instance){
                    (new Consumer())->consume($instance);
                    Coroutine::sleep(0.1);
                });
            }

        }
        $this->ready();
    }
}