<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway;

use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Process\Process;
use ESD\Go\GoApplication;
use ESD\Go\GoController;
use ESD\Go\GoPort;
use ESD\Go\GoProcess;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\AutoReload\AutoReloadPlugin;
use ESD\Plugins\Blade\BladePlugin;
use ESD\Plugins\Cache\CachePlugin;
use ESD\Plugins\Cloud\Gateway\Controller\GatewayController;
use ESD\Plugins\Console\ConsolePlugin;
use ESD\Plugins\PHPUnit\PHPUnitPlugin;
use ESD\Plugins\ProcessRPC\ProcessRPCPlugin;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Plugins\Saber\SaberPlugin;
use ESD\Plugins\Security\SecurityPlugin;
use ESD\Plugins\Session\SessionPlugin;
use ESD\Plugins\Topic\TopicPlugin;
use ESD\Plugins\Uid\UidPlugin;
use ESD\Plugins\Whoops\WhoopsPlugin;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Plugin\YiiPlugin;
use ESD\Yii\Yii;

/**
 * GatewayApplication
 * @package ESD\Plugins\Cloud\Gateway
 */
class GatewayApplication extends Server
{
    use GetLogger;

    /**
     * @var OrderAspect[]
     */
    protected $aspects = [];

    /**
     * Application constructor.
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __construct(ServerConfig $serverConfig = null)
    {
        parent::__construct($serverConfig, GoPort::class, GoProcess::class);
        $this->prepareNormalPlugins();
    }

    /**
     * Prepare normal plugins
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function prepareNormalPlugins()
    {
        $this->addNormalPlugins();
    }

    /**
     * Run
     *
     * @param $mainClass
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function run($mainClass)
    {

        $this->configure();
        $this->getContainer()->get($mainClass);
        $this->start();
    }

    /**
     * Add default plugin
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    protected function addNormalPlugins()
    {
        $this->addPlugin(new ConsolePlugin());
        $this->addPlugin(new YiiPlugin());

        $this->addPlugin(new GatewayPlugin());
        $this->addPlugin(new RedisPlugin());
        $this->addPlugin(new AutoreloadPlugin());
        $this->addPlugin(new AopPlugin());
        $this->addPlugin(new SaberPlugin());
        $this->addPlugin(new WhoopsPlugin());
        $this->addPlugin(new SessionPlugin());
        $this->addPlugin(new CachePlugin());
        $this->addPlugin(new SecurityPlugin());
        $this->addPlugin(new PHPUnitPlugin());
        $this->addPlugin(new ProcessRPCPlugin());
        $this->addPlugin(new UidPlugin());
        $this->addPlugin(new TopicPlugin());
        $this->addPlugin(new BladePlugin());

        //Add aop of Go namespace by default
        $aopConfig = new AopConfig(__DIR__);
        $aopConfig->merge();
    }

    /**
     * @param AbstractPlugin $plugin
     * @throws \ESD\Core\Exception
     */
    public function addPlugin(AbstractPlugin $plugin)
    {
        $this->getPlugManager()->addPlugin($plugin);
    }

    /**
     * All configuration plugins have been initialized
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function configureReady()
    {
        $this->debug(Yii::t('esd', 'Configure ready'));
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function onStart()
    {
        $this->debug(Yii::t('esd', 'Application start'));
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function onShutdown()
    {
        $this->debug(Yii::t('esd', 'Application shutdown'));
    }

    /**
     * @inheritDoc
     * @param Process $process
     * @param int $exit_code
     * @param int $signal
     */
    public function onWorkerError(Process $process, int $exit_code, int $signal)
    {
        return;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function onManagerStart()
    {
        $this->debug(Yii::t('esd', 'Manager process start'));
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function onManagerStop()
    {
        $this->debug(Yii::t('esd', 'Manager process stop'));
    }

    /**
     * Plugin initialization is complete
     * @return mixed
     * @throws \Exception
     */
    public function pluginInitialized()
    {
        $this->addAspects();
    }

    /**
     * Add AOP aspect
     * @return mixed
     * @throws \Exception
     */
    protected function addAspects()
    {
        foreach ($this->aspects as $aspect){
            /** @var AopConfig $aopConfig */
            $aopConfig = DIGet(AopConfig::class);
            $aopConfig->addAspect($aspect);
        }
    }

    /**
     * Add AOP aspect
     * @param OrderAspect $orderAspect
     */
    public function addAspect(OrderAspect $orderAspect)
    {
        $this->aspects[] = $orderAspect;
    }

    /**
     * @param $primarySource
     * @throws \ESD\Core\Exception
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public static function runApp($primarySource)
    {
        $app = new GoApplication();
        $app->run($primarySource);
    }
}