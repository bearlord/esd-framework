<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Autostart;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Actor\ActorPlugin;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\Autostart\Annotation\Autostart;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Class AutostartPlugin
 * @package ESD\Plugins\Autostart
 */
class AutostartPlugin extends AbstractPlugin
{
    use GetLogger;

    const PROCESS_NAME = "helper";

    const PROCESS_GROUP_NAME = "HelperGroup";

    public function __construct()
    {
        parent::__construct();

        $this->atAfter(AnnotationsScanPlugin::class);
        $this->atAfter(ActorPlugin::class);
    }

    /**
     * @inheritDoc
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new AnnotationsScanPlugin());
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "Autostart";
    }

    /**
     * @inheritDoc
     */
    public function beforeServerStart(Context $context)
    {
        Server::$instance->addProcess(self::PROCESS_NAME, HelpAutostartProcess::class, self::PROCESS_GROUP_NAME);
    }

    /**
     * @inheritDoc
     */
    public function beforeProcessStart(Context $context)
    {
        //Help process
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::PROCESS_NAME) {
            //Scan annotation
            $scanClass = Server::$instance->getContainer()->get(ScanClass::class);
            $reflectionMethods = $scanClass->findMethodsByAnnotation(Autostart::class);

            $taskList = [];
            foreach ($reflectionMethods as $reflectionMethod) {
                $reflectionClass = $reflectionMethod->getParentReflectClass();
                /** @var Autostart $autostart */
                $autostart = $scanClass->getCachedReader()->getMethodAnnotation($reflectionMethod->getReflectionMethod(), Autostart::class);
                if ($autostart instanceof Autostart) {
                    if (empty($autostart->name)) {
                        $autostart->name = $reflectionClass->getName() . "::" . $reflectionMethod->getName();
                    }

                    $taskList[$autostart->sort] = [
                        'class' => $reflectionClass->getName(),
                        'method' => $reflectionMethod->getName()
                    ];
                }
            }

            if (!empty($taskList)) {
                ksort($taskList);
                foreach ($taskList as $key => $value) {
                    $_class = $value['class'];
                    call_user_func([new $_class, $value['method']]);
                }
            }
        }

        $this->ready();
    }

    protected function customeOrderCallback($reflectionMethodA, $reflectionMethodB)
    {

    }
}