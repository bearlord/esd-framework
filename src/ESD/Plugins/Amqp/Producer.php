<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Exception;
use ESD\Plugins\Amqp\Message\ProducerMessage;
use ESD\Plugins\Amqp\Message\Type;
use ESD\Plugins\AnnotationsScan\ScanClass;

class Producer extends Builder
{
    use GetAmqp;

    /**
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    protected function setupBroker()
    {
        if ($this->setupBrokerDone) {
            return;
        }

        $this->handle->getExchange()->setName($this->exchangeName);
        $this->handle->getExchange()->setType(AMQP_EX_TYPE_TOPIC);
        $this->handle->getExchange()->setFlags(AMQP_DURABLE);
        $this->handle->getExchange()->declareExchange();

        $this->setupBrokerDone = true;
    }

    /**
     * @param $payload
     * @param string $routingKey
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function publish($payload, string $routingKey, bool $isEncode = true): bool
    {
        $this->setupBroker();

        if ($isEncode) {
            $message = $this->encode($payload);
        } else {
            $message = $payload;
        }
        try {
            return $this->handle->getExchange()->publish($message, $routingKey, AMQP_MANDATORY, ['delivery_mode' => 2]);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
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

        $this->setExchangeName($producerMessage->getExchange());
        $this->setRoutingKey($producerMessage->getRoutingKey());
        return $this->publish($producerMessage->payload(), $producerMessage->getRoutingKey());
    }

    /**
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