<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 * Class RestController
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class MqttController extends Controller
{
    /**
     * @var array
     */
    public $portTypes = ["mqtt"];

    /**
     * @var string
     */
    public $defaultMethod = "tcp";
}