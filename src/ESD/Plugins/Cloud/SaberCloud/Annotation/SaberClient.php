<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\SaberCloud\Annotation;


use ESD\Plugins\AnnotationsScan\Annotation\Component;

/**
 * @Annotation
 * @Target("CLASS")
 * Class SaberClient
 * @package ESD\Plugins\Cloud\SaberCloud\Annotation
 */
class SaberClient extends Component
{
    /**
     * 服务名称
     * @var string
     */
    public $value;
    /**
     * 没有服务名称，可以填host
     * @var string
     */
    public $host;
    /**
     * 失败调用的类
     * @var string
     */
    public $fallback;

    /**
     * 404s是否应该解码而不是抛出异常
     * @var bool
     */
    public $decode404 = false;
}