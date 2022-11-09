<?php

namespace ESD\Plugins\Actor;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ActorDeleteEvent
 * @package ESD\Plugins\Actor
 */
class ActorDeleteEvent extends Event
{
    const ActorDeleteEvent = "ActorDeleteEvent";

    public function __construct(string $type, $data)
    {
        parent::__construct($type, $data);
    }
}
