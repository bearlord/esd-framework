<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Psr\Cloud;

/**
 * Interface Leader
 * @package ESD\Psr\Cloud
 */
interface Leader
{
    /**
     * Is leader
     *
     * @return bool
     */
    public function isLeader(): bool;

    /**
     * Set leader
     *
     * @param bool $leader
     */
    public function setLeader(bool $leader): void;
}