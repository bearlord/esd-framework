<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/28
 * Time: 14:46
 */

namespace ESD\Plugins\Scheduled\Event;

use ESD\Core\Plugins\Event\Event;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;

class ScheduledRemoveEvent extends Event
{
    const ScheduledRemoveEvent = "ScheduledRemoveEvent";

    public function __construct(string $scheduledTaskName)
    {
        parent::__construct(self::ScheduledRemoveEvent, $scheduledTaskName);
    }

    /**
     * @return ScheduledTask
     */
    public function getTaskName(): string
    {
        return $this->getData();
    }
}