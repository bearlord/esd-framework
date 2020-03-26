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
class ResponseBody extends Annotation
{
    /**
     * @var string
     */
    public $value = "application/json;charset=UTF-8";
}