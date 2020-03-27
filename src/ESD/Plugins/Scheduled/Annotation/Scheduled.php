<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/14
 * Time: 15:48
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