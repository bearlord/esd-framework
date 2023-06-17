<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Rpc\IdGenerator;

/**
 * Interface IdGeneratorInterface
 */
interface IdGeneratorInterface
{
    /**
     * @return string
     */
    public function generate(): string;
}