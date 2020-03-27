<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 10:25
 */

namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Message\Message;

class ProcessRPCCallMessage extends Message
{
    /**
     * ProcessRPCCallMessage constructor.
     * @param string $className
     * @param string $name
     * @param array $arguments
     * @param bool $oneway
     */
    public function __construct(string $className, string $name, array $arguments, bool $oneway)
    {
        parent::__construct(RpcMessageProcessor::type, new ProcessRPCCallData($className, $name, $arguments, $oneway));
    }

    public function getProcessRPCCallData(): ProcessRPCCallData
    {
        return $this->getData();
    }
}