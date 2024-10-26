<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Rpc\IdGenerator;

/**
 * Class UniqidIdGenerator
 */
class UniqidIdGenerator implements IdGeneratorInterface
{

    public function generate(): string
    {
        return uniqid();
    }

}
