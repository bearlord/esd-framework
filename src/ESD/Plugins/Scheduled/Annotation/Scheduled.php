<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled\Annotation;

use Doctrine\Common\Annotations\Annotation;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;

/**
 * @Annotation
 * @Target("METHOD")
 * Class Scheduled
 * @package ESD\Plugins\Scheduled\Annotation
 */
class Scheduled extends Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $cron;

    /**
     * @var string
     */
    public $processGroup = ScheduledTask::GROUP_NAME;
}