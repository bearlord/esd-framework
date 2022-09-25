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
 * @package ESD\Plugins\Cloud\Gateway\Annotation
 */
class TcpController extends Controller
{
    /**
     * @var array
     */
    public $portTypes = ["tcp"];

    /**
     * @var string
     */
    public $defaultMethod = "TCP";
}