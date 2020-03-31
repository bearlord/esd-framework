<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Config;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ConfigChangeEvent
 * @package ESD\Core\Plugins\Config
 */
class ConfigChangeEvent extends Event
{
    const ConfigChangeEvent = "ConfigChangeEvent";

    /**
     * ConfigChangeEvent constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ConfigChangeEvent, null);
    }
}