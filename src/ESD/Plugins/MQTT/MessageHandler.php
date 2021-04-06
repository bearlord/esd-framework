<?php

/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;

/**
 * Interface MessageHandler
 * @package ESD\Plugins\MQTT
 */
interface MessageHandler
{

    public function connack(MQTT $mqtt, Message\CONNACK $connackObject);

    public function disconnect(MQTT $mqtt);

    public function suback(MQTT $mqtt, Message\SUBACK $subackObject);

    public function unsuback(MQTT $mqtt, Message\UNSUBACK $unsubackObject);

    public function publish(MQTT $mqtt, Message\PUBLISH $publishObject);

    public function puback(MQTT $mqtt, Message\PUBACK $pubackObject);

    public function pubrec(MQTT $mqtt, Message\PUBREC $pubrecObject);

    public function pubrel(MQTT $mqtt, Message\PUBREL $pubrelObject);

    public function pubcomp(MQTT $mqtt, Message\PUBCOMP $pubcompObject);

    public function pingreq(MQTT $mqtt, Message\PINGREQ $pubcompObject);

    public function pingresp(MQTT $mqtt, Message\PINGRESP $pubcompObject);

}