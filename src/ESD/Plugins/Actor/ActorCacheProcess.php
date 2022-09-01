<?php

namespace ESD\Plugins\Actor;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;
use Swoole\Lock;
use Swoole\Timer;

class ActorCacheProcess extends Process
{
    const PROCESS_NAME = "actor-cache";

    const GROUP_NAME = "ActorCache";

    const DB_LOG_HEADER = 'cachedblog##';

    const DB_HEADER = 'cachedb##';

    const SAVE_NAME = "@Actor";

    /**
     * @var Lock interprocess lock
     */
    protected $lock;

    /**
     * @var ActorCacheHash cache hash
     */
    protected $cahceHash;

    /**
     * @var float|int auto save time
     */
    protected $autoSaveTime = 5 * 1000;

    /**
     * @var string save dir
     */
    protected $saveDir = "";

    /**
     * @var string save file
     */
    protected $saveFile = "";

    /**
     * @var string save log file
     */
    protected $saveLogFile = "";

    /**
     * @var string delimiter
     */
    protected $delimiter = ".";

    /**
     * @return string
     */
    public function getSaveDir(): string
    {
        return $this->saveDir;
    }

    /**
     * @param string $saveDir
     */
    public function setSaveDir(string $saveDir): void
    {
        $this->saveDir = $saveDir;
        if (!file_exists($this->saveDir)) {
            mkdir($this->saveDir);
        }
    }

    /**
     * @return string
     */
    public function getSaveFile(): string
    {
        return $this->saveFile;
    }

    /**
     * @param string $saveFile
     */
    public function setSaveFile(string $saveFile): void
    {
        $this->saveFile = $this->getSaveDir() .$saveFile;
    }

    /**
     * @return string
     */
    public function getSaveLogFile(): string
    {
        return $this->saveLogFile;
    }

    /**
     * @param string $saveLogFile
     */
    public function setSaveLogFile(string $saveLogFile): void
    {
        $this->saveLogFile = $this->getSaveDir() . $saveLogFile;
    }

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
        $this->lock = new Lock(SWOOLE_MUTEX);

        $this->cahceHash = new ActorCacheHash($this);

        $this->setSaveDir(Server::$instance->getServerConfig()->getRootDir() . "bin/actor/");
        $this->setSaveFile("cache.db");
        $this->setSaveLogFile("cache.dblog");

        Server::$instance->getLog()->critical("Cache Process init..." . $this->saveDir);
    }

    public function onProcessStart()
    {
        Server::$instance->getLog()->critical("Cache Process onProcessStart...");

        $call = $this->eventDispatcher->listen(ActorSaveEvent::ActorSaveEvent);
        $call->call(function (ActorSaveEvent $event) {
            $class = $event->getData()[0];
            $name = $event->getData()[1];
            $data = $event->getData()[2] ?? null;
            $this->saveToCacheHash($name, [$name, $class, $data]);
        });

        Timer::tick($this->autoSaveTime, function () {
            $this->autoSave();
        });

    }

    public function onProcessStop()
    {
        $this->autoSave();
        Server::$instance->getLog()->critical("Cache Process onProcessStop...");
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        Server::$instance->getLog()->critical("Cache Process onPipeMessage...");
    }

    protected function saveToCacheHash($acotName, $data)
    {
        $name = self::SAVE_NAME . $this->delimiter . $acotName;
        $this->cahceHash[$name] = $data;
    }

    protected function autoSave()
    {
        Server::$instance->getLog()->critical("Cache Process autoSave...");

        goWithContext(function (){
            $saveTempFile = $this->saveDir . "catCache.catdb." . time();
            if (!file_exists($saveTempFile)) {
                file_put_contents($saveTempFile, self::DB_HEADER);
            }
            if (!empty($this->cahceHash->getContainer())) {
                foreach ($this->cahceHash->getContainer() as $key => $value) {
                    $one = [];
                    $one[$key] = $value;
                    $buffer = serialize($one);
                    $length = 4 + strlen($buffer);
                    $data = pack('N', $length) . $buffer;
                    file_put_contents($saveTempFile, $data, FILE_APPEND);
                }
            }

            rename($saveTempFile, $this->saveFile);
            if (file_exists($this->saveLogFile)) {
                file_put_contents($this->saveLogFile, self::DB_LOG_HEADER);
            }
        });
    }

    protected function recovery()
    {
        Server::$instance->getLog()->critical("Cache Process recovery...");
    }

    /**
     * @param string $method
     * @param array $params
     * @return void
     */
    public function writeLog(string $method, array $params)
    {
        if (!$this->isReady()) {
            $this->lock->lock();
            $this->lock->unlock();
        }

        $one[0] = $method;
        $one[1] = $params;
        $buffer = serialize($one);
        $totalLength = 4 + strlen($buffer);
        $data = pack('N', $totalLength) . $buffer;

        goWithContext(function () use ($data){
            file_put_contents($this->getSaveLogFile(), $data, FILE_APPEND);
        });
    }

}