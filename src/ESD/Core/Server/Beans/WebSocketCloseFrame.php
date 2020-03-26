<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

/**
 * Class WebSocketCloseFrame
 * @package ESD\Core\Server\Beans
 */
class WebSocketCloseFrame extends WebSocketFrame
{
    private $code;
    private $reason;

    /**
     * WebSocketCloseFrame constructor.
     * @param $frame
     */
    public function __construct($frame)
    {
        parent::__construct($frame);
        $this->code = $frame->code;
        $this->reason = $frame->reason;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }
}