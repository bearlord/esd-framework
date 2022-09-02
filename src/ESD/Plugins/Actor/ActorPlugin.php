<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\Actor\ActorCacheProcess;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\ProcessRPC\ProcessRPCPlugin;

/**
 * Class ActorPlugin
 * @package ESD\Plugins\Actor
 */
class ActorPlugin extends AbstractPlugin
{

    /**
     * @var ActorConfig|null
     */
    private $actorConfig;

    /**
     * @var ActorManager
     */
    protected $actorManager;

    /**
     * ActorPlugin constructor.
     * @param ActorConfig|null $actorConfig
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        $config = Server::$instance->getConfigContext()->get('actor');
        $actorConfig = new ActorConfig();
        $actorConfig->setActorMaxCount($config['actorMaxCount']);
        $actorConfig->setActorMailboxCapacity($config['actorMaxClassCount']);
        $actorConfig->setActorWorkerCount($config['actorWorkerCount']);
        $actorConfig->setActorMaxClassCount($config['actorMailboxCapacity']);
        $this->actorConfig = $actorConfig;
        $this->atAfter(ProcessRPCPlugin::class);
    }

    /**
     * @inheritDoc
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new ProcessRPCPlugin());
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Actor";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        $this->actorConfig->merge();
        for ($i = 0; $i < $this->actorConfig->getActorWorkerCount(); $i++) {
            Server::$instance->addProcess("actor-$i", ActorProcess::class, ActorConfig::GROUP_NAME);
        }

        Server::$instance->addProcess(ActorCacheProcess::PROCESS_NAME, ActorCacheProcess::class, ActorCacheProcess::GROUP_NAME);

        $this->actorManager = ActorManager::getInstance();

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