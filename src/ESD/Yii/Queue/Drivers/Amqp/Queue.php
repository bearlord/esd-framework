<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Drivers\Amqp;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use ESD\Plugins\Amqp\GetAmqp;
use ESD\Plugins\Amqp\Handle;
use ESD\Yii\Base\Event;
use ESD\Yii\Base\NotSupportedException;
use ESD\Yii\Queue\Cli\Queue as CliQueue;
use Swoole\Coroutine\Channel;


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

    /**
     * @var AbstractConnection
     */
    protected $connection;
    /**
     * @var AMQPChannel
     */
    protected $channel;

    public $queueName = 'yii-queue';
    public $exchangeName = 'yii-exchange';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Listens amqp-queue and runs new jobs.
     */
    public function listen()
    {
        goWithContext(function () {
            $connection = $this->amqp()->getConnection();
            $channel = $connection->channel();
            $channel->queue_declare($this->queueName, false, true, false, false);
            $channel->exchange_declare($this->exchangeName, 'direct', false, true, false);
            $channel->queue_bind($this->queueName, $this->exchangeName);

            $callback = function (AMQPMessage $payload) {
                $id = $payload->get('message_id');
                list($ttr, $message) = explode(';', $payload->body, 2);
                if ($this->handleMessage($id, $message, $ttr, 1)) {
                    $payload->delivery_info['channel']->basic_ack($payload->delivery_info['delivery_tag']);
                }
            };
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume($this->queueName, '', false, false, false, false, $callback);
            while (count($channel->callbacks)) {
                $channel->wait();
            }
        });
    }

    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        $chan = new Channel(1);
        goWithContext(function () use ($message, $ttr, $delay, $priority, $chan) {
            $connection = $this->amqp()->getConnection();
            $channel = $connection->channel();
            $channel->queue_declare($this->queueName, false, true, false, false);
            $channel->exchange_declare($this->exchangeName, 'direct', false, true, false);
            $channel->queue_bind($this->queueName, $this->exchangeName);

            if ($delay) {
                throw new NotSupportedException('Delayed work is not supported in the driver.');
            }
            if ($priority !== null) {
                throw new NotSupportedException('Job priority is not supported in the driver.');
            }

            $id = uniqid('', true);
            $channel->basic_publish(
                new AMQPMessage("$ttr;$message", [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'message_id' => $id,
                ]),
                $this->exchangeName
            );
            $chan->push($id);
        });
        return $chan->pop();
    }

    /**
     * @inheritdoc
     */
    public function status($id)
    {
        throw new NotSupportedException('Status is not supported in the driver.');
    }
}
