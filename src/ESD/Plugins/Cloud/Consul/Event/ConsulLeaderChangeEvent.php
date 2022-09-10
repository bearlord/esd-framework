<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Event;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ConsulLeaderChangeEvent
 * @package ESD\Plugins\Cloud\Consul\Event
 */
class ConsulLeaderChangeEvent extends Event
{
    const ConsulLeaderChangeEvent = "ConsulLeaderChangeEvent";

    /**
     * ConsulLeaderChangeEvent constructor.
     * @param bool $isLeader
     */
    public function __construct(bool $isLeader)
    {
        parent::__construct(self::ConsulLeaderChangeEvent, $isLeader);
    }

    /**
     * @return bool
     */
    public function isLeader(): bool
    {
        return $this->getData();
    }
}