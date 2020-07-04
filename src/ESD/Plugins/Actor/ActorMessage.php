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
    protected $form;

    /**
     * Data
     * @var mixed
     */
    protected $data;

    /**
     * ActorMessage constructor.
     * @param $data
     * @param null $msgId
     * @param null $form
     */
    public function __construct($data, $msgId = null, $form = null)
    {
        $this->msgId = $msgId;
        $this->form = $form;
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
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * @param string $form
     */
    public function setForm(string $form): void
    {
        $this->form = $form;
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