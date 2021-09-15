<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Protocol;

/**
 * Class Types
 * @package ESD\Plugins\MQTT\Protocol
 */
class Types
{
    /**
     * Client request to connect to Server
     */
    public const CONNECT = 1;

    /**
     * Connect acknowledgment
     */
    public const CONNACK = 2;

    /**
     * Publish message
     */
    public const PUBLISH = 3;

    /**
     * Publish acknowledgment
     */
    public const PUBACK = 4;

    /**
     * Publish received (assured delivery part 1)
     */
    public const PUBREC = 5;

    /**
     * Publish release (assured delivery part 2)
     */
    public const PUBREL = 6;

    /**
     * Publish complete (assured delivery part 3)
     */
    public const PUBCOMP = 7;

    /**
     * Client subscribe request
     */
    public const SUBSCRIBE = 8;

    /**
     * Subscribe acknowledgment
     */
    public const SUBACK = 9;

    /**
     * Unsubscribe request
     */
    public const UNSUBSCRIBE = 10;

    /**
     * Unsubscribe acknowledgment
     */
    public const UNSUBACK = 11;

    /**
     * PING request
     */
    public const PINGREQ = 12;

    /**
     * PING response
     */
    public const PINGRESP = 13;

    /**
     * Client is disconnecting
     */
    public const DISCONNECT = 14;

    /**
     * Authentication exchange
     */
    public const AUTH = 15;

    /**
     * @var string[]
     */
    protected static $types = [
        self::CONNECT => 'connect',
        self::CONNACK => 'connack',
        self::PUBLISH => 'publish',
        self::PUBACK => 'puback',
        self::PUBREC => 'pubrec',
        self::PUBREL => 'pubrel',
        self::PUBCOMP => 'pubcomp',
        self::SUBSCRIBE => 'subscribe',
        self::SUBACK => 'suback',
        self::UNSUBSCRIBE => 'unsubscribe',
        self::PINGREQ => 'pingreq',
        self::PINGRESP => 'pingresp',
        self::DISCONNECT => 'disconnect',
        self::AUTH => 'auth',
    ];

    /**
     * @return string[]
     */
    public static function getTypes(): array
    {
        return static::$types;
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getType(int $type): string
    {
        return static::$types[$type] ?? '';
    }
}
