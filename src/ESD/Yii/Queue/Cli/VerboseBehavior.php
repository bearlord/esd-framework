<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Cli;

use ESD\Yii\Base\Behavior;
use ESD\Yii\Console\Controller;
use ESD\Yii\Helpers\Console;
use ESD\Yii\Queue\ExecEvent;
use ESD\Yii\Queue\JobInterface;

/**
 * Verbose Behavior.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class VerboseBehavior extends Behavior
{
    /**
     * @var Queue
     */
    public $owner;
    /**
     * @var Controller
     */
    public $command;

    /**
     * @var float timestamp
     */
    private $jobStartedAt;
    /**
     * @var int timestamp
     */
    private $workerStartedAt;


    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            Queue::EVENT_BEFORE_EXEC => 'beforeExec',
            Queue::EVENT_AFTER_EXEC => 'afterExec',
            Queue::EVENT_AFTER_ERROR => 'afterError',
            Queue::EVENT_WORKER_START => 'workerStart',
            Queue::EVENT_WORKER_STOP => 'workerStop',
        ];
    }

    /**
     * @param ExecEvent $event
     */
    public function beforeExec(ExecEvent $event)
    {
        $this->jobStartedAt = microtime(true);
        $this->command->stdout(date('Y-m-d H:i:s'), Console::FG_YELLOW);
        $this->command->stdout($this->jobTitle($event), Console::FG_GREY);
        $this->command->stdout(' - ', Console::FG_YELLOW);
        $this->command->stdout('Started', Console::FG_GREEN);
        $this->command->stdout(PHP_EOL);
    }

    /**
     * @param ExecEvent $event
     */
    public function afterExec(ExecEvent $event)
    {
        $this->command->stdout(date('Y-m-d H:i:s'), Console::FG_YELLOW);
        $this->command->stdout($this->jobTitle($event), Console::FG_GREY);
        $this->command->stdout(' - ', Console::FG_YELLOW);
        $this->command->stdout('Done', Console::FG_GREEN);
        $duration = number_format(round(microtime(true) - $this->jobStartedAt, 3), 3);
        $this->command->stdout(" ($duration s)", Console::FG_YELLOW);
        $this->command->stdout(PHP_EOL);
    }

    /**
     * @param ExecEvent $event
     */
    public function afterError(ExecEvent $event)
    {
        $this->command->stdout(date('Y-m-d H:i:s'), Console::FG_YELLOW);
        $this->command->stdout($this->jobTitle($event), Console::FG_GREY);
        $this->command->stdout(' - ', Console::FG_YELLOW);
        $this->command->stdout('Error', Console::BG_RED);
        if ($this->jobStartedAt) {
            $duration = number_format(round(microtime(true) - $this->jobStartedAt, 3), 3);
            $this->command->stdout(" ($duration s)", Console::FG_YELLOW);
        }
        $this->command->stdout(PHP_EOL);
        $this->command->stdout('> ' . get_class($event->error) . ': ', Console::FG_RED);
        $message = explode("\n", ltrim($event->error->getMessage()), 2)[0]; // First line
        $this->command->stdout($message, Console::FG_GREY);
        $this->command->stdout(PHP_EOL);
    }

    /**
     * @param ExecEvent $event
     * @return string
     * @since 2.0.2
     */
    protected function jobTitle(ExecEvent $event)
    {
        $name = $event->job instanceof JobInterface ? get_class($event->job) : 'unknown job';
        $extra = "attempt: $event->attempt";
        return " [$event->id] $name ($extra)";
    }

    /**
     * @param int $value
     * @return string
     * @since 2.0.2
     */
    protected function formatDuration($value)
    {
        $seconds = $value % 60;
        $value = ($value - $seconds) / 60;
        $minutes = $value % 60;
        $value = ($value - $minutes) / 60;
        $hours = $value % 24;
        $days = ($value - $hours) / 24;

        if ($days > 0) {
            return sprintf('%d:%02d:%02d:%02d', $days, $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
