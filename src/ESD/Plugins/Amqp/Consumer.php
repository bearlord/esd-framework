<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Exception;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Coroutine\Concurrent;
use ESD\Plugins\Amqp\Builder\Builder;
use ESD\Plugins\Amqp\Message\ConsumerMessage;
use ESD\Plugins\Amqp\Message\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class consumer
 */
class Consumer extends Builder
{
    use GetAmqp;
    use GetLogger;
    
    /**
     * @inheritDoc
     * @param ConsumerMessage $consumerMessage
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPEnvelopeException
     * @throws \AMQPExchangeException
     */
    public function consume(ConsumerMessage $consumerMessage): void
    {
        $poolName = $consumerMessage->getPoolName();
        $exchange = $consumerMessage->getExchange();
        $routingKey = $consumerMessage->getRoutingKey();
        $queue = $consumerMessage->getQueue();
        $maxConsumption = $consumerMessage->getMaxConsumption();

        $connection = $this->amqp($poolName);

        try {
            $channel = $connection->getConfirmChannel();

            $this->declare($consumerMessage, $channel);
            $concurrent = $this->getConcurrent($consumerMessage->getPoolName());

            $maxConsumption = $consumerMessage->getMaxConsumption();
            $currentConsumption = 0;

            $channel->basic_consume(
                $consumerMessage->getQueue(),
                $consumerMessage->getConsumerTag(),
                false,
                false,
                false,
                false,
                function (AMQPMessage $message) use ($consumerMessage, $concurrent) {
                    $callback = $this->getCallback($consumerMessage, $message);
                    if (!$concurrent instanceof Concurrent) {
                        return parallel([$callback]);
                    }

                    $concurrent->create($callback);
                }
            );

            while ($channel->is_consuming()) {
                try {
                    $channel->wait(null, false, $consumerMessage->getWaitTimeout());
                    if ($maxConsumption > 0 && ++$currentConsumption >= $maxConsumption) {
                        break;
                    }
                } catch (\Throwable $exception) {
                    Server::$instance->getLog()->error($exception);
                    break;
                }
            }

            $this->waitConcurrentHandled($concurrent);
        } catch (\Exception $exception) {
            $connection->close();
            throw $exception;
        }
    }

    /**
     * @inheritDoc
     * @param ConsumerMessage $message
     * @param AMQPChannel|null $channel
     * @param bool $release
     * @return void
     * @throws \Exception
     */
    public function declare(ConsumerMessage $message, ?AMQPChannel $channel = null, bool $release = false): void
    {
        if (!$message instanceof ConsumerMessage) {
            throw new MessageException('Message must instanceof ' . Message::class);
        }

        try {
            if (!$channel) {
                /** @var Connection $connection */
                $connection = $this->amqp($message->getPoolName());
                $channel = $connection->getChannel();
            }

            parent::declare($message, $channel);

            $builder = $message->getQueueBuilder();
            $channel->queue_declare($builder->getQueue(), $builder->isPassive(), $builder->isDurable(), $builder->isExclusive(), $builder->isAutoDelete(), $builder->isNowait(), $builder->getArguments(), $builder->getTicket());

            $routineKeys = (array)$message->getRoutingKey();
            foreach ($routineKeys as $routingKey) {
                $channel->queue_bind($message->getQueue(), $message->getExchange(), $routingKey);
            }

            if (empty($routineKeys) && $message->getType() === Type::FANOUT) {
                $channel->queue_bind($message->getQueue(), $message->getExchange());
            }

            if (is_array($qos = $message->getQos())) {
                $size = $qos['prefetch_size'] ?? null;
                $count = $qos['prefetch_count'] ?? null;
                $global = $qos['global'] ?? null;
                $channel->basic_qos($size, $count, $global);
            }
        } catch (Exception $exception) {
            Server::$instance->getLog()->error($exception);
        }
    }

    /**
     * Wait the tasks in concurrent handled, the max wait time is 5s.
     * @param int $interval The wait interval ms
     * @param int $count The wait count
     */
    protected function waitConcurrentHandled(?Concurrent $concurrent, int $interval = 10, int $count = 500): void
    {
        $index = 0;
        while ($concurrent && !$concurrent->isEmpty()) {
            usleep($interval * 1000);
            if ($index++ > $count) {
                break;
            }
        }
    }

    /**
     * @inheritDoc
     * @param string $pool
     * @return Concurrent|null
     * @throws \Exception
     */
    protected function getConcurrent(string $pool): ?Concurrent
    {
        $concurrent = (int)Server::$instance->getConfigContext()->get("amqp.{$pool}.concurrent.limit", 0);
        if ($concurrent > 1) {
            return new Concurrent($concurrent);
        }

        return null;
    }

    /**
     * @inheritDoc
     * @param ConsumerMessage $consumerMessage
     * @param AMQPMessage $message
     * @return \Closure
     */
    protected function getCallback(ConsumerMessage $consumerMessage, AMQPMessage $message)
    {
        return function () use ($consumerMessage, $message) {
            $data = $consumerMessage->unserialize($message->getBody());
            /** @var AMQPChannel $channel */
            $channel = $message->delivery_info['channel'];
            $deliveryTag = $message->delivery_info['delivery_tag'];
            
            try {
                $result = $consumerMessage->consumeMessage($data, $message);
            } catch (\Throwable $exception) {
                Server::$instance->getLog()->error($exception);
                $result = Result::DROP;
            }

            if ($result === Result::ACK) {
                Server::$instance->getLog()->debug($deliveryTag . ' acked.');
                return $channel->basic_ack($deliveryTag);
            }
            if ($result === Result::NACK) {
                Server::$instance->getLog()->debug($deliveryTag . ' uacked.');
                return $channel->basic_nack($deliveryTag);
            }
            if ($consumerMessage->isRequeue() && $result === Result::REQUEUE) {
                Server::$instance->getLog()->debug($deliveryTag . ' requeued.');
                return $channel->basic_reject($deliveryTag, true);
            }

            Server::$instance->getLog()->debug($deliveryTag . ' rejected.');
            return $channel->basic_reject($deliveryTag, false);
        };
    }
}