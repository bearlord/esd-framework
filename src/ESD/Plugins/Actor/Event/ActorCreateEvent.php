<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor\Event;

use ESD\Core\Plugins\Event\Event;

class ActorCreateEvent extends Event
{
    const ActorCreateEvent = "ActorCreateEvent";
    
    const ActorCreateReadyEvent = "ActorCreateReadyEvent";

    /**
     * @param string $type
     * @param $data
     */
    public function __construct(string $type, $data)
    {
        parent::__construct($type, $data);
    }
}