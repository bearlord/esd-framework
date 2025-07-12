<?php

namespace ESD\TokenBucket\Storage;

use malkusch\lock\mutex\NoMutex;
use ESD\TokenBucket\Storage\scope\RequestScope;

/**
 * In-memory token storage which is only used for one single process.
 *
 * This storage is in the request scope. It is not shared among processes and
 * therefore needs no locking.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
final class SingleProcessStorage implements Storage, RequestScope
{
    /**
     * The mutex.
     */
    private NoMutex $mutex;

    /**
     * @var double The microtime.
     */
    private $microtime;

    /**
     * Initialization.
     */
    public function __construct()
    {
        $this->mutex = new NoMutex();
    }

    public function isBootstrapped()
    {
        return ! is_null($this->microtime);
    }

    public function bootstrap($microtime)
    {
        $this->setMicrotime($microtime);
    }

    public function remove()
    {
        $this->microtime = null;
    }

    public function setMicrotime($microtime)
    {
        $this->microtime = $microtime;
    }

    public function getMicrotime()
    {
        return $this->microtime;
    }

    public function getMutex()
    {
        return $this->mutex;
    }

    public function letMicrotimeUnchanged()
    {
    }
}
