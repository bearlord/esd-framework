<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Event;

use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;

/**
 * Class EventMessageProcessor
 * @package ESD\BaseServer\Plugins\Event
 */
class EventMessageProcessor extends MessageProcessor
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * EventMessageProcessor constructor.
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        parent::__construct(EventMessage::type);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     * @param Message $message
     * @return mixed
     */
    public function handler(Message $message): bool
    {
        if ($message instanceof EventMessage) {
            $this->eventDispatcher->dispatchEvent($message->getEvent());
            return true;
        }
        return false;
    }
}