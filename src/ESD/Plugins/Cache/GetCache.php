<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache;

use ESD\Core\Exception;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Coroutine\Co;

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
     * @param $key
     * @param callable $callable
     * @param int $timeout
     * @param null $namespace
     * @return mixed
     * @throws \Exception
     */
    public function cacheable($key, callable $callable, $timeout = 0, $namespace = null)
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
     * @param $key
     * @param callable $callable
     * @param int $timeout
     * @param null $namespace
     * @return mixed
     * @throws CacheException
     */
    public function cacheableWithLock($key, callable $callable, $timeout = 0, $namespace = null)
    {
        $config = DIget(CacheConfig::class);
        $data = $this->getCache($key, $namespace);
        if ($data != null) {
            $this->debug("cache Hit!");
            return serverUnSerialize($data);
        }

        if ($config->getLockTimeout() > 0) {
            if ($config->getLockAlive() < $config->getLockTimeout()) {
                $this->alert("cache cache configuration item lockAlive must be greater than lockTimeout, please correct the parameters");
            }

            if ($token = $this->Cache()->lock($key, $config->getLockAlive())) {
                $result = $callable();
                $data = serverSerialize($result);
                $this->setCache($key, $data, $timeout, $namespace);
                $this->Cache()->unlock($key, $token);

            } else {
                $i = 0;
                do {
                    $result = $this->getCache($key, $callable);
                    if ($result) {
                        break;
                    }

                    Co::sleep($config->getLockWait() / 1000.0);
                    $i += $config->getLockWait();
                    if ($i >= $config->getLockTimeout()) {
                        if ($config->getLockThrowException()) {
                            throw new CacheException('cache key lock timeout' . $key);
                        } else {
                            $result = $callable();
                        }
                        $this->warn('lock wait timeout ' . $key . ',' . $i);
                        break;
                    } else {
                        $this->debug('lock wait ' . $key . ',' . $i);
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
     * @param $key
     * @param null $namespace
     * @return mixed
     * @throws \Exception
     */
    public function getCache($key, $namespace = null)
    {
        if (is_null($namespace)) {
            $data = $this->Cache()->get($key);
        } else {
            $data = $this->Cache()->getFromNameSpace($namespace, $key);
        }
        return $data;
    }

    /**
     * @param $key
     * @param $data
     * @param int $timeout
     * @param null $namespace
     * @throws \Exception
     */
    public function setCache($key, $data, $timeout = 0, $namespace = null): void
    {

        if (is_null($namespace)) {
            $ret = $this->Cache()->set($key, $data, $timeout);
        } else {
            $ret = $this->Cache()->setFromNameSpace($namespace, $key, $data);
        }

        if (!$ret) {
            $this->warn('cache key:' . $key . ' set fail ');
        }
    }

}