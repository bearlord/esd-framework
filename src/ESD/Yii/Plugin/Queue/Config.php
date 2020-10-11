<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Yii\Plugins\Queue;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledAddEvent;
use ESD\Plugins\Scheduled\Event\ScheduledRemoveEvent;
use ESD\Server\Co\Server;
use ESD\Yii\Plugin\Queue\Beans\QueueTask;
use ESD\Yii\Yii;

/**
 * Class QueueConfig
 * @package ESD\Plugins\Scheduled
 */
class Config extends BaseConfig
{
    const KEY = "queue";

    /**
     * @var string
     */
    protected $name = "default";

    /**
     * Minimum interval
     * @var int
     */
    protected $minIntervalTime;

    /**
     * @var int
     */
    protected $poolMaxNumber = 5;

    /**
     * Config constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct(self::KEY, true, "name");
        $this->setName($name);
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getMinIntervalTime(): int
    {
        return $this->minIntervalTime;
    }

    /**
     * @param int $minIntervalTime
     */
    public function setMinIntervalTime(int $minIntervalTime)
    {
        $this->minIntervalTime = $minIntervalTime;
    }
    

    /**
     * @return int
     */
    public function getPoolMaxNumber(): int
    {
        return $this->poolMaxNumber;
    }

    /**
     * @param int $poolMaxNumber
     */
    public function setPoolMaxNumber(int $poolMaxNumber)
    {
        $this->poolMaxNumber = $poolMaxNumber;
    }


    public function buildFromArray($array)
    {
        
    }
    
}