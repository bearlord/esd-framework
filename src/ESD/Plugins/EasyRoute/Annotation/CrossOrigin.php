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
     * @var string allow origins
     */
    public $allowedOrigins = "*";

    /**
     * @var string[] allowed methods
     */
    public $allowedMethods = ["PUT", "DELETE", "POST", "GET"];

    /**
     * @var array allow headers
     */
    public $allowedHeaders = [];

    /**
     * @var array exposed headers
     */
    public $exposedHeaders = [];

    /**
     * @var bool allow credentials
     */
    public $allowCredentials = false;

}