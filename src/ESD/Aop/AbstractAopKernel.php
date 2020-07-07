<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Aop;


use Go\Aop\Features;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Instrument\PathResolver;
use Go\Instrument\Transformer\ConstructorExecutionTransformer;
use Go\Instrument\Transformer\SelfValueTransformer;
use ESD\Aop\Transformers\FilterInjectorTransformer;
use ESD\Aop\Transformers\MemCacheTransformer;
use ESD\Aop\Transformers\MemMagicConstantTransformer;
use ESD\Aop\Transformers\MemWeavingTransformer;

/**
 * Class AbstractAopKernel
 * @package ESD\Aop
 */
abstract class AbstractAopKernel extends AspectKernel
{
    /**
     * @var array
     */
    protected $aspects = [];

    /**
     * @param array $aspects
     * @return AbstractAopKernel
     */
    public function setAspects(array $aspects): self
    {
        $this->aspects = $aspects;
        return $this;
    }

    /**
     * @param array $options
     */
    public function init(array $options = [])
    {
        if ($this->wasInitialized) {
            return;
        }

        $this->options = $this->normalizeOptions($options);
        define('AOP_ROOT_DIR', $this->options['appDir']);

        /** @var AspectContainer $container */
        $container = $this->container = new $this->options['containerClass'];
        $container->set('kernel', $this);
        $container->set('kernel.interceptFunctions', $this->hasFeature(Features::INTERCEPT_FUNCTIONS));
        $container->set('kernel.options', $this->options);

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
            $transformers[] = new MemWeavingTransformer($aspectKernel,
                $aspectContainer->get('aspect.advice_matcher'),
                $aspectContainer->get('aspect.cached.loader'));
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
        $cid = \Co::getuid();
        $trace = $cid === -1 ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,
            2) : \Co::getBackTrace($cid, DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $refClass = new \ReflectionObject($this);

        $container->addResource($trace[1]['file']);
        $container->addResource($refClass->getFileName());
    }

    /**
     * @param AspectContainer $container
     */
    protected function configureAop(AspectContainer $container)
    {
        foreach ($this->aspects as $aspect) {
            $container->registerAspect($aspect);
        }
    }
}