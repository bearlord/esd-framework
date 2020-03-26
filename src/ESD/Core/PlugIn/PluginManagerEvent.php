<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\PlugIn;

use ESD\Core\Plugins\Event\Event;

/**
 * Class PluginManagerEvent
 * @package ESD\Core\PlugIn
 */
class PluginManagerEvent extends Event
{
    /**
     * Plugin before server start event
     */
    const PlugBeforeServerStartEvent = "PlugBeforeServerStartEvent";

    /**
     * Plugin after server start event
     */
    const PlugAfterServerStartEvent = "PlugAfterServerStartEvent";

    /**
     * Plugin before process start event
     */
    const PlugBeforeProcessStartEvent = "PlugBeforeProcessStartEvent";

    /**
     * Plugin after process start event
     */
    const PlugAfterProcessStartEvent = "PlugAfterProcessStartEvent";

    /**
     * Plugin all ready event
     */
    const PlugAllReadyEvent = "PlugAllReadyEvent";
}