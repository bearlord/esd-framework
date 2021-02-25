<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;

use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\Exception\ConnectError;
use ESD\Plugins\MQTT\MqttException;


/**
 * Fixed Header definition for CONNACK
 */
class CONNACK extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x00;

    /**
     * CONNACK does not have Packet Identifier
     *
     * @var bool
     */
    protected $requireMsgId = false;

    /**
     * Session Present
     *
     * @var int
     */
    protected $sessionPresent = 0;

    /**
     * Connect Return code
     *
     * @var int
     */
    protected $returnCode = 0;

    /**
     * Default error definitions
     *
     * @var array
     */
    static public $connectErrors = array(
        0 => 'Connection Accepted',
        1 => 'Connection Refused: unacceptable protocol version',
        2 => 'Connection Refused: identifier rejected',
        3 => 'Connection Refused: server unavailable',
        4 => 'Connection Refused: bad user name or password',
        5 => 'Connection Refused: not authorized',
    );

    /**
     * Decode Variable Header
     *
     * @param string & $packetData
     * @param int    & $pos
     * @return bool
     * @throws MqttException
     */
    protected function decodeVariableHeader(&$packetData, &$pos)
    {
        $this->sessionPresent = ord($packetData[2]) & 0x01;

        $this->returnCode = ord($packetData[3]);

        if ($this->returnCode != 0) {
            $error = isset(self::$connectErrors[$this->returnCode]) ? self::$connectErrors[$this->returnCode] : 'Unknown error';
            Debug::log(
                Debug::ERR,
                sprintf(
                    "Connection failed! (Error: 0x%02x 0x%02x|%s)",
                    ord($packetData[2]),
                    $this->returnCode,
                    $error
                )
            );

            /*
             If a server sends a CONNACK packet containing a non-zero return code it MUST
             then close the Network Connection [MQTT-3.2.2-5]
             */
            throw new ConnectError($error);
        }

        if ($this->sessionPresent) {
            Debug::log(Debug::DEBUG, "CONNACK: Session Present Flag: ON");
        } else {
            Debug::log(Debug::DEBUG, "CONNACK: Session Present Flag: OFF");
        }
    }

    /**
     * Build Variable Header
     *
     * @return string
     */
    protected function buildVariableHeader()
    {
        $buffer = "";
        $buffer .= chr($this->sessionPresent);
        $buffer .= chr($this->returnCode);
        return $buffer;
    }

    /**
     * @param $returnCode
     */
    public function setReturnCode($returnCode)
    {
        $this->returnCode = $returnCode;
    }

    /**
     * @param $sessionPresent
     */
    public function setSessionPresent($sessionPresent)
    {
        $this->sessionPresent = $sessionPresent;
    }
}