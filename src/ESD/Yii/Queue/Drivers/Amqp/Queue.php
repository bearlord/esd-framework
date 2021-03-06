<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Drivers\Amqp;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use AMQPEnvelope;
use ESD\Plugins\Amqp\GetAmqp;
use ESD\Yii\Base\Event;
use ESD\Yii\Base\NotSupportedException;
use ESD\Yii\Queue\Cli\Queue as CliQueue;


/**
 * Amqp Queue.
 *
 * @author Maksym Kotliar <kotlyar.maksim@gmail.com>
 * @since 2.0.2
 */
class Queue extends CliQueue
{
    use GetAmqp;

    const ATTEMPT = 'yii-attempt';
    const TTR = 'yii-ttr';
    const DELAY = 'yii-delay';
    const PRIORITY = 'yii-priority';

    const AMQP_X_DELAY = 'x-delay';
    const AMQP_X_DELAYED_MESSAGE = 'x-delayed-message';
    const AMQP_X_DELAYED_TYPE = 'x-delayed-type';

    public $name = 'default';

    /**
     * The connection to the borker could be configured as an array of options
     * or as a DSN string like amqp:, amqps:, amqps://user:pass@localhost:1000/vhost.
     *
     * @var string
     */
    public $dsn;

    /**
     * The message queue broker's host.
     *
     * @var string|null
     */
    public $host;

    /**
     * The message queue broker's port.
     *
     * @var string|null
     */
    public $port;

    /**
     * This is RabbitMQ user which is used to login on the broker.
     *
     * @var string|null
     */
    public $user;

    /**
     * This is RabbitMQ password which is used to login on the broker.
     *
     * @var string|null
     */
    public $password;

    /**
     * Virtual hosts provide logical grouping and separation of resources.
     *
     * @var string|null
     */
    public $vhost;

    /**
     * The time PHP socket waits for an information while reading. In seconds.
     *
     * @var float|null
     */
    public $readTimeout;

    /**
     * The time PHP socket waits for an information while witting. In seconds.
     *
     * @var float|null
     */
    public $writeTimeout;

    /**
     * The time RabbitMQ keeps the connection on idle. In seconds.
     *
     * @var float|null
     */
    public $connectionTimeout;

    /**
     * The periods of time PHP pings the broker in order to prolong the connection timeout. In seconds.
     *
     * @var float|null
     */
    public $heartbeat;

    /**
     * PHP uses one shared connection if set true.
     *
     * @var bool|null
     */
    public $persisted;

    /**
     * The connection will be established as later as possible if set true.
     *
     * @var bool|null
     */
    public $lazy;

    /**
     * If false prefetch_count option applied separately to each new consumer on the channel
     * If true prefetch_count option shared across all consumers on the channel.
     *
     * @var bool|null
     */
    public $qosGlobal;

    /**
     * Defines number of message pre-fetched in advance on a channel basis.
     *
     * @var int|null
     */
    public $qosPrefetchSize;

    /**
     * Defines number of message pre-fetched in advance per consumer.
     *
     * @var int|null
     */
    public $qosPrefetchCount;

    /**
     * Defines whether secure connection should be used or not.
     *
     * @var bool|null
     */
    public $sslOn;

    /**
     * Require verification of SSL certificate used.
     *
     * @var bool|null
     */
    public $sslVerify;

    /**
     * Location of Certificate Authority file on local filesystem which should be used with the verify_peer context option to authenticate the identity of the remote peer.
     *
     * @var string|null
     */
    public $sslCacert;

    /**
     * Path to local certificate file on filesystem.
     *
     * @var string|null
     */
    public $sslCert;

    /**
     * Path to local private key file on filesystem in case of separate files for certificate (local_cert) and private key.
     *
     * @var string|null
     */
    public $sslKey;

    /**
     * The queue used to consume messages from.
     *
     * @var string
     */
    public $queueName = 'yii-queue';

    /**
     * The exchange used to publish messages to.
     *
     * @var string
     */
    public $exchangeName = 'yii-exchange';

    /**
     * The routing key
     *
     * @var string
     */
    public $routingKey = 'yii-routing-key';

    /**
     * This property should be an integer indicating the maximum priority the queue should support. Default is 10.
     *
     * @var int
     */
    public $maxPriority = 10;
    
    /**
     * @var Context;
     */
    protected $context;

    /**
     * The property tells whether the setupBroker method was called or not.
     * Having it we can do broker setup only once per process.
     *
     * @var bool
     */
    protected $setupBrokerDone = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->context = $this->amqp();
    }

    /**
     * Listens amqp-queue and runs new jobs.
     * 
     * @param int $timeout
     * @return int|null
     */
    public function listen($timeout = 3)
    {
        if (!is_numeric($timeout)) {
            throw new Exception('Timeout must be numeric.');
        }
        if ($timeout < 1) {
            throw new Exception('Timeout must be greater than zero.');
        }

        return $this->run(true, $timeout);
    }

    /**
     * Listens queue and runs each job.
     *
     * @param bool $repeat whether to continue listening when queue is empty.
     * @param int $timeout number of seconds to wait for next message.
     * @return null|int exit code.
     * @internal for worker command only.
     * @since 2.0.2
     */
    public function run($repeat, $timeout = 0)
    {
        $this->setupBroker();
        
        return $this->runWorker(function (callable $canContinue) use ($repeat, $timeout) {
            while ($canContinue()) {
                $callback = function (AMQPEnvelope $message, AMQPQueue $q) use (&$max_consume) {
                    if ($message->isRedelivery()) {
                        $q->ack($message->getDeliveryTag());
                    }

                    $ttr = $attempt = null;
                    if ($this->handleMessage($message->getMessageId(), $message->getBody(), $ttr, $attempt)) {
                        $q->ack($message->getDeliveryTag());
                    } else {
                        $q->ack($message->getDeliveryTag());
                        $this->redeliver($message);
                    }
                };

                $this->context->getQueue()->consume($callback);
                return true;
            }
        });
    }


    /**
     * @return AmqpContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    protected function pushMessage($payload, $ttr, $delay, $priority)
    {
        $this->setupBroker();

        $messageId = uniqid('', true);

        $attributes = [
            'message_id' => $messageId,
            'timestamp' => time()
        ];
        if ($delay) {
            $attributes['headers'][self::AMQP_X_DELAY] = $delay * 1000;
        }
        if ($priority) {
            $attributes['headers'][self::PRIORITY] = $priority;
        }
        $this->context->getExchange()->publish($payload, $this->routingKey, AMQP_DURABLE, $attributes);
        return $messageId;
    }

    /**
     * @inheritdoc
     */
    public function status($id)
    {
        throw new NotSupportedException('Status is not supported in the driver.');
    }

    protected function setupBroker()
    {
        if ($this->setupBrokerDone) {
            return;
        }

        $this->context->getExchange()->setName($this->exchangeName);
        $this->context->getExchange()->setType(self::AMQP_X_DELAYED_MESSAGE);
        $this->context->getExchange()->setArgument(self::AMQP_X_DELAYED_TYPE, AMQP_EX_TYPE_DIRECT);
        $this->context->getExchange()->declareExchange();

        $this->context->getQueue()->setName($this->queueName);
        $this->context->getQueue()->setFlags(AMQP_DURABLE);
        $this->context->getQueue()->setArgument('x-max-priority', $this->maxPriority);
        $this->context->getQueue()->declareQueue();

        $this->context->getQueue()->bind($this->exchangeName, $this->routingKey);
        $this->setupBrokerDone = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function redeliver(AmqpMessage $message)
    {
        return true;
    }
}
