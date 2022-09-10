<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul;

use ESD\Psr\Cloud\Leader;

/**
 * Class Leader
 * @package ESD\Plugins\Cloud\Consul
 */
class ConsulLeader implements Leader
{
    /**
     * @var bool
     */
    public $leader;

    /**
     * @return bool
     */
    public function isLeader(): bool
    {
        return $this->leader;
    }

    /**
     * @param bool $leader
     */
    public function setLeader(bool $leader): void
    {
        $this->leader = $leader;
    }
}