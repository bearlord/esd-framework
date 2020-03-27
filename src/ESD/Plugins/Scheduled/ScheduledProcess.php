<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 13:50
 */

namespace ESD\Plugins\Scheduled;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;

class ScheduledProcess extends Process
{

    /**
     * 在onProcessStart之前，用于初始化成员变量
     * @return mixed
     */
    public function init()
    {

    }

    public function onProcessStart()
    {

    }

    public function onProcessStop()
    {

    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {

    }
}