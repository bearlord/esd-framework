<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RequestFormData extends Annotation
{
    /**
     * @var string
     */
    public $value;
    /**
     * @var string|null
     */
    public $param;
    /**
     * @var bool
     */
    public $required = false;
}