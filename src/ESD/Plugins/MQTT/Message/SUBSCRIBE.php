<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\MqttException;
use ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Utility;


/**
 * Message SUBSCRIBE
 * Client -> Server
 *
 * 3.8 SUBSCRIBE - Subscribe to topics
 */
class SUBSCRIBE extends Base
{
    protected $messageType = Message::SUBSCRIBE;
    protected $protocolType = self::WITH_PAYLOAD;

    protected $topics = array();

    /**
     * @param $topicFilter
     * @param $qosMax
     * @throws MqttException
     */
    public function addTopic($topicFilter, $qosMax)
    {
        Utility::CheckTopicFilter($topicFilter);
        Utility::CheckQoS($qosMax);
        $this->topics[$topicFilter] = $qosMax;
    }

    /**
     * @return array
     */
    public function getTopic()
    {
        return $this->topics;
    }

    /**
     * @return string
     * @throws MqttException
     */
    protected function payload()
    {
        if (empty($this->topics)) {
            /*
             The payload of a SUBSCRIBE packet MUST contain at least one Topic Filter / QoS pair.
             A SUBSCRIBE packet with no payload is a protocol violation [MQTT-3.8.3-3]
             */
            throw new MqttException('Missing topics!');
        }

        $buffer = "";
        # Payload
        foreach ($this->topics as $topic => $qos_max) {
            $buffer .= Utility::PackStringWithLength($topic);
            $buffer .= chr($qos_max);
        }
        return $buffer;
    }

    /**
     * @param $packetData
     * @param $payloadPos
     * @return bool|void
     */
    protected function decodePayload(&$packetData, &$payloadPos)
    {
        while (isset($packetData[$payloadPos])) {
            $topic = Utility::UnpackStringWithLength($packetData, $payloadPos);
            $qos = ord($packetData[$payloadPos]);
            $this->topics[$topic] = $qos;
            ++$payloadPos;
        }
    }
}