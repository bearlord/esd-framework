<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Channel\Channel;

/**
 * Class RpcManager
 * @package ESD\Plugins\ProcessRPC
 */
class RpcManager
{
    private static $token = 0;

    /**
     * @var Channel[];
     */
    private static $channelMap = [];

    public static function getToken()
    {
        return self::$token++;
    }

    /**
     * @param $token
     * @return Channel
     * @throws \Exception
     */
    public static function getChannel($token): Channel
    {
        self::$channelMap[$token] = DIGet(Channel::class);
        return self::$channelMap[$token];
    }

    /**
     * @param $token
     * @param $data
     */
    public static function callChannel($token, $data)
    {
        $channel = self::$channelMap[$token] ?? null;
        if ($channel != null) {
            $channel->push($data);
            unset(self::$channelMap[$token]);
        }
    }
}