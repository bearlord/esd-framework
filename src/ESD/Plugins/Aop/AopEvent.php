<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Core\Plugins\Event\Event;

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