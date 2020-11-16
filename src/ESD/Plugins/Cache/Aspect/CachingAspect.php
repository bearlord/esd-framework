<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache\Aspect;

use DI\DependencyException;
use DI\NotFoundException;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Coroutine\Coroutine;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Cache\Annotation\Cacheable;
use ESD\Plugins\Cache\Annotation\CacheEvict;
use ESD\Plugins\Cache\Annotation\CachePut;
use ESD\Plugins\Cache\CacheConfig;
use ESD\Plugins\Cache\CacheException;
use ESD\Plugins\Cache\CacheStorage;
use ESD\Plugins\Redis\GetRedis;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

/**
 * Caching aspect
 */
class CachingAspect extends OrderAspect
{
    use GetLogger;
    use GetRedis;

    /**
     * @var CacheStorage
     */
    private $cacheStorage;

    /**
     * @var CacheConfig
     */
    private $config;

    /**
     * CachingAspect constructor.
     * @param CacheStorage $cacheStorage
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function __construct(CacheStorage $cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
        $this->config = DIget(CacheConfig::class);
    }


    /**
     * Around cacheable
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @return mixed
     * @throws CacheException
     * @Around("@execution(ESD\Plugins\Cache\Annotation\Cacheable)")
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        $cacheable = $invocation->getMethod()->getAnnotation(Cacheable::class);

        $p = $invocation->getArguments();

        $key = null;
        if (!empty($cacheable->key)) {
            $key = eval("return (" . $cacheable->key . ");");
        }
        if (empty($key)) {
            $this->warn("cache key is empty,ignore this cache");
            return $invocation->proceed();
        } else {
            $this->debug("cache get namespace:{$cacheable->namespace} key:{$key}");

            $condition = true;
            if (!empty($cacheable->condition)) {
                $condition = eval("return (" . $cacheable->condition . ");");
            }
            $data = null;
            $data = $this->getCache($key, $cacheable);
            if ($data != null) {
                $this->debug("cache Hit!");
                return serverUnSerialize($data);
            }
            if ($condition) {
                if ($this->config->getLockTimeout() > 0) {
                    if ($this->config->getLockAlive() < $this->config->getLockTimeout()) {
                        $this->alert('cache cache configuration item lockAlive must be greater than lockTimeout, please correct the parameters immediately');
                    }

                    if ($token = $this->cacheStorage->lock($key, $this->config->getLockAlive())) {
                        $result = $invocation->proceed();
                        $data = serverSerialize($result);
                        $this->setCache($key, $data, $cacheable);
                        $this->cacheStorage->unlock($key, $token);

                    } else {
                        $i = 0;
                        do {
                            $result = $this->getCache($key, $cacheable);
                            if ($result) break;
                            Coroutine::sleep($this->config->getLockWait() / 1000.0);
                            $i += $this->config->getLockWait();
                            if ($i >= $this->config->getLockTimeout()) {
                                if($this->config->getLockThrowException()){
                                    throw new CacheException('cache key lock timeout' . $key);
                                }else{
                                    $result = $invocation->proceed();
                                }
                                $this->warn('lock wait timeout ' . $key . ',' . $i);
                                break;
                            } else {
                                $this->debug('lock wait ' . $key . ',' . $i);
                            }
                        } while ($i <= $this->config->getLockTimeout());
                    }
                } else {
                    $result = $invocation->proceed();
                    $data = serverSerialize($result);
                    $this->setCache($key, $data, $cacheable);
                }
            } else {
                $result = $invocation->proceed();
            }
            return $result;
        }
    }


    /**
     * This advice intercepts an execution of cachePut methods
     *
     * Logic is pretty simple: we look for the value in the cache and if it's not present here
     * then invoke original method and store it's result in the cache.
     *
     * Real-life examples will use APC or Memcache to store value in the cache
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @return mixed
     * @throws \Exception
     * @Around("@execution(ESD\Plugins\Cache\Annotation\CachePut)")
     */
    public function aroundCachePut(MethodInvocation $invocation)
    {
        $cachePut = $invocation->getMethod()->getAnnotation(CachePut::class);

        $p = $invocation->getArguments();

        $key = null;
        if (!empty($cachePut->key)) {
            $key = eval("return (" . $cachePut->key . ");");
        }
        if (empty($key)) {
            $this->warn("cache key is empty,ignore this cache");
            $result = $invocation->proceed();
        } else {
            $this->debug("cache put namespace:{$cachePut->namespace} key:{$key}");

            $condition = true;
            if (!empty($cachePut->condition)) {
                $condition = eval("return (" . $cachePut->condition . ");");
            }

            $result = $invocation->proceed();

            if ($condition) {
                $data = serverSerialize($result);
                if (empty($cachePut->namespace)) {
                    $this->cacheStorage->set($key, $data, $cachePut->time);
                } else {
                    $this->cacheStorage->setFromNameSpace($cachePut->namespace, $key, $data);
                }
            }
        }
        return $result;
    }

    /**
     * This advice intercepts an execution of cacheEvict methods
     *
     * Logic is pretty simple: we look for the value in the cache and if it's not present here
     * then invoke original method and store it's result in the cache.
     *
     * Real-life examples will use APC or Memcache to store value in the cache
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @return mixed
     * @throws \Exception
     * @Around("@execution(ESD\Plugins\Cache\Annotation\CacheEvict)")
     */
    public function aroundCacheEvict(MethodInvocation $invocation)
    {
        $cacheEvict = $invocation->getMethod()->getAnnotation(CacheEvict::class);

        $p = $invocation->getArguments();

        $key = null;
        if (!empty($cacheEvict->key)) {
            $key = eval("return (" . $cacheEvict->key . ");");
        }
        if (empty($key) && ($cacheEvict->allEntries == false || empty($cacheEvict->namespace))) {
            $this->warn("cache key is empty,ignore this cache");
            $result = $invocation->proceed();
        } else {
            $this->debug("cache evict namespace:{$cacheEvict->namespace} key:{$key}");
            $result = null;
            if ($cacheEvict->beforeInvocation) {
                $result = $invocation->proceed();
            }
            if (empty($cacheEvict->namespace)) {
                $this->cacheStorage->remove($key);
            } else {
                if ($cacheEvict->allEntries) {
                    $this->cacheStorage->removeNameSpace($cacheEvict->namespace);
                } else {
                    $this->cacheStorage->removeFromNameSpace($cacheEvict->namespace, $key);
                }
            }
            if (!$cacheEvict->beforeInvocation) {
                $result = $invocation->proceed();
            }
        }
        return $result;
    }

    /**
     * Get cache
     *
     * @param $key
     * @param Cacheable $cacheable
     * @return mixed
     */
    public function getCache($key, Cacheable $cacheable)
    {
        if (empty($cacheable->namespace)) {
            $data = $this->cacheStorage->get($key);
        } else {
            $data = $this->cacheStorage->getFromNameSpace($cacheable->namespace, $key);
        }
        return $data;
    }

    /**
     * Set cache
     *
     * @param $key
     * @param $data
     * @param Cacheable $cacheable
     * @throws \Exception
     */
    public function setCache($key, $data, Cacheable $cacheable): void
    {

        if (empty($cacheable->namespace)) {
            $ret = $this->cacheStorage->set($key, $data, $cacheable->time);
        } else {
            $ret = $this->cacheStorage->setFromNameSpace($cacheable->namespace, $key, $data);
        }

        if (!$ret) {
            $this->warn('cache key:' . $key . ' set fail ');
        }
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "CachingAspect";
    }
}
