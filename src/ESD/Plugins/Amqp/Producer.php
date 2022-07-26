<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Exception;
use ESD\Plugins\Amqp\Builder\Builder;
use ESD\Plugins\Amqp\Message\ProducerMessage;
use ESD\Plugins\Amqp\Message\Type;
use ESD\Plugins\AnnotationsScan\ScanClass;
use PhpAmqpLib\Message\AMQPMessage;

class Producer extends Builder
{
    use GetAmqp;

    /**
     * @inheritDoc
     * @param ProducerMessage $producerMessage
     * @param bool $confirm
     * @param int $timeout
     * @return bool
     * @throws \Throwable
     */
    public function produce(ProducerMessage $producerMessage, bool $confirm = false, int $timeout = 5): bool
    {
        return retry(1, function () use ($producerMessage, $confirm, $timeout) {
            return $this->produceMessage($producerMessage, $confirm, $timeout);
        });
    }

    /**
     * @inheritDoc
     * @param ProducerMessage $producerMessage
     * @param bool $confirm
     * @param int $timeout
     * @return bool
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    private function produceMessage(ProducerMessage $producerMessage, bool $confirm = false, int $timeout = 5)
    {
        $result = false;
        $this->injectMessageProperty($producerMessage);

        $message = new AMQPMessage($this->encode($producerMessage->payload()), $producerMessage->getProperties());

        $connection = $this->amqp();
        try {
            if ($confirm) {
                $channel = $connection->getConfirmChannel();
            } else {
                $channel = $connection->getChannel();
            }
            $channel->set_ack_handler(function () use (&$result) {
                $result = true;
            });
            $channel->basic_publish($message, $producerMessage->getExchange(), $producerMessage->getRoutingKey());
            $channel->wait_for_pending_acks_returns($timeout);
        } catch (\Throwable $exception) {
            // Reconnect the connection before release.
            $connection->reconnect();
            throw $exception;
        }

        return $confirm ? $result : true;
    }

    /**
     * @inheritDoc
     * @param ProducerMessage $producerMessage
     * @throws \Exception
     */
    private function injectMessageProperty(ProducerMessage $producerMessage)
    {
        /** @var ScanClass $scanClass */
        $scanClass = DIget(ScanClass::class);
        $annotation = $scanClass->getClassAndInterfaceAnnotation(new \ReflectionClass($producerMessage), \ESD\Plugins\Amqp\Annotation\Producer::class);
        if ($annotation) {
            $annotation->routingKey && $producerMessage->setRoutingKey($annotation->routingKey);
            $annotation->exchange && $producerMessage->setExchange($annotation->exchange);
        }
    }
}