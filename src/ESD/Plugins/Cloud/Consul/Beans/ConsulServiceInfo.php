<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Beans;

use ESD\Psr\Cloud\ServiceInfo;

/**
 * Consul service info
 *
 * Class ConsulServiceInfo
 * @package ESD\Plugins\Cloud\Consul\Beans
 */
class ConsulServiceInfo extends ServiceInfo
{
    /**
     * ConsulServiceInfo constructor.
     * @param $serviceName
     * @param $serviceId
     * @param $serviceAddress
     * @param $servicePort
     * @param $serviceMeta
     * @param $serviceTags
     */
    public function __construct($serviceName, $serviceId, $serviceAddress, $servicePort, $serviceMeta, $serviceTags)
    {
        $serviceProtocol = $serviceMeta['protocol'];
        parent::__construct($serviceName, $serviceId, $serviceAddress, $servicePort, $serviceMeta, $serviceTags, $serviceProtocol);
    }
}