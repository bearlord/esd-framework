<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled\Beans;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Plugins\Scheduled\Cron\CronExpression;

/**
 * Class ScheduledTask
 * @package ESD\Plugins\Scheduled\Beans
 */
class ScheduledTask extends BaseConfig
{
    const KEY = "scheduled.task";

    const PROCESS_GROUP_ALL = "all";

    const GROUP_NAME = "ScheduledGroup";

    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $functionName;

    /**
     * @var string
     */
    protected $processGroup = ScheduledTask::GROUP_NAME;

    /**
     * @var CronExpression
     */
    private $cron;

    /**
     * ScheduledTask constructor.
     * @param $name
     * @param $expression
     * @param $className
     * @param $functionName
     * @param string $processGroup
     */
    public function __construct($name, $expression, $className, $functionName, $processGroup = ScheduledTask::GROUP_NAME)
    {
        parent::__construct(self::KEY);
        $this->name = $name;
        $this->expression = $expression;
        $this->className = $className;
        $this->functionName = $functionName;
        $this->processGroup = $processGroup;
        if ($expression != null) {
            $this->cron = CronExpression::factory($expression);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression(string $expression): void
    {
        $this->expression = $expression;
        $this->cron = CronExpression::factory($expression);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @param string $functionName
     */
    public function setFunctionName(string $functionName): void
    {
        $this->functionName = $functionName;
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

    /**
     * @return CronExpression
     */
    public function getCron(): CronExpression
    {
        return $this->cron;
    }
}