<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\PlugIn;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Core\Order\Order;
use ESD\Core\Server\Server;

/**
 * Class BasePlug
 * @package ESD\Core\Server\Plug
 */
abstract class AbstractPlugin extends Order implements PluginInterface
{
    /**
     * @var PluginInterfaceManager
     */
    protected $pluginInterfaceManager;

    /**
     * @var Channel
     */
    private $readyChannel;

    /**
     * AbstractPlugin constructor.
     */
    public function __construct()
    {
        $this->readyChannel = DIGet(Channel::class);
        //注入DI
        Server::$instance->getContainer()->injectOn($this);
    }

    /**
     * Set to DI container
     *
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function setToDIContainer($name, $value)
    {
        DISet($name, $value);
    }

    /**
     * @inheritDoc
     * @return Channel
     */
    public function getReadyChannel(): Channel
    {
        return $this->readyChannel;
    }

    /**
     * Ready channel push message
     */
    public function ready()
    {
        $this->readyChannel->push("ready");
    }

    /**
     * @inheritDoc
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        $this->pluginInterfaceManager = $pluginInterfaceManager;
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     */
    public function init(Context $context)
    {
        return;
    }
}