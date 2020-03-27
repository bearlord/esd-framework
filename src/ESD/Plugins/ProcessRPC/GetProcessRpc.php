<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 10:15
 */

namespace ESD\Plugins\ProcessRPC;


use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Server;

trait GetProcessRpc
{
    /**
     * @param Process $process
     * @param string $className
     * @param bool $oneway 是否单向
     * @param float $timeOut
     * @return RPCProxy
     * @throws ProcessRPCException
     */
    public function callProcess(Process $process, string $className, bool $oneway = false, float $timeOut = 5): RPCProxy
    {
        if ($process == null) {
            throw new ProcessRPCException("没有该进程");
        }
        return new RPCProxy($process, $className, $oneway, $timeOut);
    }

    /**
     * @param string $processName
     * @param string $className
     * @param bool $oneway 是否单向
     * @param float $timeOut
     * @return RPCProxy
     * @throws ProcessRPCException
     */
    public function callProcessName(string $processName, string $className, bool $oneway = false, float $timeOut = 5): RPCProxy
    {
        $process = Server::$instance->getProcessManager()->getProcessFromName($processName);
        if ($process == null) {
            throw new ProcessRPCException("没有该进程");
        }
        return new RPCProxy($process, $className, $oneway, $timeOut);
    }
}