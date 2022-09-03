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

    const DB_LOG_HEADER = 'cachedblog##';

    const DB_HEADER = 'cachedb##';

    const SAVE_NAME = "@Actor";

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
     * @var Lock interprocess lock
     */
    protected $lock;

    /**
     * @var string read buffer
     */
    protected $readBuffer;

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
        $this->saveFile = $this->getSaveDir() . $saveFile;
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

        $this->cacheHash = new ActorCacheHash($this);

        $this->setSaveDir(Server::$instance->getServerConfig()->getRootDir() . "bin/actor/");
        $this->setSaveFile("cache.db");
        $this->setSaveLogFile("cache.dblog");
    }

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
        Server::$instance->getLog()->critical("Cache Process autoSave...");

        goWithContext(function () {
            $temp = [];
            if (!empty($this->cacheHash->getContainer())) {
                foreach ($this->cacheHash->getContainer()[self::SAVE_NAME] as $key => $value) {
                    $temp[$key] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
                $this->redis()->hMSet("actor-cache", $temp);
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
        Server::$instance->getLog()->critical("Cache Process recovery...");
        $acotrs = $this->redis()->hGetAll("actor-cache");
        if (!empty($acotrs)) {
            foreach ($acotrs as $key => $value) {
                $valueArray = json_decode($value, true);
                Actor::create($valueArray[0], $valueArray[1], $valueArray[2], false, 30);
                Coroutine::sleep(0.001);
            }
        }

    }

    /**
     * Write log
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

        goWithContext(function () use ($data) {
            file_put_contents($this->getSaveLogFile(), $data, FILE_APPEND);
        });
    }

    /**
     * Read file
     * @param string $filepath
     * @param $callback
     * @param int int $size
     * @param int $offset
     * @return void
     */
    protected function readFile($filepath, $callback, $size = 8192, $offset = 0)
    {
        \Swoole\Coroutine::create(function () use ($filepath, $callback, $size, $offset) {
            $fp = fopen($filepath, "r");
            while (!feof($fp)) {
                $data = fread($fp, $size);
                $callback($filepath, $data);
            }
            $callback($filepath, '');
            fclose($fp);
        });
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