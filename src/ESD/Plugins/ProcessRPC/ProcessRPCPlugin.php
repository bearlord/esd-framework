<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */
namespace ESD\Plugins\ProcessRPC;

use ESD\Core\Context\Context;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\Plugin\AbstractPlugin;

/**
 * Class ProcessRPCPlugin
 * @package ESD\Plugins\ProcessRPC
 */
class ProcessRPCPlugin extends AbstractPlugin
{
    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "ProcessRPC";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //Register event dispatch handler
        MessageProcessor::addMessageProcessor(new RpcMessageProcessor());
        $this->ready();
    }
}