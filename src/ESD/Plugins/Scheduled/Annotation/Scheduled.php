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
     * 名称
     * @var string
     */
    public $name;
    /**
     * corn语法
     * @var string
     */
    public $cron;
    /**
     * 进程组
     * @var string
     */
    public $processGroup = ScheduledTask::GroupName;
}