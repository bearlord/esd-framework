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

    /**
     * @var float|int auto save time
     */
    protected $autoSaveTime = 5 * 1000;

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
     * @return mixed|void
     * @throws \ESD\Core\Exception
     */
    public function init()
    {
        $this->cacheHash = new ActorCacheHash($this);

        Server::$instance->getLog()->error(Server::$instance->getConfigContext()->get('actor.autoSaveTime'));
        $this->setAutoSaveTime(Server::$instance->getConfigContext()->get('actor.autoSaveTime'));
    }

    /**
     * @inheritDoc
     * @return mixed|void
     * @throws \Exception
     */
    public function onProcessStart()
    {
        $call = $this->eventDispatcher->listen(ActorSaveEvent::ActorSaveEvent);
        $call->call(function (ActorSaveEvent $event) {
            $class = $event->getData()[0];
            $name = $event->getData()[1];
            $data = $event->getData()[2] ?? null;
            $this->saveToCacheHash($name, [$class, $name, $data]);
        });

        $this->recovery();

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
     * @param string $acotName
     * @param array $data
     * @return void
     */
    protected function saveToCacheHash(string $acotName, array $data)
    {
        $name = self::SAVE_NAME . $this->delimiter . $acotName;
        $this->cacheHash[$name] = $data;
    }

    /**
     * Auto save
     * @return void
     * @throws \Exception
     */
    protected function autoSave()
    {
        goWithContext(function () {
            $saveData = [];
            if (!empty($this->cacheHash->getContainer())) {
                foreach ($this->cacheHash->getContainer()[self::SAVE_NAME] as $key => $value) {
                    $saveData[$key] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
                $this->redis()->hMSet(self::HASH_KEY, $saveData);
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
        $acotrs = $this->redis()->hGetAll(self::HASH_KEY);
        if (!empty($acotrs)) {
            foreach ($acotrs as $key => $value) {
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
        if (is_callable($function)) {
            return $function(...$parameter);
        }
    }

}