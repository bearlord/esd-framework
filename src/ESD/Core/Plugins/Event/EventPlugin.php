<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Event;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\PlugIn\AbstractPlugin;

/**
 * Class EventPlugin
 * @package ESD\Core\Plugins\Event
 */
class EventPlugin extends AbstractPlugin
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \Exception
     */
    public function init(Context $context)
    {
        parent::init($context);

        //Create event dispatcher
        $this->eventDispatcher = DIGet(EventDispatcher::class);

        //Add eventDispatcher type
        $this->eventDispatcher->addOrder(new ProcessEventDispatcher());
        $this->eventDispatcher->addOrder(new TypeEventDispatcher());
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        class_exists(MessageProcessor::class);
        class_exists(EventMessageProcessor::class);
        DIGet(EventCall::class, [$this->eventDispatcher, ""]);
        DIGet(Channel::class);
        class_exists(Message::class);
        class_exists(EventMessage::class);
        return;
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //Register event dispatch handler
        MessageProcessor::addMessageProcessor(new EventMessageProcessor($this->eventDispatcher));

        //Ready
        $this->ready();
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return "Event";
    }
}