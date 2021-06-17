<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Core\Exception;
use ESD\Core\Order\OrderOwnerTrait;
use ESD\Core\Server\Server;
use Go\Aop\Aspect;
use Go\Aop\Features;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Instrument\ClassLoading\SourceTransformingLoader;
use Go\Instrument\PathResolver;
use Go\Instrument\Transformer\SelfValueTransformer;
use ESD\Plugins\Aop\Transformers\FilterInjectorTransformer;
use ESD\Plugins\Aop\Transformers\MemCacheTransformer;
use ESD\Plugins\Aop\Transformers\MemMagicConstantTransformer;
use ESD\Plugins\Aop\Transformers\MemWeavingTransformer;

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

    /**
     * @param array $options
     */
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
    public function init(array $options = [])
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
     * @param array $options
     * @return array
     */
    protected function normalizeOptions(array $options)
    {
        $options = array_replace($this->getDefaultOptions(), $options);

        $options['excludePaths'][] = __DIR__ . '/../../../goaop/';
        $options['excludePaths'][] = __DIR__ . '/../';
        $options['appDir'] = PathResolver::realpath($options['appDir']);
        $options['includePaths'] = PathResolver::realpath($options['includePaths']);
        $options['excludePaths'] = PathResolver::realpath($options['excludePaths']);

        return $options;
    }

    /**
     * @return array|\Closure|\Go\Instrument\Transformer\SourceTransformer[]
     */
    protected function registerTransformers()
    {
        $filterInjector = new FilterInjectorTransformer($this, SourceTransformingLoader::getId());
        $magicTransformer = new MemMagicConstantTransformer($this);
        $aspectKernel = $this;

        $sourceTransformers = function () use ($filterInjector, $magicTransformer, $aspectKernel) {
            $transformers = [];
            if ($aspectKernel->hasFeature(Features::INTERCEPT_INITIALIZATIONS)) {
                $transformers[] = new ConstructorExecutionTransformer();
            }
            if ($aspectKernel->hasFeature(Features::INTERCEPT_INCLUDES)) {
                $transformers[] = $filterInjector;
            }
            $aspectContainer = $aspectKernel->getContainer();
            $transformers[] = new SelfValueTransformer($aspectKernel);
            $transformers[] = new MemWeavingTransformer(
                $aspectKernel,
                $aspectContainer->get('aspect.advice_matcher'),
                $aspectContainer->get('aspect.cached.loader')
            );
            $transformers[] = $magicTransformer;

            return $transformers;
        };

        return [new MemCacheTransformer($this, $sourceTransformers)];
    }


    /**
     * @param AspectContainer $container
     */
    protected function addKernelResourcesToContainer(AspectContainer $container)
    {
        $cid = \Swoole\Coroutine::getuid();
        $trace = ($cid === -1) ? debug_backtrace(
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