<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class RequestMapping extends Annotation
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var array
     */
    public $method = [];
}