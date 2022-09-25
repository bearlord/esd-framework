<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Annotation;

use ESD\Plugins\EasyRoute\Annotation\Controller;

/**
 * @Annotation
 * @Target("CLASS")
 */
class RestGatewayController extends Controller
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