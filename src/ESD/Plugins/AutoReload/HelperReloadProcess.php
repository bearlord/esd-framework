<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\AutoReload;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;

/**
 * Class HelperReloadProcess
 * @package ESD\Plugins\AutoReload
 */
class HelperReloadProcess extends Process
{

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function init()
    {

    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStart()
    {

    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStop()
    {

    }

    /**
     * @inheritDoc
     * @param Message $message
     * @param Process $fromProcess
     * @return mixed|void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {

    }
}