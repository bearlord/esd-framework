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
 * Class ModelAttribute
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class ModelAttribute extends Annotation
{
    /**
     * @var string
     */
    public $value;
}