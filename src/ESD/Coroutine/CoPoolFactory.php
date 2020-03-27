<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine;

use ESD\Coroutine\Pool\CoroutinePoolExecutor;

/**
 * Class CoPoolFactory
 * @package ESD\Coroutine
 */
class CoPoolFactory
{
    /**
     * @var CoroutinePoolExecutor[]
     */
    private static $factory = [];

    /**
     * Create coroutine pool
     * @param string $name
     * @param int $corePoolSize
     * @param int $maximumPoolSize
     * @param float $keepAliveTime
     * @return CoroutinePoolExecutor
     * @throws \Exception
     */
    public static function createCoroutinePool(string $name, int $corePoolSize, int $maximumPoolSize, float $keepAliveTime): CoroutinePoolExecutor
    {
        $coPool = new CoroutinePoolExecutor($corePoolSize, $maximumPoolSize, $keepAliveTime);
        self::addCoroutinePool($name, $coPool);
        return $coPool;
    }

    /**
     * Add coroutine pool
     *
     * @param string $name
     * @param CoroutinePoolExecutor $coroutinePoolExecutor
     * @throws \Exception
     */
    public static function addCoroutinePool(string $name, CoroutinePoolExecutor $coroutinePoolExecutor)
    {
        if (isset(self::$factory[$name])) {
            throw new \Exception("协程池命名重复");
        }
        $coroutinePoolExecutor->setName($name);
        self::$factory[$name] = $coroutinePoolExecutor;
    }

    /**
     * Get coroutine pool
     *
     * @param string $name
     * @return mixed|null
     */
    public static function getCoroutinePool(string $name)
    {
        return self::$factory[$name] ?? null;
    }

    /**
     * Delete coroutine pool
     *
     * @param string $name
     */
    public static function deleteCoroutinePool(string $name)
    {
        $pool = self::$factory[$name] ?? null;
        if ($pool != null) {
            $pool->destroy();
            unset(self::$factory[$name]);
        }
    }
}