<?php

namespace ESD\Plugins\Actor;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use ESD\Server\Coroutine\Server;
use Swoole\Lock;
use Swoole\Timer;

class ActorCacheProcess extends Process
{
    /**
     * @var Lock interprocess lock
     */
    protected $lock;

    /**
     * @var float|int auto save time
     */
    public $autoSaveTime = 30 * 1000;

    /**
     * @var string save dir
     */
    public $saveDir = "";

    /**
     * @var string delimiter
     */
    public $delimiter = ".";

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

        $this->setSaveDir(Server::$instance->getServerConfig()->getRootDir() . "bin/actor/");

        Server::$instance->getLog()->critical("Cache Process init..." . $this->saveDir);
    }

    public function onProcessStart()
    {
        Server::$instance->getLog()->critical("Cache Process onProcessStart...");

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

    protected function autoSave()
    {
        Server::$instance->getLog()->critical("Cache Process autoSave...");
    }

    protected function recovery()
    {
        Server::$instance->getLog()->critical("Cache Process recovery...");
    }

}