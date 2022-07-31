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
     * Message id
     * @var int
     */
    protected $msgId;

    /**
     * From
     * @var string
     */
    protected $from;

    /**
     * Data
     * @var mixed
     */
    protected $data;

    /**
     * ActorMessage constructor.
     * @param $data
     * @param null $msgId
     * @param null $from
     */
    public function __construct($data, $msgId = null, $from = null)
    {
        $this->msgId = $msgId;
        $this->from = $from;
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
}