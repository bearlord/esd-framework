<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Psr\Cloud;

/**
 * Interface Services
 * @package ESD\Psr\Cloud
 */
interface Services
{
    /** Get Service info list
     *
     * @param string $service
     * @return ServiceInfoList|null
     */
    public function getServices(string $service): ?ServiceInfoList;

    /**
     * Get Service info
     *
     * @param string $service
     * @return ServiceInfo|null
     */
    public function getService(string $service): ?ServiceInfo;
}