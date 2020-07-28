<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugin;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Core\Exception;
use ESD\Core\Order\OrderOwnerTrait;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;

/**
 * Class PlugManager
 * @package ESD\Core\Server\Plug
 */
class PluginInterfaceManager implements PluginInterface
{
    use OrderOwnerTrait;
    use GetLogger;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var Channel
     */
    private $readyChannel;


    /**
     * PluginInterfaceManager constructor.
     * @param Server $server
     * @throws \Exception
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->readyChannel = DIGet(Channel::class);
        $this->eventDispatcher = DIGet(EventDispatcher::class);
    }

    /**
     * Add plug
     *
     * @param AbstractPlugin $plugin
     * @throws Exception
     */
    public function addPlugin(AbstractPlugin $plugin)
    {
        $this->addOrder($plugin);
        $plugin->onAdded($this);
    }

    /**
     * Get plug
     *
     * @param String $className
     * @return PluginInterface|null
     */
    public function getPlug(String $className)
    {
        $plugin = $this->orderClassList[$className] ?? null;
        if ($plugin instanceof PluginInterface) {
            return $plugin;
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \Exception
     */
    public function init(Context $context)
    {
        foreach ($this->orderList as $plugin) {
            if ($plugin instanceof PluginInterface) {
                $plugin->init($context);
            }
        }
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     */
    public function beforeServerStart(Context $context)
    {
        //Dispatch PlugManagerEvent: PlugBeforeServerStartEvent event
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugBeforeServerStartEvent, $this));
        }
        foreach ($this->orderList as $plugin) {
            if ($plugin instanceof PluginInterface) {
                $plugin->beforeServerStart($context);
            }
        }
        //Dispatch PlugManagerEvent:PlugAfterServerStartEvent event
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugAfterServerStartEvent, $this));
        }
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //Dispatch PlugManagerEvent:PlugBeforeProcessStartEvent event
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugBeforeProcessStartEvent, $this));
        }
        foreach ($this->orderList as $plugin) {
            if ($plugin instanceof PluginInterface) {
                try {
                    $plugin->beforeProcessStart($context);
                } catch (\Throwable $e) {
                    $this->error($e);
                    $this->error(sprintf("%s plugin failed to load", $plugin->getName()));
                    continue;
                }
                if (!$plugin->getReadyChannel()->pop(5)) {
                    $plugin->getReadyChannel()->close();
                    $this->error(sprintf("%s plugin failed to load", $plugin->getName()));
                    if ($this->eventDispatcher != null) {
                        $this->eventDispatcher->dispatchEvent(new PluginEvent(PluginEvent::PlugFailEvent, $plugin));
                    }
                } else {
                    if ($this->eventDispatcher != null) {
                        $this->eventDispatcher->dispatchEvent(new PluginEvent(PluginEvent::PluginSuccessEvent, $plugin));
                    }
                }
            }
        }
        //Dispatch PlugManagerEvent:PlugAfterProcessStartEvent event
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugAfterProcessStartEvent, $this));
        }
        $this->readyChannel->push("Ready");
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "PlugManager";
    }

    /**
     * @return Channel
     */
    public function getReadyChannel(): Channel
    {
        return $this->readyChannel;
    }

    /**
     * Wait to ready
     */
    public function waitReady()
    {
        $this->readyChannel->pop();
        $this->readyChannel->close();

        //Dispatch PlugManagerEvent:PlugAllReadyEvent event
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugAllReadyEvent, $this));
        }
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        return;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }
}