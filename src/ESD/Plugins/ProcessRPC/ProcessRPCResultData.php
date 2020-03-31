<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\ProcessRPC;

/**
 * Class ProcessRPCResultData
 * @package ESD\Plugins\ProcessRPC
 */
class ProcessRPCResultData
{
    /**
     * @var int
     */
    private $token;
    /**
     * @var mixed
     */
    private $result;
    /**
     * @var string
     */
    private $errorClass;
    /**
     * @var int
     */
    private $errorCode;
    /**
     * @var string
     */
    private $errorMessage;

    /**
     * ProcessRPCResultData constructor.
     * @param int $token
     * @param $result
     * @param string|null $errorClass
     * @param int|null $errorCode
     * @param string|null $errorMessage
     */
    public function __construct(int $token, $result,?string $errorClass,?int $errorCode,?string $errorMessage)
    {
        $this->token = $token;
        $this->result = $result;
        $this->errorClass = $errorClass;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return int
     */
    public function getToken(): int
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getErrorClass(): ?string
    {
        return $this->errorClass;
    }

    /**
     * @return int
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

}