<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Message\Message;

/**
 * Class ProcessRPCResultMessage
 * @package ESD\Plugins\ProcessRPC
 */
class ProcessRPCResultMessage extends Message
{
    /**
     * ProcessRPCCallMessage constructor.
     * @param int $token
     * @param $result
     * @param string|null $errorClass
     * @param int|null $errorCode
     * @param string|null $errorMessage
     */
    public function __construct(int $token, $result, ?string $errorClass, ?int $errorCode, ?string $errorMessage)
    {
        parent::__construct(RpcMessageProcessor::type, new ProcessRPCResultData($token, $result, $errorClass, $errorCode, $errorMessage));
    }

    /**
     * @return ProcessRPCResultData
     */
    public function getProcessRPCResultData(): ProcessRPCResultData
    {
        return $this->getData();
    }
}