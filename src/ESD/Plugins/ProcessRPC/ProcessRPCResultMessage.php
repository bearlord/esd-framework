<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 10:25
 */

namespace ESD\Plugins\ProcessRPC;


use ESD\Core\Message\Message;

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

    public function getProcessRPCResultData(): ProcessRPCResultData
    {
        return $this->getData();
    }
}