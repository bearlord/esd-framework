<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Event;

use ESD\Core\Plugins\Event\Event;
use ESD\Plugins\Cloud\Consul\Beans\ConsulServiceListInfo;

/**
 * Class ConsulOneServiceChangeEvent
 * @package ESD\Plugins\Cloud\Consul\Event
 */
class ConsulOneServiceChangeEvent extends Event
{
    const ConsulOneServiceChangeEvent = "ConsulOneServiceChangeEvent";

    /**
     * ConsulOneServiceChangeEvent constructor.
     * @param string $type
     * @param ConsulServiceListInfo $consulServiceListInfo
     */
    public function __construct(string $type, ConsulServiceListInfo $consulServiceListInfo)
    {
        parent::__construct(self::ConsulOneServiceChangeEvent . "::$type", $consulServiceListInfo);
    }

    /**
     * @return ConsulServiceListInfo
     */
    public function getConsulServiceListInfo(): ConsulServiceListInfo
    {
        return $this->getData();
    }
}