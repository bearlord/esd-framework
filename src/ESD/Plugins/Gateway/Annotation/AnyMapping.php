<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class AnyMapping extends RequestMapping
{
    /**
     * @var array
     */
    public $method = ["get","post","delete","put","options","head","trace","connect"];
}