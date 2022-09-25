<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class MqttMapping extends RequestMapping
{
    public $method = ["mqtt"];
}