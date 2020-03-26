<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Event;

use ESD\Core\Order\Order;

/**
 * Class AbstractEventDispatcher
 * @package ESD\Core\Plugins\Event
 */
abstract class AbstractEventDispatcher extends Order
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcherManager;

    /**
     * AbstractEventDispatcher constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->eventDispatcherManager = DIGet(EventDispatcher::class);
    }

    /**
     * Handle event
     *
     * @param Event $event
     * @return mixed
     */
    abstract public function handleEventFrom(Event $event);

    /**
     * Dispatch event
     *
     * @param Event $event
     * @return mixed
     */
    abstract public function dispatchEvent(Event $event): bool;
}