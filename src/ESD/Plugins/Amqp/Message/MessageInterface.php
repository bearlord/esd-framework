<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp\Message;

use ESD\Plugins\Amqp\Builder\ExchangeBuilder;

interface MessageInterface
{
    public function getPoolName(): string;

    public function setType(string $type);

    public function getType(): string;

    public function setExchange(string $exchange);

    public function getExchange(): string;

    public function setRoutingKey($routingKey);

    public function getRoutingKey();

    public function getExchangeBuilder(): ExchangeBuilder;

    public function serialize(): string;
    
    public function unserialize(string $data);
}
