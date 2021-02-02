<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ProcessEvent
 * @package ESD\Core\Server\Process
 */
class ProcessEvent extends Event
{
    /**
     * Process start event
     */
    const ProcessStartEvent = "ProcessStartEvent";

    /**
     * Process stop event
     */
    const ProcessStopEvent = "ProcessStopEvent";
}