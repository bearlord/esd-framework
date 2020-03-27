<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Go\Exception;

use ESD\Core\Plugins\Logger\GetLogger;

/**
 * Class ResponseException
 * @package ESD\Go\Exception
 */
class ResponseException extends \Exception{

    use GetLogger;

    /**
     * ResponseException constructor.
     * @param null $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws \Exception
     */
    function __construct($message = null, $code = 200, \Throwable $previous = null)
    {
        if(is_null($message)){
            $message = "请求失败，请稍后再试";
        }
        $this->warn($message);

        return parent::__construct($message, $code, $previous);
    }
}