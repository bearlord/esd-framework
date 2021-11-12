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
     * @param ConsumerMessage $consumerMessage
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPEnvelopeException
     * @throws \AMQPExchangeException
     */
    public function consume(ConsumerMessage $consumerMessage): void
    {
        $this->setExchangeName($consumerMessage->getExchange());
        $this->setRoutingKey($consumerMessage->getRoutingKey());
        $this->setQueueName($consumerMessage->getQueue());
        $this->setupBroker();

        $maxConsumption = $consumerMessage->getMaxConsumption();
        $callback = function (\AMQPEnvelope $message, \AMQPQueue $q) use ($consumerMessage, &$maxConsumption) {
            $deliveryTag = $message->getDeliveryTag();
            if ($message->isRedelivery()) {
                $q->ack($deliveryTag);
            }

            $ttr = $attempt = null;
            $result = $consumerMessage->consume($message->getBody());
            if ($result == Result::ACK) {
                $this->debug($deliveryTag . ' acked.');
                return $q->ack($deliveryTag);
            }

            if ($result === Result::NACK) {
                $this->debug($deliveryTag . ' uacked.');
                return $q->nack($deliveryTag);
            }

            if ($consumerMessage->isRequeue() && $result === Result::REQUEUE) {
                $this->debug($deliveryTag . ' requeued.');
                return $q->reject($deliveryTag, AMQP_REQUEUE);
            }

            $this->debug($deliveryTag . ' requeued.');
            return $q->reject($deliveryTag);
        };

        goWithContext(function () use ($callback){
            $this->handle->getQueue()->consume($callback);
        });
    }
}