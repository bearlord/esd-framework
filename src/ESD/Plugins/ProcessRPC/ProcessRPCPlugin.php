<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/9
 * Time: 9:27
 */

namespace ESD\Plugins\ProcessRPC;


use ESD\Core\Context\Context;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\PlugIn\AbstractPlugin;

class ProcessRPCPlugin extends AbstractPlugin
{
    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "ProcessRPC";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //注册事件派发处理函数
        MessageProcessor::addMessageProcessor(new RpcMessageProcessor());
        $this->ready();
    }
}