<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT;

/**
 * Class CMDStore
 *
 * @package ESD\Plugins\MQTT
 */
class CMDStore
{

    protected $commandAwaits = array();
    protected $commandAwaitsCounter = 0;

    /**
     * @param int $messageType
     * @param int $msgId
     * @return bool
     */
    public function isEmpty($messageType, $msgId = null)
    {
        if ($msgId) {
            return empty($this->commandAwaits[$messageType][$msgId]);
        } else {
            return empty($this->commandAwaits[$messageType]);
        }
    }

    /**
     * Add
     *
     * @param int $messageType
     * @param int $msgId
     * @param array $data
     */
    public function addWait($messageType, $msgId, array $data)
    {
        if (!isset($this->commandAwaits[$messageType][$msgId])) {
            Debug::Log(Debug::DEBUG, "Waiting for " . Message::$name[$messageType] . " msgid={$msgId}");

            $this->commandAwaits[$messageType][$msgId] = $data;
            ++$this->commandAwaitsCounter;
        }
    }

    /**
     * Delete
     *
     * @param int $messageType
     * @param int $msgId
     */
    public function deleteWait($messageType, $msgId)
    {
        if (isset($this->commandAwaits[$messageType][$msgId])) {
            Debug::Log(Debug::DEBUG, "Forget " . Message::$name[$messageType] . " msgid={$msgId}");

            unset($this->commandAwaits[$messageType][$msgId]);
            --$this->commandAwaitsCounter;
        }
    }

    /**
     * Get
     *
     * @param int $messageType
     * @param int $msgId
     * @return false|array
     */
    public function getWait($messageType, $msgId)
    {
        return $this->isEmpty($messageType, $msgId) ?
            false : $this->commandAwaits[$messageType][$msgId];
    }

    /**
     * Get all by message_type
     *
     * @param int $messageType
     * @return array
     */
    public function getWaits($messageType)
    {
        return $this->isEmpty($this->commandAwaits[$messageType]) ?
            false : $this->commandAwaits[$messageType];
    }

    /**
     * Count
     *
     * @return int
     */
    public function countWaits()
    {
        return $this->commandAwaitsCounter;
    }
}