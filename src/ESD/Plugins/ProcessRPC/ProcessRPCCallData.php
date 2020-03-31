<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\ProcessRPC;

/**
 * Class ProcessRPCCallData
 * @package ESD\Plugins\ProcessRPC
 */
class ProcessRPCCallData
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $arguments;
    /**
     * @var int
     */
    private $token;
    /**
     * @var bool
     */
    private $oneway;

    /**
     * ProcessRPCCallData constructor.
     * @param string $className
     * @param string $name
     * @param array $arguments
     * @param bool $oneway
     */
    public function __construct(string $className, string $name, array $arguments,bool $oneway)
    {
        $this->className = $className;
        $this->name = $name;
        $this->arguments = $arguments;
        $this->token = RpcManager::getToken();
        $this->oneway = $oneway;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return int
     */
    public function getToken(): int
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isOneway(): bool
    {
        return $this->oneway;
    }
}