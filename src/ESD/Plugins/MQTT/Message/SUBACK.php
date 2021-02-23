<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message SUBACK
 * Client <- Server
 *
 * 3.9 SUBACK – Subscribe acknowledgement
 */
class SUBACK extends Base
{
    protected $messageType = Message::SUBACK;
    protected $protocolType = self::WITH_VARIABLE;
    protected $readBytes = 4;

    /**
     * Return Codes from SUBACK Payload
     *
     * @var array
     */
    protected $returnCodes = array();

    /**
     * Get return codes
     *
     * @return array
     */
    public function getReturnCodes()
    {
        return $this->returnCodes;
    }

    public function setReturnCodes($codes)
    {
        $this->returnCodes = $codes;
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
        $returnCode = array();

        while (isset($packetData[$payloadPos])) {
            $returnCode[] = ord($packetData[$payloadPos]);

            ++ $payloadPos;
        }

        $this->returnCodes = $returnCode;
    }

    protected function payload()
    {
        $buffer = "";

        # Payload
        foreach ($this->returnCodes as $qos_max) {
            $buffer .= chr($qos_max);
        }

        return $buffer;
    }
}