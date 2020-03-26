<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\PlugIn;


use ESD\Core\Plugins\Event\Event;

class PluginEvent extends Event
{
    /**
     * Plugin success event
     */
    const PlugSuccessEvent = "PlugSuccessEvent";

    /**
     * Plugin fail event
     */
    const PlugFailEvent = "PlugFailEvent";

    /**
     * Plugin ready
     */
    const PlugReady = "PlugReady";
}