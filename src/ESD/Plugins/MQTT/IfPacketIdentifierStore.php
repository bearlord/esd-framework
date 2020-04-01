<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;


/**
 * Interface ifPacketIdentifierStore
 *
 * @package ESD\Plugins\MQTT
 */
interface IfPacketIdentifierStore
{
    /**
     * Get Current Packet Identifier
     *
     * @return int
     */
    public function get();

    /**
     * Next Packet Identifier
     *
     * @return int
     */
    public function next();

    /**
     * Set A New ID
     *
     * @param $new_id
     * @return void
     */
    public function set($new_id);
}