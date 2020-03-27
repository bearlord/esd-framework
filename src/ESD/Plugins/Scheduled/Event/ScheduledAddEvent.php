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

class ScheduledAddEvent extends Event
{
    const ScheduledAddEvent = "ScheduledAddEvent";

    public function __construct(ScheduledTask $data)
    {
        parent::__construct(self::ScheduledAddEvent, $data);
    }

    /**
     * @return ScheduledTask
     */
    public function getTask(): ScheduledTask
    {
        return $this->getData();
    }
}