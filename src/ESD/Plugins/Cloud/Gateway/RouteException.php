<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway;

use ESD\Core\Exception;
use Throwable;

/**
 * Class RouteException
 * @package ESD\Plugins\EasyRoute
 */
class RouteException extends Exception
{
    /**
     * RouteException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setTrace(false);
    }
}