<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server;


use ESD\Core\Plugins\Event\Event;

class ApplicationEvent extends Event
{
    const ApplicationStartingEvent = "ApplicationStartingEvent";
    const ApplicationShutdownEvent = "ApplicationShutdownEvent";
}