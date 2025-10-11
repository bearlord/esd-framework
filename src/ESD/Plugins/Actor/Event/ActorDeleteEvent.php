<?php

namespace ESD\Plugins\Actor\Event;

use ESD\Core\Plugins\Event\Event;

class ActorDeleteEvent extends Event
{
    const ActorDeleteEvent = "ActorDeleteEvent";

    /**
     * @param string $type
     * @param $data
     */
    public function __construct(string $type, $data)
    {
        parent::__construct($type, $data);
    }
}
