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
     * Service name
     * @var string
     */
    public $value;

    /**
     * Fill in host when no service name
     * @var string
     */
    public $host;

    /**
     * Class called on failure
     * @var string
     */
    public $fallback;

    /**
     * When a 404, should it decode instead of throwing an exception
     * @var bool
     */
    public $decode404 = false;
}