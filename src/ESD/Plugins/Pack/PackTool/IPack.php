<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack\PackTool;

use ESD\Core\Server\Config\PortConfig;
use ESD\Plugins\Pack\ClientData;

interface IPack
{
    public function encode(string $buffer);

    public function decode(string $buffer);

    public function pack($data, PortConfig $portConfig, ?string $topic = null);

    public function unPack(int $fd, string $data, PortConfig $portConfig): ?ClientData;

    public static function changePortConfig(PortConfig $portConfig);
}