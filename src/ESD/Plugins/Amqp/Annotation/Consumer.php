<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp\Annotation;

use Doctrine\Common\Annotations\Annotation;
use ESD\Plugins\AnnotationsScan\Annotation\Component;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Consumer extends Component
{
    /**
     * @var string
     */
    public $exchange = '';

    /**
     * @var string
     */
    public $routingKey = '';

    /**
     * @var string
     */
    public $queue = '';

    /**
     * @var string
     */
    public $name = 'Consumer';

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var null|bool
     */
    public $enable;

    /**
     * @var int
     */
    public $maxConsumption = 0;
}