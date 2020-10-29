<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Go;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Class GoProcess
 * @package ESD\Go
 */
class GoProcess extends Process
{

    /**
     * @inheritDoc
     * @return mixed
     * @throws \Exception
     */
    public function init()
    {
        $this->log = Server::$instance->getLog();
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStart()
    {
        $this->log->info(Yii::t('esd', 'Process start'));
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStop()
    {
        $this->log->info(Yii::t('esd', 'Process stop'));
    }

    /**
     * @inheritDoc
     * @param Message $message
     * @param Process $fromProcess
     * @return mixed|void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        return;
    }
}