<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core;

use Throwable;

/**
 * Class ParamException
 * @package ESD\Core
 */
class ParamException extends Exception
{
    /**
     * ParamException constructor.
     * @param string|null $message
     * @param int|null $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = '', ?int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setTrace(false);
    }
}
