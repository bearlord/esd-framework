<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/22
 * Time: 10:13
 */

namespace ESD\Plugins\Topic;

use ESD\Core\Context\Context;
use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Topic\Aspect\TopicAspect;
use ESD\Plugins\Uid\UidConfig;
use ESD\Plugins\Uid\UidPlugin;

class TopicPlugin extends AbstractPlugin
{
    const processGroupName = "HelperGroup";
    /**
     * @var Table
     */
    protected $topicTable;
    /**
     * @var TopicConfig
     */
    private $topicConfig;
    /**
     * @var Topic
     */
    private $topic;
    /**
     * @var TopicAspect
     */
    private $topicAspect;

    /**
     * TopicPlugin constructor.
     * @param TopicConfig|null $topicConfig
     *
     */
    public function __construct(?TopicConfig $topicConfig = null)
    {
        parent::__construct();
        if ($topicConfig == null) $topicConfig = new TopicConfig();
        $this->topicConfig = $topicConfig;
        $this->atAfter(UidPlugin::class);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new UidPlugin());
    }

    /**
     * @param Context $context
     * @return mixed|void
     */
    public function init(Context $context)
    {
        parent::init($context);
        $aopConfig = DIGet(AopConfig::class);
        $this->topicAspect = new TopicAspect();
        $aopConfig->addAspect($this->topicAspect);
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Topic";
    }

    /**
     * 初始化
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Plugins\Config\ConfigException
     *
     */
    public function beforeServerStart(Context $context)
    {
        $this->topicConfig->merge();
        $uidConfig = DIGet(UidConfig::class);
        $this->topicTable = new Table($this->topicConfig->getCacheTopicCount());
        $this->topicTable->column("topic", Table::TYPE_STRING, $this->topicConfig->getTopicMaxLength());
        $this->topicTable->column("uid", Table::TYPE_STRING, $uidConfig->getUidMaxLength());
        $this->topicTable->create();
        //添加一个TopicProcess进程
        Server::$instance->addProcess($this->topicConfig->getProcessName(), TopicProcess::class, self::processGroupName);
        return;
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     * @throws \Exception
     */
    public function beforeProcessStart(Context $context)
    {
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() == $this->topicConfig->getProcessName()) {
            //topic进程
            $this->topic = new Topic($this->topicTable);
            $this->setToDIContainer(Topic::class, $this->topic);
        }
        $this->ready();
    }
}