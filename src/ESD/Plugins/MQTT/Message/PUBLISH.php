<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\Utility;
use ESD\Plugins\MQTT\Message;

/**
 * Message PUBLISH
 * Client <-> Server
 *
 * 3.3 PUBLISH – Publish Message
 *
 * @property \ESD\Plugins\MQTT\Message\Header\PUBLISH $header
 */
class PUBLISH extends Base
{
    protected $messageType = Message::PUBLISH;
    protected $protocolType = self::WITH_PAYLOAD;

    protected $topic;
    protected $message;

    /**
     * Set Topic
     *
     * @param string $topic
     */
    public function setTopic($topic)
    {
        Utility::checkTopicName($topic);

        $this->topic = $topic;
    }

    /**
     * Get Topic
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set Message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set DUP
     *
     * @param int $dup
     */
    public function setDup($dup)
    {
        $this->header->setDup($dup);
    }

    /**
     * Get DUP
     *
     * @return int
     */
    public function getDup()
    {
        return $this->header->getDup();
    }

    /**
     * Set QoS
     *
     * @param int $qos
     */
    public function setQos($qos)
    {
        $this->header->setQos($qos);
    }

    /**
     * Get QoS
     *
     * @return int
     */
    public function getQos()
    {
        return $this->header->getQos();
    }

    /**
     * Set RETAIN
     *
     * @param int $retain
     */
    public function setRetain($retain)
    {
        $this->header->setRetain($retain);
    }

    /**
     * Get RETAIN
     *
     * @return int
     */
    public function getRetain()
    {
        return $this->header->getRetain();
    }

    /**
     * Build Payload
     *
     * @return string
     */
    protected function payload()
    {
        $buffer = "";

        # Payload
        $buffer .= $this->message;
        Debug::log(Debug::DEBUG, 'Message PUBLISH: Message='.$this->message);

        return  $buffer;
    }

    /**
     * Decode Payload
     *
     * @param string & $packetData
     * @param int    & $payloadPos
     * @return void
     */
    protected function decodePayload(& $packetData, & $payloadPos)
    {
        $this->message = substr($packetData, $payloadPos);
    }
}