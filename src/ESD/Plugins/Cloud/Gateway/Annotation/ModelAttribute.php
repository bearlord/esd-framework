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
 * Class ModelAttribute
 * @package ESD\Plugins\Cloud\Gateway\Annotation
 */
class ModelAttribute extends Annotation
{
    /**
     * @var string
     */
    public $value;
}