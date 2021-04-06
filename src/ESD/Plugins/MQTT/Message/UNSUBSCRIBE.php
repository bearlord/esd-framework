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
 * Message UNSUBSCRIBE
 * Client -> Server
 *
 * 3.10 UNSUBSCRIBE – Unsubscribe from topics
 */
class UNSUBSCRIBE extends Base
{
    protected $messageType = Message::UNSUBSCRIBE;

    protected $protocolType = self::WITH_PAYLOAD;

    protected $topics = array();

    public function addTopic($topicFilter)
    {
        Utility::checkTopicFilter($topicFilter);
        $this->topics[] = $topicFilter;
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
             The Topic Filters in an UNSUBSCRIBE packet MUST be UTF-8 encoded strings as
             defined in Section 1.5.3, packed contiguously [MQTT-3.10.3-1].
             */
            throw new MqttException('Missing topics!');
        }

        $buffer = "";
        # Payload
        foreach ($this->topics as $topic) {
            $buffer .= Utility::packStringWithLength($topic);
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
        $this->topics = $this->readUTF($packetData);
    }
}