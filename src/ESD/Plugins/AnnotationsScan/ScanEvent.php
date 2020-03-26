<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\AnnotationsScan;

use ESD\Core\Plugins\Event\Event;

/**
 * Class ScanEvent
 * @package ESD\Plugins\AnnotationsScan
 */
class ScanEvent extends Event
{
    /**
     * Type
     */
    const type = "ScanEvent";

    /**
     * ScanEvent constructor.
     */
    public function __construct()
    {
        parent::__construct(self::type, "");
    }
}