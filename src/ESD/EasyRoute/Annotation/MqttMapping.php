<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class MqttMapping extends RequestMapping
{
    public $method = ["mqtt"];
}