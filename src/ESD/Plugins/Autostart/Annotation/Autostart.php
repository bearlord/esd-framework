<?php
/**
 * ESD framework
 * @author tmtbe <565364226@qq.com>
 */

namespace ESD\Plugins\Autostart\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 * Class Scheduled
 * @package ESD\Plugins\Autostart\Annotation
 */
class Autostart extends Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var int
     */
    public $delay;
}