<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul\Event;


use ESD\Core\Plugins\Event\Event;

class ConsulLeaderChangeEvent extends Event
{
    const ConsulLeaderChangeEvent = "ConsulLeaderChangeEvent";

    public function __construct(bool $isLeader)
    {
        parent::__construct(self::ConsulLeaderChangeEvent, $isLeader);
    }

    public function isLeader(): bool
    {
        return $this->getData();
    }
}