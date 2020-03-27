<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * For methods marked with @Cacheable, before each execution, it is checked whether there are cache elements with the same key in the cache.
 * If it exists, the method is no longer executed, but the result is directly obtained from the cache and returned,
 * otherwise it is executed and the returned result is stored in the specified cache.
 * @CachePut can also declare a method to support caching.
 * The difference with @Cacheable is that the method marked with @CachePut does not check the results of previous executions before execution,
 * Instead, the method is executed every time, and the execution result is stored in the specified cache in the form of key-value pairs.
 *
 * @Annotation
 * @Target("METHOD")
 */
class CachePut extends Annotation
{
    /**
     * Cache time 0 means use the default time, -1 means infinite time, invalid for namespaces
     *
     * @var int
     */
    public $time = 0;

    /**
     * Represents the unique cache key in the namespace to be deleted.
     * Use php syntax, $p[0] to get the corresponding parameters
     * @var string
     */
    public $key = "";

    /**
     * Sometimes we may not want to cache all the results returned by a method.
     * This function can be achieved through the condition attribute. The condition property is empty by default, which means that all call situations will be cached.
     * The value is specified by a PHP expression. When true, it means cache processing;
     * When false, it means no cache processing, that is, the method will be executed once each time the method is called.
     * @var string
     */
    public $condition = "";

    /**
     * Namespace
     * @var string
     */
    public $namespace = "";
}
