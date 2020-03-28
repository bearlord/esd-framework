<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\PHPUnit;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Server;
use PHPUnit\TextUI\Command;

/**
 * Class PHPUnitProcess
 * @package ESD\Plugins\PHPUnit
 */
class PHPUnitProcess extends Process
{
    /**
     * @inheritDoc
     * @return mixed
     */
    public function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStart()
    {
        $command = new Command();
        try {
            $command->run(["", Server::$instance->getContainer()->get("phpunit.file")], false);
        } catch (\Throwable $e) {
        }
        \swoole_event_exit();
        $this->getSwooleProcess()->exit();
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStop()
    {
        // TODO: Implement onProcessStop() method.
    }

    /**
     * @inheritDoc
     * @param Message $message
     * @param Process $fromProcess
     * @return mixed|void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        // TODO: Implement onPipeMessage() method.
    }
}