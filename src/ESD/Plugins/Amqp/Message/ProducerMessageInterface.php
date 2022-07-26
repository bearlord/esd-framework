<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp\Message;

interface ProducerMessageInterface extends MessageInterface
{
    public function setPayload($data);

    public function payload(): string;

    public function getProperties(): array;
}
