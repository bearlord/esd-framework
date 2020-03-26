<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

use ESD\Plugins\AnnotationsScan\Annotation\Component;

/**
 * @Annotation
 * @Target("CLASS")
 * Class RestController
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class Controller extends Component
{
    /**
     * Route prefix
     * @var string
     */
    public $value = "";

    /**
     * Default method
     * @var string
     */
    public $defaultMethod;

    /**
     * Port access type, http, ws, tcp, udp, unlimited if empty array
     * @var array
     */
    public $portTypes = [];

    /**
     * Port name, unlimited if empty array
     * @var array
     */
    public $portNames = [];
}