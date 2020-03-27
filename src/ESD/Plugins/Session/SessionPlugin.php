<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/7
 * Time: 14:33
 */

namespace ESD\Plugins\Session;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Plugins\Redis\RedisPlugin;

class SessionPlugin extends AbstractPlugin
{

    /**
     * @var SessionConfig
     */
    private $sessionConfig;

    /**
     * @var SessionStorage
     */
    protected $sessionStorage;

    /**
     * SessionPlugin constructor.
     * @param SessionConfig|null $sessionConfig
     * @throws \ReflectionException
     */
    public function __construct(?SessionConfig $sessionConfig = null)
    {
        parent::__construct();
        $this->atAfter(RedisPlugin::class);
        if ($sessionConfig == null) {
            $sessionConfig = new SessionConfig();
        }
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new RedisPlugin());
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Session";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->sessionConfig->merge();
        $class = $this->sessionConfig->getSessionStorageClass();
        $this->sessionStorage = new $class($this->sessionConfig);
        $this->setToDIContainer(SessionStorage::class, $this->sessionStorage);
        $this->setToDIContainer(HttpSession::class, new HttpSessionProxy());
        return;
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return SessionStorage
     */
    public function getSessionStorage(): SessionStorage
    {
        return $this->sessionStorage;
    }
}