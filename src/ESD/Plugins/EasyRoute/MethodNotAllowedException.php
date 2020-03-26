<?php
/**
 * Created by PhpStorm.
 * User: anythink-wx
 * Date: 2019/5/30
 * Time: 10:45
 */

namespace ESD\Plugins\EasyRoute;


use ESD\Core\Exception;
use Throwable;

class MethodNotAllowedException extends Exception
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