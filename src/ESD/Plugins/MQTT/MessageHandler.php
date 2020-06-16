<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT;

interface MessageHandler
{

    /**
     * @param MQTT $mqtt
     * @param Message\CONNACK $connack_object
     * @return mixed
     */
    public function connack(MQTT $mqtt, Message\CONNACK $connack_object);

    /**
     * @param MQTT $mqtt
     * @return mixed
     */
    public function disconnect(MQTT $mqtt);

    /**
     * @param MQTT $mqtt
     * @param Message\SUBACK $suback_object
     * @return mixed
     */
    public function suback(MQTT $mqtt, Message\SUBACK $suback_object);

    /**
     * @param MQTT $mqtt
     * @param Message\UNSUBACK $unsuback_object
     * @return mixed
     */
    public function unsuback(MQTT $mqtt, Message\UNSUBACK $unsuback_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PUBLISH $publish_object
     * @return mixed
     */
    public function publish(MQTT $mqtt, Message\PUBLISH $publish_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PUBACK $puback_object
     * @return mixed
     */
    public function puback(MQTT $mqtt, Message\PUBACK $puback_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PUBREC $pubrec_object
     * @return mixed
     */
    public function pubrec(MQTT $mqtt, Message\PUBREC $pubrec_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PUBREL $pubrel_object
     * @return mixed
     */
    public function pubrel(MQTT $mqtt, Message\PUBREL $pubrel_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PUBCOMP $pubcomp_object
     * @return mixed
     */
    public function pubcomp(MQTT $mqtt, Message\PUBCOMP $pubcomp_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PINGREQ $pubcomp_object
     * @return mixed
     */
    public function pingreq(MQTT $mqtt, Message\PINGREQ $pubcomp_object);

    /**
     * @param MQTT $mqtt
     * @param Message\PINGRESP $pubcomp_object
     * @return mixed
     */
    public function pingresp(MQTT $mqtt, Message\PINGRESP $pubcomp_object);

}

# EOF