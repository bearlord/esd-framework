<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Message\Message;
use ESD\Core\Server\Server;

/**
 * Class ManagerProcess
 * @package ESD\Core\Server\Process
 */
class ManagerProcess extends Process
{
    const NAME = "manager";

    const ID = "-2";

    /**
     * ManagerProcess constructor.
     * @param Server $server
     * @throws \Exception
     */
    public function __construct(Server $server)
    {
        parent::__construct($server, self::ID, self::NAME, Process::SERVER_GROUP);
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function onProcessStart()
    {
        Process::setProcessTitle(Server::$instance->getServerConfig()->getName() . "-" . $this->getProcessName());
        $this->processPid = getmypid();
        $this->server->getProcessManager()->setCurrentProcessId($this->processId);
    }

    /**
     * @inheritDoc
     */
    public function onProcessStop()
    {
    }

    /**
     * @inheritDoc
     *
     * @param Message $message
     * @param Process $fromProcess
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {
    }


    /**
     * @inheritDoc
     *
     */
    public function init()
    {
    }
}
