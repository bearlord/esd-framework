<?php
/**
 * ESD framework
 * @author tmtbe <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class ResponeJsonRpc extends Annotation
{
    /**
     * @var string
     */
    public $value = "application/json;charset=UTF-8";
}