<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Core\Exception;
use ESD\Core\Order\OrderOwnerTrait;
use ESD\Server\Coroutine\Server;
use Go\Aop\Aspect;
use Go\Aop\Features;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Instrument\ClassLoading\SourceTransformingLoader;

/**
 * Class ApplicationAspectKernel
 * @package ESD\Plugins\Aop
 */
class ApplicationAspectKernel extends AspectKernel
{
    use OrderOwnerTrait;
    /**
     * @var AopConfig
     */
    private $aopConfig;


    public function setConfig(AopConfig $aopConfig)
    {
        $this->aopConfig = $aopConfig;
    }

    public function initContainer(array $options)
    {
        $this->options = $this->normalizeOptions($options);
        define('AOP_ROOT_DIR', $this->options['appDir']);
        define('AOP_CACHE_DIR', $this->options['cacheDir']);
        $this->container = new $this->options['containerClass'];
        $this->container->set('kernel', $this);
        $this->container->set('kernel.interceptFunctions', $this->hasFeature(Features::INTERCEPT_FUNCTIONS));
        $this->container->set('kernel.options', $this->options);
    }

    /**
     * @param array $options
     * @throws Exception
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function init(array $options = []): void
    {
        if ($this->wasInitialized) {
            return;
        }
        $this->options = $this->normalizeOptions($options);
        /** @var $container AspectContainer */
        $container = $this->container;
        SourceTransformingLoader::register();

        foreach ($this->registerTransformers() as $sourceTransformer) {
            SourceTransformingLoader::addTransformer($sourceTransformer);
        }

        // Register kernel resources in the container for debug mode
        if ($this->options['debug']) {
            $this->addKernelResourcesToContainer($container);
        }

        AopComposerLoader::init($this->options, $container);

        // Register all AOP configuration in the container
        $this->configureAop($container);

        $this->wasInitialized = true;
    }

    /**
     * @param AspectContainer $container
     */
    protected function addKernelResourcesToContainer(AspectContainer $container)
    {
        $cid = \Swoole\Coroutine::getuid();
        $trace = $cid === -1 ? debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS,
            2
        ) : \Swoole\Coroutine::getBackTrace($cid, DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $refClass = new \ReflectionObject($this);

        $container->addResource($trace[1]['file']);
        $container->addResource($refClass->getFileName());
    }

    /**
     * Configure an AspectContainer with advisors, aspects and pointcuts
     *
     * @param AspectContainer $container
     *
     * @return void
     * @throws Exception
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function configureAop(AspectContainer $container)
    {
        foreach ($this->aopConfig->getAspects() as $aspect) {
            $this->addOrder($aspect);
        }
        $this->order();
        foreach ($this->orderList as $order) {
            if ($order instanceof Aspect) {
                //Server::$instance->getLog()->debug("Add {$order->getName()} aspect");
                $this->container->registerAspect($order);
            }
        }
    }

}