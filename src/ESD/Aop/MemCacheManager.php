<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Aop;


use Go\Core\AspectKernel;
use Go\Instrument\ClassLoading\CachePathManager;

/**
 * Class MemCacheManager
 * @package ESD\Aop
 */
class MemCacheManager extends CachePathManager
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var \Go\Core\AspectKernel
     */
    protected $kernel;

    /**
     * @var string|null
     */
    protected $appDir;

    /**
     * Cached metadata for transformation state for the concrete file
     *
     * @var array
     */
    protected $cacheState = [];

    /**
     * New metadata items, that was not present in $cacheState
     *
     * @var array
     */
    protected $newCacheState = [];

    public function __construct(AspectKernel $kernel)
    {
        $this->kernel = $kernel;
        $this->options = $kernel->getOptions();
        $this->appDir = $this->options['appDir'];
    }

    /**
     * @param string $resource
     * @return bool|string
     */
    public function getCachePathForResource($resource)
    {
        return str_replace($this->appDir, '', $resource);
    }

    /**
     * @param null $resource
     * @return array|mixed|null
     */
    public function queryCacheState($resource = null)
    {
        if ($resource === null) {
            return $this->cacheState;
        }

        if (isset($this->newCacheState[$resource])) {
            return $this->newCacheState[$resource];
        }

        if (isset($this->cacheState[$resource])) {
            return $this->cacheState[$resource];
        }

        return null;
    }

    /**
     * Put a record about some resource in the cache
     *
     * This data will be persisted during object destruction
     *
     * @param string $resource Name of the file
     * @param array $metadata Miscellaneous information about resource
     */
    public function setCacheState($resource, array $metadata)
    {
        $this->newCacheState[$resource] = $metadata;
    }

    /**
     * Automatic destructor saves all new changes into the cache
     *
     * This implementation is not thread-safe, so be care
     */
    public function __destruct()
    {
        $this->flushCacheState();
    }

    /**
     * Flushes the cache state into the file
     *
     * @var bool $force Should be flushed regardless of its state.
     */
    public function flushCacheState($force = false)
    {
        if ((!empty($this->newCacheState) || $force)) {
            $this->cacheState = $this->newCacheState + $this->cacheState;
            $this->newCacheState = [];
        }
    }

    /**
     * Clear the cache state.
     */
    public function clearCacheState()
    {
        $this->cacheState = [];
        $this->newCacheState = [];

        $this->flushCacheState(true);
    }
}