<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */
namespace ESD\Plugins\Amqp;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

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
//    protected function setupBroker()
//    {
//        if ($this->setupBrokerDone) {
//            return;
//        }
//
//        $this->handle->getExchange()->setName($this->exchangeName);
//        $this->handle->getExchange()->setType(AMQP_EX_TYPE_TOPIC);
//        $this->handle->getExchange()->setFlags(AMQP_DURABLE);
//        $this->handle->getExchange()->declareExchange();
//
//        $this->handle->getQueue()->setName($this->queueName);
//        $this->handle->getQueue()->setFlags(AMQP_DURABLE);
//        $this->handle->getQueue()->declareQueue();
//
//        $this->handle->getQueue()->bind($this->exchangeName, $this->routingKey);
//        $this->setupBrokerDone = true;
//    }

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

        $poolName = $consumerMessage->getPoolName();
        $exchange = $consumerMessage->getExchange();
        $routingKey = $consumerMessage->getRoutingKey();
        $queue = $consumerMessage->getQueue();
        $maxConsumption = $consumerMessage->getMaxConsumption();

        /** @var AMQPStreamConnection $connection */
        $connection = $this->amqp($poolName);

        try {

            $channel = $connection->getConfirmChannel();
            
        } finally {
            $connection->close();
        }



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