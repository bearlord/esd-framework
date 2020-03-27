<?php

/**
 * MQTT Client
 */



namespace ESD\Plugins\MQTT\Exception;
use ESD\Plugins\MQTT\MqttException;

/**
 * Exception: Protocol Exception
 *
 * Those clients MUST close connections.
 *
 */
class Protocol extends MqttException {}


# EOF