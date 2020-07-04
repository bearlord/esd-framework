<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ActorCreateEvent
 * @package ESD\Plugins\Actor
 */
class ActorCreateEvent extends Event
{
    const ActorCreateEvent = "ActorCreateEvent";
    
    const ActorCreateReadyEvent = "ActorCreateReadyEvent";

    /**
     * ActorCreateEvent constructor.
     * @param $type
     * @param $data
     */
    public function __construct($type, $data)
    {
        parent::__construct($type, $data);
    }
}