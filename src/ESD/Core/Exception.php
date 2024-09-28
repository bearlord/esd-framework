<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core;

use Throwable;

/**
 * Class Exception
 * @package ESD\Core
 */
class Exception extends \Exception
{
    protected $trace = true;

    protected $time;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, int $code = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->time = (int)(microtime(true) * 1000 * 1000);
    }

    /**
     * @return bool
     */
    public function isTrace(): bool
    {
        return $this->trace;
    }

    /**
     * @param bool $trace
     */
    public function setTrace(bool $trace): void
    {
        $this->trace = $trace;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }
}