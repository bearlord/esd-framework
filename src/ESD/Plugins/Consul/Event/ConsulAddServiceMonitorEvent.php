<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul\Event;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ConsulAddServiceMonitorEvent
 * @package ESD\Plugins\Consul\Event
 */
class ConsulAddServiceMonitorEvent extends Event
{
    const ConsulAddServiceMonitorEvent = "ConsulAddServiceMonitorEvent";

    /**
     * ConsulAddServiceMonitorEvent constructor.
     * @param string $service
     */
    public function __construct(string $service)
    {
        parent::__construct(self::ConsulAddServiceMonitorEvent, $service);
    }

    /**
     * @return string
     */
    public function getService(): string
    {
        return $this->getData();
    }
}