<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\PHPUnit;

use ESD\Server\Coroutine\Server;

/**
 * Class UnitServer
 * @package ESD\Plugins\PHPUnit
 */
class UnitServer
{
    public $worker_id = 0;
    public $worker_pid = 0;
    public $master_pid = 0;

    /**
     * UnitServer constructor.
     */
    public function __construct()
    {
        $this->master_pid = getmypid();
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {

    }

    /**
     * @param $name
     */
    public function __get($name)
    {

    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {

    }

    /**
     * Start
     */
    public function start()
    {
        $process = Server::$instance->getProcessManager()->getProcessFromName(PHPUnitPlugin::processName);
        $this->worker_id = $process->getProcessId();
        enableRuntimeCoroutine(true);
        $this->worker_pid = $process->getSwooleProcess()->start();
    }
}