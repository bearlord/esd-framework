<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue;

use ESD\Yii\Yii;
use ESD\Yii\Base\Behavior;

/**
 * Log Behavior.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class LogBehavior extends Behavior
{
    /**
     * @var Queue
     * @inheritdoc
     */
    public $owner;
    /**
     * @var bool
     */
    public $autoFlush = true;


    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            Queue::EVENT_AFTER_PUSH => 'afterPush',
            Queue::EVENT_BEFORE_EXEC => 'beforeExec',
            Queue::EVENT_AFTER_EXEC => 'afterExec',
            Queue::EVENT_AFTER_ERROR => 'afterError',
            Cli\Queue::EVENT_WORKER_START => 'workerStart',
            Cli\Queue::EVENT_WORKER_STOP => 'workerStop',
        ];
    }

    /**
     * @param PushEvent $event
     */
    public function afterPush(PushEvent $event)
    {
        $title = $this->getJobTitle($event);
        Yii::info("$title is pushed.", Queue::class);
    }

    /**
     * @param ExecEvent $event
     */
    public function beforeExec(ExecEvent $event)
    {
        $title = $this->getExecTitle($event);
        Yii::info("$title is started.", Queue::class);
        Yii::beginProfile($title, Queue::class);
    }

    /**
     * @param ExecEvent $event
     */
    public function afterExec(ExecEvent $event)
    {
        $title = $this->getExecTitle($event);
        Yii::endProfile($title, Queue::class);
        Yii::info("$title is finished.", Queue::class);
        if ($this->autoFlush) {
            Yii::getLogger()->flush(true);
        }
    }

    /**
     * @param ExecEvent $event
     */
    public function afterError(ExecEvent $event)
    {
        $title = $this->getExecTitle($event);
        Yii::endProfile($title, Queue::class);
        Yii::error("$title is finished with error: $event->error.", Queue::class);
        if ($this->autoFlush) {
            Yii::getLogger()->flush(true);
        }
    }

    /**
     * @param JobEvent $event
     * @return string
     * @since 2.0.2
     */
    protected function getJobTitle(JobEvent $event)
    {
        $name = $event->job instanceof JobInterface ? get_class($event->job) : 'unknown job';
        return "[$event->id] $name";
    }

    /**
     * @param ExecEvent $event
     * @return string
     * @since 2.0.2
     */
    protected function getExecTitle(ExecEvent $event)
    {
        $title = $this->getJobTitle($event);
        $extra = "attempt: $event->attempt";
        return "$title ($extra)";
    }
}
