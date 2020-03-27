<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/5
 * Time: 11:53
 */

namespace ESD\Plugins\Validate;


use ESD\Core\Exception;
use Throwable;

class ValidationException extends Exception
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