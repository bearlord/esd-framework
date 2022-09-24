<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

/**
 * Class ActorMessage
 * @package ESD\Plugins\Actor
 */
class ActorMessage
{
    /**
     * @var int Message id
     */
    protected $msgId;

    /**
     * @var string From
     */
    protected $from;

    /**
     * @var string To
     */
    protected $to;

    /**
     * @var mixed Data
     */
    protected $data;

    /**
     * ActorMessage constructor.
     * @param $data
     * @param null $msgId
     * @param null $from
     */
    public function __construct($data, $msgId = null, $from = null, $to = null)
    {
        $this->data = $data;
        $this->msgId = $msgId;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getMsgId(): int
    {
        return $this->msgId;
    }

    /**
     * @param int $msgId
     */
    public function setMsgId(int $msgId): void
    {
        $this->msgId = $msgId;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * @return mixed|string|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

}