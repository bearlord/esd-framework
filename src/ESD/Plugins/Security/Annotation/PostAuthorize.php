<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class PostAuthorize extends Annotation
{
    /**
     * @var string
     */
    public $value;
}