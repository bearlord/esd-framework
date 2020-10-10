<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Queue\Cli;

use ESD\Yii\Yii;
use ESD\Yii\Base\InvalidConfigException;

use ESD\Yii\Console\Application as ConsoleApp;
use ESD\Yii\Helpers\Inflector;
use ESD\Yii\Queue\Queue as BaseQueue;

/**
 * Queue with CLI.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class Queue extends BaseQueue
{
    /**
     * @event WorkerEvent that is triggered when the worker is started.
     * @since 2.0.2
     */
    const EVENT_WORKER_START = 'workerStart';
    /**
     * @event WorkerEvent that is triggered each iteration between requests to queue.
     * @since 2.0.3
     */
    const EVENT_WORKER_LOOP = 'workerLoop';
    /**
     * @event WorkerEvent that is triggered when the worker is stopped.
     * @since 2.0.2
     */
    const EVENT_WORKER_STOP = 'workerStop';
    
    /**
     * @var string command class name
     */
    public $commandClass = Command::class;
    /**
     * @var array of additional options of command
     */
    public $commandOptions = [];
    /**
     * @var callable|null
     * @internal for worker command only
     */
    public $messageHandler;

    /**
     * Runs worker.
     *
     * @param callable $handler
     * @return null|int exit code
     * @since 2.0.2
     */
    protected function runWorker(callable $handler)
    {
        $event = new WorkerEvent();
        $this->trigger(self::EVENT_WORKER_START, $event);
        if ($event->exitCode !== null) {
            return $event->exitCode;
        }

        $exitCode = null;
        try {
            call_user_func($handler, function () use ($event) {
                $this->trigger(self::EVENT_WORKER_LOOP, $event);
                return $event->exitCode === null;
            });
        } finally {
            $this->trigger(self::EVENT_WORKER_STOP, $event);
        }

        return $event->exitCode;
    }

    /**
     * @inheritdoc
     */
    protected function handleMessage($id, $message, $ttr, $attempt)
    {
        if ($this->messageHandler) {
            return call_user_func($this->messageHandler, $id, $message, $ttr, $attempt);
        }

        return parent::handleMessage($id, $message, $ttr, $attempt);
    }

    /**
     * @param string $id of a message
     * @param string $message
     * @param int $ttr time to reserve
     * @param int $attempt number
     * @param int|null $workerPid of worker process
     * @return bool
     * @internal for worker command only
     */
    public function execute($id, $message, $ttr, $attempt, $workerPid)
    {
        return parent::handleMessage($id, $message, $ttr, $attempt);
    }
}
