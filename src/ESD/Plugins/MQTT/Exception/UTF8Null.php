<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT\Exception;
use ESD\Plugins\MQTT\MqttException;

/**
 * Exception \0
 *
 * Should be useless becauses mosquitto does not cares \0
 *
 */
class UTF8Null extends MqttException {}


# EOF