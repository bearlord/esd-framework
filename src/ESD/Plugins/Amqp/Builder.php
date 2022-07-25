<?php

namespace ESD\Plugins\Amqp;

use ESD\Core\Exception;
use ESD\Core\Server\Server;
use ESD\Plugins\Amqp\Message\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use function Swlib\Http\str;

class Builder
{
    /**
     * @param Message $message
     * @param AMQPChannel|null $channel
     * @param bool $release
     * @return void
     * @throws \Exception
     */
    public function declare(Message $message, ?AMQPChannel $channel = null, bool $release = false): void
    {
        try {
            if (!$channel) {
                /** @var Connection $connection */
                $connection = $this->amqp($message->getPoolName());
                $channel = $connection->getChannel();
            }

            $builder = $message->getExchangeBuilder();

            $channel->exchange_declare($builder->getExchange(), $builder->getType(), $builder->isPassive(),
                $builder->isDurable(), $builder->isAutoDelete(), $builder->isInternal(), $builder->isNowait(),
                $builder->getArguments(), $builder->getTicket());
        } catch (Exception $exception) {
            Server::$instance->getLog()->warning((string)$exception);
        }
    }
}