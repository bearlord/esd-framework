<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Core\Pool;

interface PoolInterface
{
    /**
     * Get a connection from the connection pool.
     */
    public function get(): ConnectionInterface;

    /**
     * Release a connection back to the connection pool.
     */
    public function release(ConnectionInterface $connection): void;

    /**
     * Close and clear the connection pool.
     */
    public function flush(): void;

    /**
     * Get option
     */
    public function getOption(): PoolOptionInterface;
}
