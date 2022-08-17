<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\AutoReload;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Server\Coroutine\Server;

class AutoReloadPlugin extends AbstractPlugin
{
    const PROCESS_NAME = "helper";
    const PROCESS_GROUP_NAME = "HelperGroup";

    /**
     * @var InotifyReload
     */
    protected $inotifyReload;

    /**
     * @var AutoReloadConfig
     */
    private $autoReloadConfig;

    /**
     * AutoReloadPlugin constructor.
     * @param AutoReloadConfig|null $autoReloadConfig
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     * @throws \DI\NotFoundException
     */
    public function __construct(?AutoReloadConfig $autoReloadConfig = null)
    {
        parent::__construct();
        if ($autoReloadConfig == null) {
            $autoReloadConfig = new AutoReloadConfig();
        }
        $this->autoReloadConfig = $autoReloadConfig;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "AutoReload";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     * @throws \ESD\Core\Exception
     */
    public function beforeServerStart(Context $context)
    {
        if ($this->autoReloadConfig->getMonitorDir() == null) {
            $this->autoReloadConfig->setMonitorDir(Server::$instance->getServerConfig()->getSrcDir());
        }
        $this->autoReloadConfig->merge();

        //Add help process
        Server::$instance->addProcess(self::PROCESS_NAME, HelperReloadProcess::class, self::PROCESS_GROUP_NAME);
        return;
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \Exception
     */
    public function beforeProcessStart(Context $context)
    {
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::PROCESS_NAME) {
            $this->inotifyReload = new InotifyReload($this->autoReloadConfig);
        }
        $this->ready();
    }

    /**
     * @return InotifyReload
     */
    public function getInotifyReload(): InotifyReload
    {
        return $this->inotifyReload;
    }
}