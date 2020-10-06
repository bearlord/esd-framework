<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class CacheConfig
 * @package ESD\Plugins\Cache
 */
class CacheConfig extends BaseConfig
{
    const KEY = "cache";
    
    /**
     *
     * Time out
     * @var int
     */
    protected $timeout = 30 * 60;

    /**
     * @var string
     */
    protected $db = "default";

    /**
     * @var string
     */
    protected $cacheStorageClass = RedisCacheStorage::class;
    
    /**
     * Cache write lock, read lock wait time. Unit (ms)
     * If set to 0, read-write lock is not enabled
     * It is recommended to enable this setting for high concurrency, and increase the redis connection pool and the number of redis connections.
     * If 2000 concurrently per second and the timeout is set to 3 seconds, there will be 2000 connections waiting.
     * If the second level set is recommended to set 3000
     * If several hundred milliseconds set is recommended to set 1000
     * If less than 100 milliseconds, it is recommended to set 500
     * @var int
     */
    protected $lockTimeout = 0;


    /**
     * Read lock waiting time, in milliseconds.
     * Retry every lockWait. If cache write takes seconds, it is recommended to adjust lockWait to more than 500 milliseconds.
     * If cache writes take hundreds of milliseconds, it is recommended to use the default 100 milliseconds
     * If cache write takes less than one hundred milliseconds, 50 milliseconds is recommended
     * Note: Setting this value too low will seriously increase the load of redis get. If there are 2000 concurrently per second,
     * the timeout is set for 3 seconds, and the default lock waits for 100 milliseconds, then the worst will have 60,000 get operations
     * Note: Do not wait longer than lockTimeout, otherwise it will waste coroutine resources
     *
     * @var int
     */
    protected $lockWait = 100;


    /**
     * Deadlock expiration time unit (ms)
     * Note: This setting must be longer than the read lock wait time, otherwise
     * if the lock is released and not yet written to the cache, it will also cause cache penetration.
     * @var int
     */
    protected $lockAlive = 10000;

    /**
     * Whether an exception is thrown when the deadlock expires, if not, it will directly check the library
     * @var bool
     */
    protected $lockThrowException = false;

    /**
     * CacheConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return int
     */
    public function getLockAlive(): int
    {
        return $this->lockAlive;
    }

    /**
     * @param $lockAlive
     */
    public function setLockAlive($lockAlive): void
    {
        $this->lockAlive = $lockAlive;
    }

    /**
     * @param int $timeout
     */
    public function setLockTimeout(int $timeout): void
    {
        $this->lockTimeout = $timeout;
    }

    /**
     * @return int
     */
    public function getLockTimeout(): int
    {
        return $this->lockTimeout;
    }

    /**
     * @return int
     */
    public function getLockWait(): int
    {
        return $this->lockWait;
    }

    /**
     * @param $lockwait
     */
    public function setLockWait($lockwait): void
    {
        $this->lockWait = $lockwait;
    }

    /**
     * @return bool
     */
    public function getLockThrowException(): bool
    {
        return $this->lockThrowException;
    }

    /**
     * @param bool $isThrow
     */
    public function setLockThrowException(bool $isThrow): void
    {
        $this->lockThrowException = $isThrow;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getDb(): string
    {
        return $this->db;
    }

    /**
     * @param string $db
     */
    public function setDb(string $db): void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getCacheStorageClass(): string
    {
        return $this->cacheStorageClass;
    }

    /**
     * @param string $cacheStorageClass
     */
    public function setCacheStorageClass(string $cacheStorageClass): void
    {
        $this->cacheStorageClass = $cacheStorageClass;
    }
}