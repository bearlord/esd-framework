<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Event;

use ESD\Core\Message\Message;

/**
 * Class Event
 * @package ESD\BaseServer\Plugins\Event
 */
class EventMessage extends Message
{
    const type = "@event";

    /**
     * EventMessage constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct(self::type, $event);
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->getData();
    }
}