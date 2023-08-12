<?php

namespace ESD\Plugins\Actor;

use ESD\Core\Exception;
use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use ESD\Coroutine\Coroutine;
use ESD\Plugins\Redis\GetRedis;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;
use Swoole\Lock;
use Swoole\Timer;

class ActorCacheProcess extends Process
{
    use GetRedis;

    const PROCESS_NAME = "actor-cache";

    const GROUP_NAME = "ActorCache";

    const SAVE_NAME = "@Actor";

    const HASH_KEY = "actor-cache";

    const KEY_PREFIX = "actor-single-";

    /**
     * @var float|int auto save time
     */
    protected $autoSaveTime = 5000;

    /**
     * @var int delayed recovery wait time
     */
    protected $delayedRecoveryWaitTime = 3000;

    /**
     * @var ActorCacheHash cache hash
     */
    protected $cacheHash;

    /**
     * @var string delimiter
     */
    protected $delimiter = ".";

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return float|int
     */
    public function getAutoSaveTime()
    {
        return $this->autoSaveTime;
    }

    /**
     * @param float|int $autoSaveTime
     */
    public function setAutoSaveTime($autoSaveTime): void
    {
        $this->autoSaveTime = $autoSaveTime;
    }

    /**
     * @return float|int
     */
    public function getDelayedRecoveryWaitTime()
    {
        return $this->delayedRecoveryWaitTime;
    }

    /**
     * @param float|int $delayedRecoveryWaitTime
     */
    public function setDelayedRecoveryWaitTime($delayedRecoveryWaitTime): void
    {
        $this->delayedRecoveryWaitTime = $delayedRecoveryWaitTime;
    }


    /**
     * @return mixed|void
     * @throws \ESD\Core\Exception
     */
    public function init()
    {
        $this->cacheHash = new ActorCacheHash($this);

        $autoSaveTime = Server::$instance->getConfigContext()->get('actor.autoSaveTime');
        if ($autoSaveTime > 0) {
            $this->setAutoSaveTime($autoSaveTime);
        }

        $delayedRecoveryWaitTime = Server::$instance->getConfigContext()->get('actor.delayedRecoveryWaitTime');
        if ($delayedRecoveryWaitTime > 0) {
            $this->delayedRecoveryWaitTime = $delayedRecoveryWaitTime;
        }

    }

    /**
     * @inheritDoc
     * @return mixed|void
     * @throws \Exception
     */
    public function onProcessStart()
    {
        //Save call
        $saveCall = $this->eventDispatcher->listen(ActorSaveEvent::ActorSaveEvent);
        $saveCall->call(function (ActorSaveEvent $event) {
            $class = $event->getData()[0];
            $name = $event->getData()[1];
            $data = $event->getData()[2] ?? null;
            $this->saveToCacheHash($name, [$class, $name, $data]);
        });

        //Delete call
        $deleteCall = $this->eventDispatcher->listen(ActorDeleteEvent::ActorDeleteEvent);
        $deleteCall->call(function (ActorDeleteEvent $event) {
            $name = $event->getData()[0];
            $this->deleteFromCacheHash($name);
        });

        //Recovery
        Timer::after($this->delayedRecoveryWaitTime, function () {
            $this->recovery();
        });


        //Auto save to redis
        Timer::tick($this->autoSaveTime, function () {
            $this->autoSave();
        });
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStop()
    {
        $this->autoSave();
    }

    /**
     * @inheritDoc
     * @param Message $message
     * @param Process $fromProcess
     * @return mixed|void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {
    }

    /**
     * Save to chache hash
     * @param string $actorName
     * @param array $data
     * @return void
     */
    protected function saveToCacheHash(string $actorName, array $data)
    {
        $name = self::SAVE_NAME . $this->delimiter . $actorName;
        $this->cacheHash[$name] = $data;
    }

    /**
     * Delete from cache hash
     * @param string $actorName
     * @return void
     */
    protected function deleteFromCacheHash(string $actorName)
    {
        $name = self::SAVE_NAME . $this->delimiter . $actorName;
        if (!empty($this->cacheHash[$name])) {
            unset($this->cacheHash[$name]);
        }
    }

    /**
     * Auto save
     * @return void
     * @throws \Exception
     */
    protected function autoSave()
    {
        goWithContext(function () {
            if (!empty($this->cacheHash->getContainer())) {
                foreach ($this->cacheHash[self::SAVE_NAME] as $k1 => $v1) {
                    $valueJson = json_encode($v1, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                    $redisKey = self::KEY_PREFIX . $k1;
                    $this->redis()->set($redisKey, $valueJson);

                    Coroutine::sleep(0.001);
                }

                //actor keys of redis
                $actorKeysRedis = $this->redis()->keys(self::KEY_PREFIX . "*");
                //actor keys current
                $actorKeysCurrent = array_map(function ($value) {
                    return self::KEY_PREFIX . $value;
                }, array_keys($this->cacheHash[self::SAVE_NAME]));

                //delete data from redis for non-existent actors
                foreach ($actorKeysRedis as $k2 => $v2) {
                    if (!in_array($v2, $actorKeysCurrent)) {
                        $this->redis()->del($v2);
                    }
                }
            }
        });
    }

    /**
     * Recovery
     * @return void
     * @throws \Exception
     */
    protected function recovery()
    {
        $actorKeys = $this->redis()->keys(self::KEY_PREFIX . "*");
        if (!empty($actorKeys)) {
            foreach ($actorKeys as $k => $v) {
                $value = $this->redis()->get($v);
                $valueArray = json_decode($value, true);
                Actor::create($valueArray[0], $valueArray[1], $valueArray[2], false, 30);

                Coroutine::sleep(0.001);
            }
        }
    }

    /**
     * @inheritDoc
     * @param $callback
     * @param $parameter
     * @return void
     */
    protected function call($callback, $parameter)
    {
        if (is_callable($callback)) {
            return $callback(...$parameter);
        }
    }

}