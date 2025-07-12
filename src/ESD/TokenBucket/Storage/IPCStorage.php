<?php

namespace ESD\TokenBucket\Storage;

use malkusch\lock\mutex\Mutex;
use malkusch\lock\mutex\SemaphoreMutex;
use ESD\TokenBucket\Storage\scope\GlobalScope;
use ESD\TokenBucket\Util\DoublePacker;
use SysvSharedMemory;
use SysvSemaphore;

/**
 * Shared memory based storage which can be shared among processes of a single host.
 *
 * This storage is in the global scope. However the scope is limited to the
 * shared memory. I.e. the scope is not shared between hosts.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
final class IPCStorage implements Storage, GlobalScope
{

    /**
     * The mutex.
     */
    private ?Mutex $mutex = null;

    /**
     * The System V IPC key.
     */
    private int $key;

    /**
     * The shared memory.
     */
    private ?SysvSharedMemory $memory = null;

    /**
     * The semaphore id.
     */
    private ?SysvSemaphore $semaphore = null;

    /**
     * Sets the System V IPC key for the shared memory and its semaphore.
     *
     * You can create the key with PHP's function ftok().
     *
     * @param int $key The System V IPC key.
     *
     * @throws StorageException Could initialize IPC infrastructure.
     */
    public function __construct(int $key)
    {
        $this->key = $key;
        $this->attach();
    }

    /**
     * Attaches the shared memory segment.
     *
     * @throws StorageException Could not initialize IPC infrastructure.
     */
    private function attach()
    {
        try {
            $this->semaphore = sem_get($this->key);
            $this->mutex = new SemaphoreMutex($this->semaphore);
        } catch (\InvalidArgumentException $e) {
            throw new StorageException("Could not get semaphore id.", 0, $e);
        }

        $this->memory = shm_attach($this->key, 128);
    }

    public function bootstrap($microtime)
    {
        if (is_null($this->memory)) {
            $this->attach();
        }
        $this->setMicrotime($microtime);
    }

    public function isBootstrapped()
    {
        return ! is_null($this->memory) && shm_has_var($this->memory, 0);
    }

    public function remove()
    {
        if ($this->memory && ! shm_remove($this->memory)) {
            throw new StorageException("Could not release shared memory.");
        }
        $this->memory = null;

        if ($this->semaphore && ! sem_remove($this->semaphore)) {
            throw new StorageException("Could not remove semaphore.");
        }
        $this->semaphore = null;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function setMicrotime($microtime)
    {
        $data = DoublePacker::pack($microtime);
        if (! $this->memory) {
            throw new StorageException("Could not store in shared memory.");
        }

        if (! shm_put_var($this->memory, 0, $data)) {
            throw new StorageException("Could not store in shared memory.");
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function getMicrotime()
    {
        $data = shm_get_var($this->memory, 0);
        if ($data === false) {
            throw new StorageException("Could not read from shared memory.");
        }
        return DoublePacker::unpack($data);
    }

    public function getMutex()
    {
        return $this->mutex;
    }

    public function letMicrotimeUnchanged()
    {
    }
}
