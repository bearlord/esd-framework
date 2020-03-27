<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT;

/**
 * Packet Identifier Generator
 *
 * @package ESD\Plugins\MQTT
 */
class PacketIdentifier
{
    /**
     * @var ifPacketIdentifierStore
     */
    protected $pi;

    public function __construct()
    {
        $this->pi = new PacketIdentifierStore_Static();
    }

    /**
     * Next Packet Identifier
     *
     * @return int
     */
    public function next()
    {
        return $this->pi->next() % 65535 + 1;
    }

    /**
     * Current Packet Identifier
     *
     * @return mixed
     */
    public function get()
    {
        return $this->pi->get() % 65535 + 1;
    }

    /**
     * Set A New ID
     *
     * @param int $new_id
     * @return void
     */
    public function set($new_id)
    {
        $this->pi->set($new_id);
    }
}

# EOF