<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */
namespace ESD\Plugins\Amqp;

use ESD\Plugins\Amqp\Message\ConsumerMessage;

/**
 * Class consumer
 */
class Consumer extends Builder
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
        $this->handle->getExchange()->setType(AMQP_EX_TYPE_DIRECT);
        $this->handle->getExchange()->setFlags(AMQP_DURABLE);
        $this->handle->getExchange()->declareExchange();

        $this->handle->getQueue()->setName($this->queueName);
        $this->handle->getQueue()->setFlags(AMQP_DURABLE);
        $this->handle->getQueue()->declareQueue();

        $this->handle->getQueue()->bind($this->exchangeName, $this->routingKey);
        $this->setupBrokerDone = true;
    }

    public function consume(ConsumerMessage $consumerMessage): void
    {
        $maxConsumption = $consumerMessage->getMaxConsumption();
        $callback = function (\AMQPEnvelope $message, \AMQPQueue $q) use ($consumerMessage, &$maxConsumption) {
            if ($message->isRedelivery()) {
                $q->ack($message->getDeliveryTag());
            }

            $ttr = $attempt = null;
            if ($consumerMessage->consume($message->getBody())) {
                $q->ack($message->getDeliveryTag());
            } else {
                $q->ack($message->getDeliveryTag());
                $this->redeliver($message);
            }
        };

        $this->setupBroker();

        goWithContext(function () use ($callback){
            $this->handle->getQueue()->consume($callback);
        });
    }
}