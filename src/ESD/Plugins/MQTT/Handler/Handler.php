<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Handler;

/**
 * Interface Handler
 * @package ESD\Plugins\MQTT\Handler
 */
interface Handler
{
    /**
     * @param $data
     * @return string
     */
    public function pack($data): string;

    /**
     * @param $data
     * @return mixed
     */
    public function upPack($data);
}