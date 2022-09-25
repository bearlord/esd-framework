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
class RestController extends Controller
{
    /**
     * @var array
     */
    public $portTypes = ["http"];

    /**
     * @var string
     */
    public $defaultMethod = "GET";
}