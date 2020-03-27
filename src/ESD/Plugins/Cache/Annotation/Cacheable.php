<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Cacheable extends Annotation
{
    /**
     * Cache time 0 means use default time, -1 means infinite time, invalid for namespace
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
     * This function can be achieved through the condition attribute. The condition property is empty by default,
     * which means that all call situations will be cached.
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
