<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Message\Message;
use ESD\Core\Server\Server;

class ManagerProcess extends Process
{
    const name = "manager";
    const id = "-2";

    /**
     * ManagerProcess constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        parent::__construct($server, self::id, self::name, Process::SERVER_GROUP);
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStart()
    {
        Process::setProcessTitle(Server::$instance->getServerConfig()->getName() . "-" . $this->getProcessName());
        $this->processPid = getmypid();
        $this->server->getProcessManager()->setCurrentProcessId($this->processId);
        /*
        //block SIGINT
        pcntl_signal(SIGINT, function () {

        });*/
    }

    /**
     * @inheritDoc
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
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        return;
    }


    /**
     * @inheritDoc
     *
     * @return mixed|void
     */
    public function init()
    {
        return;
    }
}