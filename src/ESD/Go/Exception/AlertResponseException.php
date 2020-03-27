<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Go\Exception;

use ESD\Core\Plugins\Logger\GetLogger;

class AlertResponseException extends \Exception{

    use GetLogger;

    /**
     * AlertResponseException constructor.
     * @param null $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws \Exception
     */
    public function __construct($message = null, $code = 500, \Throwable $previous = null)
    {
        if(is_null($message)){
            $message = "内部服务器错误，请稍后再试";
        }
        $this->alert($message);
        $this->alert($this);
        return parent::__construct($message, $code, $previous);
    }
}