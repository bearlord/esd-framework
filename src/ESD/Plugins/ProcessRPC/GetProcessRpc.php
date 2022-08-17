<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Server\Process\Process;
use ESD\Server\Coroutine\Server;

/**
 * Trait GetProcessRpc
 * @package ESD\Plugins\ProcessRPC
 */
trait GetProcessRpc
{
    /**
     * @param Process $process
     * @param string $className
     * @param bool $oneway Whether one way
     * @param float $timeOut
     * @return RPCProxy
     * @throws ProcessRPCException
     */
    public function callProcess(Process $process, string $className, bool $oneway = false, float $timeOut = 5): RPCProxy
    {
        if ($process == null) {
            throw new ProcessRPCException("The process does not exist");
        }
        return new RPCProxy($process, $className, $oneway, $timeOut);
    }

    /**
     * @param string $processName
     * @param string $className
     * @param bool $oneway
     * @param float $timeOut
     * @return RPCProxy
     * @throws ProcessRPCException
     */
    public function callProcessName(string $processName, string $className, bool $oneway = false, float $timeOut = 5): RPCProxy
    {
        $process = Server::$instance->getProcessManager()->getProcessFromName($processName);
        if ($process == null) {
            throw new ProcessRPCException("The process does not exist");
        }
        return new RPCProxy($process, $className, $oneway, $timeOut);
    }
}