<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\SaberCloud;


use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\Cloud\SaberCloud\Annotation\SaberClient;
use ESD\Server\Co\Server;

class SaberCloudPlugin extends AbstractPlugin
{
    use GetLogger;
    /**
     * @var SaberCloudConfig|null
     */
    private $saberCloudConfig;

    /**
     * SaberCloudPlugin constructor.
     * @param SaberCloudConfig|null $saberCloudConfig
     * @throws \ReflectionException
     */
    public function __construct(?SaberCloudConfig $saberCloudConfig = null)
    {
        parent::__construct();
        if ($saberCloudConfig == null) {
            $saberCloudConfig = new SaberCloudConfig();
        }
        $this->saberCloudConfig = $saberCloudConfig;
        $this->atAfter(AnnotationsScanPlugin::class);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AnnotationsScanPlugin());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "SaberCloudPlugin";
    }

    /**
     * 初始化
     * @param Context $context
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        $this->saberCloudConfig->merge();
        return;
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     * @throws SaberCloudException
     */
    public function beforeProcessStart(Context $context)
    {
        /** @var ScanClass $scanClass */
        $scanClass = DIGet(ScanClass::class);
        $clients = $scanClass->findClassesByAnn(SaberClient::class);
        foreach ($clients as $client) {
            DISet($client->getName(), new SaberClientProxy($client));
            if (Server::$instance->getProcessManager()->getCurrentProcessId() == 0) {
                $this->debug("Register a SaberClient {$client->getName()}");
            }
        }
        $this->ready();
    }
}