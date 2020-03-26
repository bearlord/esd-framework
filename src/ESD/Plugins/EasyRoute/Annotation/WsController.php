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
class WsController extends Controller
{
    /**
     * @var array
     */
    public $portTypes = ["ws"];

    /**
     * @var string
     */
    public $defaultMethod = "WS";
}