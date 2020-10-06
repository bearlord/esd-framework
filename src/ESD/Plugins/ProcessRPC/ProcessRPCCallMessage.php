<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Message\Message;

/**
 * Class ProcessRPCCallMessage
 * @package ESD\Plugins\ProcessRPC
 */
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
        parent::__construct(RpcMessageProcessor::TYPE, new ProcessRPCCallData($className, $name, $arguments, $oneway));
    }

    /**
     * @return ProcessRPCCallData
     */
    public function getProcessRPCCallData(): ProcessRPCCallData
    {
        return $this->getData();
    }
}