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
class CacheEvict extends Annotation
{
    /**
     * Represents the unique cache key in the namespace to be deleted.
     * Use php syntax, $p[0] to get the corresponding parameters
     * @var string
     */
    public $key = "";

    /**
     * Namespace
     * @var string
     */
    public $namespace = "";

    /**
     * Flag whether to delete all caches in the namespace, default is false
     * @var bool
     */
    public $allEntries = false;

    /**
     * By default, the clear operation is triggered after the corresponding method is successfully executed, that is,
     * if the method fails to return because of throwing an exception, the clear operation will not be triggered.
     * Using beforeInvocation can change the time when the clear operation is triggered.
     * When we specify the value of this property as true, the specified element in the cache will be cleared before calling this method.
     * @var bool
     */
    public $beforeInvocation = false;
}
