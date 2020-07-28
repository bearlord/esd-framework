<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugin;


use ESD\Core\Plugins\Event\Event;

class PluginEvent extends Event
{
    /**
     * Plugin success event
     */
    const PluginSuccessEvent = "PluginSuccessEvent";

    /**
     * Plugin fail event
     */
    const PlugFailEvent = "PlugFailEvent";

    /**
     * Plugin ready
     */
    const PlugReady = "PlugReady";
}