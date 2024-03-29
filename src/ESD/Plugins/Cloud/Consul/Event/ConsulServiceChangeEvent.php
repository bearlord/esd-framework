<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Event;

use ESD\Core\Plugins\Event\Event;
use ESD\Plugins\Cloud\Consul\Beans\ConsulServiceListInfo;

/**
 * Class ConsulServiceChangeEvent
 * @package ESD\Plugins\Cloud\Consul\Event
 */
class ConsulServiceChangeEvent extends Event
{
    const ConsulServiceChangeEvent = "ConsulServiceChangeEvent";

    /**
     * ConsulServiceChangeEvent constructor.
     * @param ConsulServiceListInfo $consulServiceListInfo
     */
    public function __construct(ConsulServiceListInfo $consulServiceListInfo)
    {
        parent::__construct(self::ConsulServiceChangeEvent, $consulServiceListInfo);
    }

    /**
     * @return ConsulServiceListInfo
     */
    public function getConsulServiceListInfo(): ConsulServiceListInfo
    {
        return $this->getData();
    }
}