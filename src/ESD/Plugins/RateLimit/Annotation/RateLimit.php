<?php

namespace ESD\Plugins\RateLimit\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RateLimit extends Annotation
{

    /**
     * @var int Number of tokens generated per second
     */
    public int $create = 1;

    /**
     * @var int Each request consumes the number of tokens
     */
    public int $consume = 1;

    /**
     * @var int Maximum capacity of the token bucket
     */
    public int $capacity = 2;

    /**
     * @var array Callback method when rate limiting is triggered
     */
    public array $limitCallback = [];

    /**
     * @var string The key for rate limiting
     */
    public string $key = "";

    /**
     * @var int Queue timeout time
     */
    public int $waitTimeout = 1;

    /**
     * @var string|null IP
     */
    public ?string $ip = null;
}