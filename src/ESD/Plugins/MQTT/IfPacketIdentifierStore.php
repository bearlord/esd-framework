<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/13
 * Time: 12:01
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