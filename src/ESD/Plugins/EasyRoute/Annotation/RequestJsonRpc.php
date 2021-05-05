<?php
/**
 * ESD framework
 * @author tmtbe <565364226@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RequestJsonRpc extends Annotation
{
    /**
     * @var string
     */
    public $value;
}