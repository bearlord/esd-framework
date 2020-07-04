<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class ActorConfig
 * @package ESD\Plugins\Actor
 */
class ActorConfig extends BaseConfig
{
    const key = "actor";

    const groupName = "ActorGroup";
    
    /**
     * @var int Actor max count
     */
    protected $actorMaxCount = 10000;

    /**
     * @var int Actor mx class count
     */
    protected $actorMaxClassCount = 100;

    /**
     * @var int Actor worker count
     */
    protected $actorWorkerCount = 1;

    /**
     * @var int Actor mailbox capacity
     */
    protected $actorMailboxCapacity = 100;

    /**
     * ActorConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return int
     */
    public function getActorMaxCount(): int
    {
        return $this->actorMaxCount;
    }

    /**
     * @param int $actorMaxCount
     */
    public function setActorMaxCount(int $actorMaxCount): void
    {
        $this->actorMaxCount = $actorMaxCount;
    }

    /**
     * @return int
     */
    public function getActorMaxClassCount(): int
    {
        return $this->actorMaxClassCount;
    }

    /**
     * @param int $actorMaxClassCount
     */
    public function setActorMaxClassCount(int $actorMaxClassCount): void
    {
        $this->actorMaxClassCount = $actorMaxClassCount;
    }


    /**
     * @return int
     */
    public function getActorWorkerCount(): int
    {
        return $this->actorWorkerCount;
    }

    /**
     * @param int $actorWorkerCount
     */
    public function setActorWorkerCount(int $actorWorkerCount): void
    {
        $this->actorWorkerCount = $actorWorkerCount;
    }

    /**
     * @return int
     */
    public function getActorMailboxCapacity(): int
    {
        return $this->actorMailboxCapacity;
    }

    /**
     * @param int $actorMailboxCapacity
     */
    public function setActorMailboxCapacity(int $actorMailboxCapacity): void
    {
        $this->actorMailboxCapacity = $actorMailboxCapacity;
    }
}