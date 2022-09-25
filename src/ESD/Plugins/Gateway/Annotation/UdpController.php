<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 * Class RestController
 * @package ESD\Plugins\EasyRoute\Annotation
 */
class UdpController extends Controller
{
    /**
     * @var array
     */
    public $portTypes = ["udp"];

    /**
     * @var string
     */
    public $defaultMethod = "UDP";
}