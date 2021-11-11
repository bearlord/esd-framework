<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace  ESD\Plugins\Amqp\Annotation;

use Doctrine\Common\Annotations\Annotation;
use ESD\Plugins\AnnotationsScan\Annotation\Component;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Producer extends Component
{
    /**
     * @var string
     */
    public $exchange = '';

    /**
     * @var string
     */
    public $routingKey = '';
}
