<?php

namespace ESD\TokenBucket\Storage;

use ESD\TokenBucket\Storage\scope\GlobalScope;
use ESD\TokenBucket\Util\DoublePacker;
use Redis;
use RedisException;
use malkusch\lock\Mutex\RedisMutex;
use malkusch\lock\Mutex\Mutex;

/**
 * Redis based storage which uses the phpredis extension.
 *
 * This storage is in the global scope.
 *
 * This implementation requires at least phpredis-2.2.4.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
final class PHPRedisStorage implements Storage, GlobalScope
{
    /**
     * The mutex.
     */
    private RedisMutex $mutex;

    /**
     * Sets the connected Redis API.
     *
     * The Redis API needs to be connected yet. I.e. Redis::connect() was
     * called already.
     *
     * @param string $name The resource name.
     * @param Redis $redis The Redis API.
     */
    public function __construct(private string $key, private Redis $redis)
    {
        $this->mutex = new PHPRedisMutex([$redis], $this->key);
    }

    public function bootstrap($microtime)
    {
        $this->setMicrotime($microtime);
    }

    public function isBootstrapped()
    {
        try {
            return (bool) $this->redis->exists($this->key);
        } catch (RedisException $e) {
            throw new StorageException("Failed to check for key existence", 0, $e);
        }
    }

    public function remove()
    {
        try {
            if (! $this->redis->del($this->key)) {
                throw new StorageException("Failed to delete key");
            }
        } catch (RedisException $e) {
            throw new StorageException("Failed to delete key", 0, $e);
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function setMicrotime($microtime)
    {
        try {
            $data = DoublePacker::pack($microtime);

            if (! $this->redis->set($this->key, $data)) {
                throw new StorageException("Failed to store microtime");
            }
        } catch (RedisException $e) {
            throw new StorageException("Failed to store microtime", 0, $e);
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function getMicrotime()
    {
        try {
            $data = $this->redis->get($this->key);
            if ($data === false) {
                throw new StorageException("Failed to get microtime");
            }
            return DoublePacker::unpack($data);
        } catch (RedisException $e) {
            throw new StorageException("Failed to get microtime", 0, $e);
        }
    }

    public function getMutex()
    {
        return $this->mutex;
    }

    public function letMicrotimeUnchanged()
    {
    }
}
