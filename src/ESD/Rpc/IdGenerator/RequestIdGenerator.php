<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Rpc\IdGenerator;

/**
 * Class RequestIdGenerator
 */
class RequestIdGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        $us = strstr(microtime(), ' ', true);
        return strval($us * 1000 * 1000) . rand(100, 999);
    }
}