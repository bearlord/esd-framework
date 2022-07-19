<?php

namespace ESD\Plugins\Amqp\Message;

interface ProducerMessageInterface extends MessageInterface
{
    public function setPayload($data);

    public function payload(): string;

    public function getProperties(): array;
}
