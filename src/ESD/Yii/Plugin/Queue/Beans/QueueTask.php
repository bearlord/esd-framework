<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Yii\Plugin\Queue\Beans;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Plugins\Scheduled\Cron\CronExpression;

/**
 * Class ScheduledTask
 * @package ESD\Plugins\Scheduled\Beans
 */
class QueueTask extends BaseConfig
{
    const KEY = "queue.task";

    const PROCESS_GROUP_ALL = "all";

    const GROUP_NAME = "QueueGroup";

    /**
     * @var string
     */
    protected $processGroup = QueueTask::GROUP_NAME;

    /**
     * ScheduledTask constructor.
     * @param $name
     * @param $expression
     * @param $className
     * @param $functionName
     * @param string $processGroup
     */
    public function __construct($processGroup = QueueTask::GROUP_NAME)
    {
        parent::__construct(self::KEY);
        $this->processGroup = $processGroup;
    }

    /**
     * @return string
     */
    public function getProcessGroup(): string
    {
        return $this->processGroup;
    }

    /**
     * @param string $processGroup
     */
    public function setProcessGroup(string $processGroup): void
    {
        $this->processGroup = $processGroup;
    }
}