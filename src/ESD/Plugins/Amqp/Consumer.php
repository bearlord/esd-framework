<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */
namespace ESD\Plugins\Amqp;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Amqp\Message\ConsumerMessage;

/**
 * Class consumer
 */
class Consumer extends Builder
{
    use GetAmqp;
    use GetLogger;

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

    /**
     * @param ConsumerMessage $consumerMessage
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPEnvelopeException
     * @throws \AMQPExchangeException
     */
    public function consume(ConsumerMessage $consumerMessage): void
    {
        $maxConsumption = $consumerMessage->getMaxConsumption();
        $callback = function (\AMQPEnvelope $message, \AMQPQueue $q) use ($consumerMessage, &$maxConsumption) {
            if ($message->isRedelivery()) {
                $q->ack($message->getDeliveryTag());
            }

            $ttr = $attempt = null;
            $result = $consumerMessage->consume($message->getBody());
            if ($result == Result::ACK) {
                $this->debug($deliveryTag . ' acked.');
                return $q->ack($message->getDeliveryTag());
            }

            if ($result === Result::NACK) {
                $this->debug($deliveryTag . ' uacked.');
                return $q->nack($message->getDeliveryTag());
            }

            if ($consumerMessage->isRequeue() && $result === Result::REQUEUE) {
                $this->debug($deliveryTag . ' requeued.');
                return $q->reject($message->getDeliveryTag(), AMQP_REQUEUE);
            }

            $this->debug($deliveryTag . ' requeued.');
            return $q->reject($message->getDeliveryTag());
        };

        $this->setupBroker();

        goWithContext(function () use ($callback){
            $this->handle->getQueue()->consume($callback);
        });
    }
}