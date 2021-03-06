<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled\Event;

use ESD\Core\Plugins\Event\Event;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;

/**
 * Class ScheduledRemoveEvent
 * @package ESD\Plugins\Scheduled\Event
 */
class ScheduledRemoveEvent extends Event
{
    const SCHEDULED_REMOVE_EVENT = "ScheduledRemoveEvent";

    /**
     * ScheduledRemoveEvent constructor.
     * @param string $scheduledTaskName
     */
    public function __construct(string $scheduledTaskName)
    {
        parent::__construct(self::SCHEDULED_REMOVE_EVENT, $scheduledTaskName);
    }

    /**
     * @return ScheduledTask
     */
    public function getTaskName(): string
    {
        return $this->getData();
    }
}