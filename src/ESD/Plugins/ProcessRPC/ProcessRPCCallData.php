<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 10:59
 */

namespace ESD\Plugins\ProcessRPC;


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