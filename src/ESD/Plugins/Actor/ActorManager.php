<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Core\Memory\CrossProcess\Atomic;
use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Class ActorManager
 * @package ESD\Plugins\Actor
 */
class ActorManager
{
    use GetLogger;
    /**
     * @var ActorManager
     */
    protected static $instance;

    /**
     * @var Table
     */
    protected $actorTable;

    /**
     * @var Table
     */
    protected $actorIdClassNameTable;

    /**
     * @var Table
     */
    protected $actorClassNameIdTable;

    /**
     *
     * @var int
     */
    protected $serverStartTime;

    /**
     * @var ActorConfig
     */
    protected $actorConfig;

    /**
     * @var Atomic
     */
    protected $atomic;

    /**
     * ActorManager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->actorConfig = DIGet(ActorConfig::class);
        $this->actorTable = new Table($this->actorConfig->getActorMaxCount());
        $this->actorTable->column("processId", Table::TYPE_INT);
        $this->actorTable->column("createTime", Table::TYPE_INT);
        $this->actorTable->column("classId", Table::TYPE_INT);
        $this->actorTable->create();

        $this->actorIdClassNameTable = new Table($this->actorConfig->getActorMaxClassCount());
        $this->actorIdClassNameTable->column("className", Table::TYPE_STRING, 100);
        $this->actorIdClassNameTable->create();

        $this->actorClassNameIdTable = new Table($this->actorConfig->getActorMaxClassCount());
        $this->actorClassNameIdTable->column("id", Table::TYPE_INT);
        $this->actorClassNameIdTable->create();

        $this->atomic = new Atomic();
    }

    /**
     * @return ActorManager
     * @throws \Exception
     */
    public static function getInstance(): ActorManager
    {
        if (self::$instance == null) {
            self::$instance = new ActorManager();
        }
        return self::$instance;
    }

    /**
     * @param string $actorName
     * @return ActorInfo
     */
    public function getActorInfo(string $actorName): ?ActorInfo
    {
        $data = $this->actorTable->get($actorName);
        if (empty($data)) {
            return null;
        }

        $className = $this->actorIdClassNameTable->get($data["classId"], "className");
        $actorInfo = new ActorInfo();
        $actorInfo->setName($actorName);
        $actorInfo->setClassName($className);
        $actorInfo->setProcess(Server::$instance->getProcessManager()->getProcessFromId($data["processId"]));
        $actorInfo->setCreateTime($data["createTime"]);
        return $actorInfo;
    }

    /**
     * @param Actor $actor
     * @throws ActorException
     */
    public function addActor(Actor $actor)
    {
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getGroupName() != ActorConfig::GROUP_NAME) {
            throw new ActorException("Do not new a actor, use Actor::create()");
        }
        if ($this->actorTable->exist($actor->getName())) {
            throw new ActorException("Has same actor name :{$actor->getName()}");
        }
        $className = get_class($actor);
        $actorClassNameId = $this->actorClassNameIdTable->get($className);
        if (empty($actorClassNameId)) {
            $id = $this->actorIdClassNameTable->count();
            $this->actorIdClassNameTable->set($id, ["className" => $className]);
            $this->actorClassNameIdTable->set($className, ['id' => $id]);
        } else {
            $id = $actorClassNameId['id'];
        }

        $this->actorTable->set($actor->getName(), [
            "processId" => Server::$instance->getProcessManager()->getCurrentProcessId(),
            "createTime" => time(),
            "classId" => $id
        ]);
        DISet($className . ":" . $actor->getName(), $actor);

        $this->debug(Yii::t('esd', 'Actor {actor} created', [
            'actor' => $actor->getName()
        ]));
    }

    /**
     * @param Actor $actor
     */
    public function removeActor(Actor $actor)
    {
        $className = get_class($actor);
        DISet($className . ":" . $actor->getName(), null);
        $this->actorTable->del($actor->getName());

        $this->debug(Yii::t('esd', 'Actor {actor} removed', [
            'actor' => $actor->getName()
        ]));
    }

    /**
     * @return Atomic
     */
    public function getAtomic(): Atomic
    {
        return $this->atomic;
    }

    /**
     * @param string $actorName
     * @return bool
     */
    public function hasActor(string $actorName)
    {
        $data = $this->actorTable->get($actorName);
        if (empty($data)) {
            return false;
        }
        return true;
    }
}