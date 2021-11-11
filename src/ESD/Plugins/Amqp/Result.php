<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp;

/**
 * Class Result
 * @package ESD\Plugins\Amqp
 */
class Result
{
    /**
     * Acknowledge the message.
     */
    const ACK = 'ack';

    /**
     * Unacknowledge the message.
     */
    const NACK = 'nack';

    /**
     * Reject the message and requeue it.
     */
    const REQUEUE = 'requeue';

    /**
     * Reject the message and drop it.
     */
    const DROP = 'drop';
}