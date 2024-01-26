<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Message\Message;
use ESD\Core\Server\Server;

/**
 * Class MasterProcess
 * @package ESD\Core\Server\Process
 */
class MasterProcess extends Process
{
    const NAME = "master";

    const ID = "-1";

    /**
     * MasterProcess constructor.
     * @param Server $server
     * @throws \Exception
     */
    public function __construct(Server $server)
    {
        parent::__construct($server, self::ID, self::NAME, Process::SERVER_GROUP);
    }

    /**
     * inheritDoc
     * @return void
     */
    public function onProcessStart()
    {
        Process::setProcessTitle(Server::$instance->getServerConfig()->getName() . "-" . $this->getProcessName());
        $this->processPid = getmypid();
        $this->server->getProcessManager()->setCurrentProcessId($this->processId);
    }

    /**
     * On process stop
     */
    public function onProcessStop()
    {
        return;
    }


    /**
     * @inheritDoc
     *
     * @param Message $message
     * @param Process $fromProcess
     * @return void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        return;
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function init()
    {
        return;
    }
}
