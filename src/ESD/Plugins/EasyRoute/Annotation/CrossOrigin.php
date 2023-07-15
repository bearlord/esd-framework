<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class CrossOrigin extends Annotation
{

    /**
     * @var array allow origins
     */
    public $allowedOrigins = ["*"];

    /**
     * @var array allowed methods
     */
    public $allowedMethods = ["*"];

    /**
     * @var array allow headers
     */
    public $allowedHeaders = ["*"];

    /**
     * @var array exposed headers
     */
    public $exposedHeaders = ["*"];

    /**
     * @var bool allow credentials
     */
    public $allowCredentials = false;

    /**
     * @var int max age
     */
    public $maxAge = 3628800;

}