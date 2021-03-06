<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled\Event;

use ESD\Core\Plugins\Event\Event;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;

/**
 * Class ScheduledAddEvent
 * @package ESD\Plugins\Scheduled\Event
 */
class ScheduledAddEvent extends Event
{
    const SCHEDULED_ADD_EVENT = "ScheduledAddEvent";

    /**
     * ScheduledAddEvent constructor.
     * @param ScheduledTask $data
     */
    public function __construct(ScheduledTask $data)
    {
        parent::__construct(self::SCHEDULED_ADD_EVENT, $data);
    }

    /**
     * @return ScheduledTask
     */
    public function getTask(): ScheduledTask
    {
        return $this->getData();
    }
}