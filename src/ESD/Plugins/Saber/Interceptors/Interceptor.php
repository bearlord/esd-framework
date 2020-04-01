<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber\Interceptors;

/**
 * Class Interceptor
 * @package ESD\Plugins\Saber
 */
abstract class Interceptor
{
    const BEFORE = "before";
    const AFTER = "after";
    const RETRY = "retry";
    const BEFORE_REDIRECT = "before_redirect";

    /**
     * @var string
     */
    private $type;

    /**
     * Interceptor constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;
}