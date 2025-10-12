<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;


class ActorMessage
{
    const TYPE_COMMON = 'common';

    const TYPE_MULTICAST = 'multicast';

    /**
     * @var string|null Message id
     */
    protected ?string $msgId = null;

    /**
     * @var string|null From
     */
    protected ?string $from = null;

    /**
     * @var string|null To
     */
    protected ?string $to = null;

    /**
     * @var mixed Data
     */
    protected $data;

    /**
     * @var string|null
     */
    protected ?string $type = self::TYPE_COMMON;

    /**
     * @param $data
     * @param string|null $type
     * @param string|null $msgId
     * @param string|null $from
     * @param string|null $to
     */
    public function __construct($data, ?string $type = null, ?string $msgId = null, ?string $from = null, ?string $to = null)
    {
        $this->data = $data;

        $this->msgId = $msgId;

        $this->from = $from;

        $this->to = $to;

        $this->setType($type);
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
     * @return string|null
     */
    public function getMsgId(): ?string
    {
        return $this->msgId;
    }

    /**
     * @param int|null $msgId
     */
    public function setMsgId(?int $msgId = null): void
    {
        $this->msgId = $msgId;
    }

    /**
     * @return string
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @param string|null $from
     */
    public function setFrom(?string $from = null): void
    {
        $this->from = $from;
    }

    /**
     * @return string|null
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * @param string|null $to
     */
    public function setTo(?string $to = null): void
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return void
     */
    public function setType(?string $type = null): void
    {
        if (in_array($type, [self::TYPE_COMMON, self::TYPE_MULTICAST])) {
            $this->type = $type;
            return;
        }

        $this->type = self::TYPE_COMMON;
    }
}
