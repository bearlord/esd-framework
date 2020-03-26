<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack\PackTool;

use ESD\Core\Server\Config\PortConfig;
use ESD\Plugins\Pack\PackException;

abstract class AbstractPack implements IPack
{
    /**
     * @var PortConfig
     */
    protected $portConfig;

    /**
     * Get length
     *
     * c: signed, 1 bytes
     * C: unsigned, 1 bytes
     * s: signed, Host byte order, 2 bytes
     * S: unsigned, Host byte order, 2 bytes
     * n: unsigned, network byte order, 2 bytes
     * N: unsigned, network byte order, 4 bytes
     * l: signed, Host byte order, 4 bytes
     * L: unsigned, Host byte order, 4 bytes
     * v: unsigned, little-endian、2 bytes
     * V: unsigned, little-endian、4 bytes
     *
     * @param string $type
     * @return int
     * @throws PackException
     */
    protected function getLength(string $type)
    {
        switch ($type) {
            case "C":
            case "c":
                return 1;
            case "S":
            case "n":
            case "v":
            case "s":
                return 2;
            case "l":
            case "L":
            case "V":
            case "N":
                return 4;
            default:
                throw new PackException('Wrong type');
        }
    }
}