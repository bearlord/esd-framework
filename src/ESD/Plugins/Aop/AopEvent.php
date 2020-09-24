<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Core\Plugins\Event\Event;

/**
 * Class AopEvent
 * @package ESD\Plugins\Aop
 */
class AopEvent extends Event
{
    const type = "AopEvent";

    /**
     * AopEvent constructor.
     */
    public function __construct()
    {
        parent::__construct(self::type, "");
    }
}