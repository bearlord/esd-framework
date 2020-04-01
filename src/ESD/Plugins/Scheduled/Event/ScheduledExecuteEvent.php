<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled\Event;

use ESD\Core\Plugins\Event\Event;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;

/**
 * Class ScheduledExecuteEvent
 * @package ESD\Plugins\Scheduled\Event
 */
class ScheduledExecuteEvent extends Event
{
    const ScheduledExecuteEvent = "ScheduledExecuteEvent";

    /**
     * ScheduledExecuteEvent constructor.
     * @param ScheduledTask $data
     */
    public function __construct(ScheduledTask $data)
    {
        parent::__construct(self::ScheduledExecuteEvent, $data);
    }

    /**
     * @return ScheduledTask
     */
    public function getTask(): ScheduledTask
    {
        return $this->getData();
    }
}