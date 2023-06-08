<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache;

use ESD\Core\Exception;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Coroutine\Coroutine;

/**
 * Trait GetCache
 * @package ESD\Plugins\Cache
 */
trait  GetCache
{
    use GetLogger;

    /**
     * @return CacheStorage
     * @throws \Exception
     */
    public function Cache(): CacheStorage
    {
        return DIGet(CacheStorage::class);
    }

    /**
     * @param string $key
     * @param callable $callable
     * @param int|null $timeout
     * @param string|null $namespace
     * @return mixed
     * @throws \Exception
     */
    public function cacheable(string $key, callable $callable, ?int $timeout = 0, ?string $namespace = null)
    {
        $data = $this->getCache($key, $namespace);
        if ($data != null) {
            $this->debug("cache Hit!");
            return serverUnSerialize($data);
        }
        $result = $callable();
        $data = serverSerialize($result);
        $this->setCache($key, $data, $timeout, $namespace);
        return $result;
    }

    /**
     * @param string $key
     * @param callable $callable
     * @param int|null $timeout
     * @param string|null $namespace
     * @return mixed
     * @throws CacheException
     */
    public function cacheableWithLock(string $key, callable $callable, ?int $timeout = 0, ?string $namespace = null)
    {
        $config = DIget(CacheConfig::class);
        $data = $this->getCache($key, $namespace);
        if ($data != null) {
            $this->debug("Cache Hit!");
            return serverUnSerialize($data);
        }

        if ($config->getLockTimeout() > 0) {
            if ($config->getLockAlive() < $config->getLockTimeout()) {
                $this->alert("Cache cache configuration item lockAlive must be greater than lockTimeout, please correct the parameters");
            }

            if ($token = $this->Cache()->lock($key, $config->getLockAlive())) {
                $result = $callable();
                $data = serverSerialize($result);
                $this->setCache($key, $data, $timeout, $namespace);
                $this->Cache()->unlock($key, $token);

            } else {
                $i = 0;
                do {
                    $result = $this->getCache($key, $namespace);
                    if ($result) {
                        break;
                    }

                    Coroutine::sleep($config->getLockWait() / 1000.0);
                    $i += $config->getLockWait();
                    if ($i >= $config->getLockTimeout()) {
                        if ($config->getLockThrowException()) {
                            throw new CacheException('Cache key lock timeout' . $key);
                        } else {
                            $result = $callable();
                        }
                        $this->warn('Lock wait timeout ' . $key . ',' . $i);
                        break;
                    } else {
                        $this->debug('Lock wait ' . $key . ',' . $i);
                    }
                } while ($i <= $config->getLockTimeout());
            }
        } else {
            $this->info("lock_timeout configuration is off, with lock function is invalid");
            $result = $callable();
            $data = serverSerialize($result);
            $this->setCache($key, $data, $timeout, $namespace);
        }
        return $result;
    }

    /**
     * @param string $key
     * @param string|null $namespace
     * @return mixed
     * @throws \Exception
     */
    public function getCache(string $key, ?string $namespace = null)
    {
        if (is_null($namespace)) {
            $data = $this->Cache()->get($key);
        } else {
            $data = $this->Cache()->getFromNameSpace($namespace, $key);
        }
        return $data;
    }

    /**
     * @param string $key
     * @param string $data
     * @param int|null $timeout
     * @param string|null $namespace
     * @return void
     * @throws \Exception
     */
    public function setCache(string $key, string $data, ?int $timeout = 0, ?string $namespace = null): void
    {
        if (is_null($namespace)) {
            $ret = $this->Cache()->set($key, $data, $timeout);
        } else {
            $ret = $this->Cache()->setFromNameSpace($namespace, $key, $data);
        }

        if (!$ret) {
            $this->warn(sprintf("Set cache key: %s error", $key));
        }
    }

}