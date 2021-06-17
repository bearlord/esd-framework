<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Aop\Transformers;

use Go\Aop\Advisor;
use Go\Aop\Aspect;
use Go\Aop\Framework\AbstractJoinpoint;
use Go\Core\AdviceMatcher;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Core\AspectLoader;
use Go\Instrument\Transformer\BaseSourceTransformer;
use Go\Instrument\Transformer\StreamMetaData;
use Go\ParserReflection\ReflectionClass;
use Go\ParserReflection\ReflectionFile;
use Go\ParserReflection\ReflectionFileNamespace;
use Go\ParserReflection\ReflectionMethod;
use Go\Proxy\ClassProxy;
use Go\Proxy\TraitProxy;
use rabbit\aop\FunctionProxy;

/**
 * Class WeavingTransformer
 * @package rabbit\aop\Transformers
 */
class MemWeavingTransformer extends BaseSourceTransformer
{
    /**
     * @var AdviceMatcher
     */
    protected $adviceMatcher;
    /**
     * Instance of aspect loader
     *
     * @var AspectLoader
     */
    protected $aspectLoader;

    /**
     * Constructs a weaving transformer
     *
     * @param AspectKernel $kernel Instance of aspect kernel
     * @param AdviceMatcher $adviceMatcher Advice matcher for class
     * @param AspectLoader $loader Loader for aspects
     */
    public function __construct(
        AspectKernel $kernel,
        AdviceMatcher $adviceMatcher,
        AspectLoader $loader
    ) {
        parent::__construct($kernel);
        $this->adviceMatcher = $adviceMatcher;
        $this->aspectLoader = $loader;
    }

    /**
     * This method may transform the supplied source and return a new replacement for it
     *
     * @param StreamMetaData $metadata Metadata for source
     * @return string See RESULT_XXX constants in the interface
     */
    public function transform(StreamMetaData $metadata)
    {
        $totalTransformations = 0;
        $parsedSource = new ReflectionFile($metadata->uri, $metadata->syntaxTree);

        // Check if we have some new aspects that weren't loaded yet
        $unloadedAspects = $this->aspectLoader->getUnloadedAspects();
        if (!empty($unloadedAspects)) {
            $this->loadAndRegisterAspects($unloadedAspects);
        }
        $advisors = $this->container->getByTag('advisor');

        $namespaces = $parsedSource->getFileNamespaces();

        foreach ($namespaces as $namespace) {
            $classes = $namespace->getClasses();
            foreach ($classes as $class) {
                // Skip interfaces and aspects
                if ($class->isInterface() || in_array(Aspect::class, $class->getInterfaceNames())) {
                    continue;
                }
                $wasClassProcessed = $this->processSingleClass($advisors, $metadata, $class);
                $totalTransformations += (integer)$wasClassProcessed;
            }
            $wasFunctionsProcessed = $this->processFunctions($advisors, $metadata, $namespace);
            $totalTransformations += (integer)$wasFunctionsProcessed;
        }

        $result = ($totalTransformations > 0) ? self::RESULT_TRANSFORMED : self::RESULT_ABSTAIN;

        return $result;
    }

    /**
     * Utility method to load and register unloaded aspects
     *
     * @param array $unloadedAspects List of unloaded aspects
     */
    private function loadAndRegisterAspects(array $unloadedAspects)
    {
        foreach ($unloadedAspects as $unloadedAspect) {
            $this->aspectLoader->loadAndRegister($unloadedAspect);
        }
    }

    /**
     * Performs weaving of single class if needed
     *
     * @param array|Advisor[] $advisors
     * @param StreamMetaData $metadata Source stream information
     * @param ReflectionClass $class Instance of class to analyze
     *
     * @return bool True if was class processed, false otherwise
     */
    private function processSingleClass(array $advisors, StreamMetaData $metadata, ReflectionClass $class)
    {
        $advices = $this->adviceMatcher->getAdvicesForClass($class, $advisors);

        if (empty($advices)) {
            // Fast return if there aren't any advices for that class
            return false;
        }

        // Sort advices in advance to keep the correct order in cache
        foreach ($advices as &$typeAdvices) {
            foreach ($typeAdvices as &$joinpointAdvices) {
                if (is_array($joinpointAdvices)) {
                    $joinpointAdvices = AbstractJoinpoint::sortAdvices($joinpointAdvices);
                }
            }
        }

        // Prepare new class name
        $newClassName = $class->getShortName() . AspectContainer::AOP_PROXIED_SUFFIX;

        // Replace original class name with new
        $this->adjustOriginalClass($class, $advices, $metadata, $newClassName);

        // Prepare child Aop proxy
        $child = $class->isTrait()
            ? new TraitProxy($class, $advices)
            : new ClassProxy($class, $advices);

        // Set new parent name instead of original
        $child->setParentName($newClassName);
        $contentToInclude = $this->saveProxyToCache($class, $child);

        // Get last token for this class
        $lastClassToken = $class->getNode()->getAttribute('endTokenPos');

        $metadata->tokenStream[$lastClassToken][1] .= PHP_EOL . $contentToInclude;

        return true;
    }

    /**
     * Adjust definition of original class source to enable extending
     *
     * @param ReflectionClass $class Instance of class reflection
     * @param array $advices List of class advices (used to check for final methods and make them non-final)
     * @param StreamMetaData $streamMetaData Source code metadata
     * @param string $newClassName New name for the class
     */
    private function adjustOriginalClass(
        ReflectionClass $class,
        array $advices,
        StreamMetaData $streamMetaData,
        $newClassName
    ) {
        $classNode = $class->getNode();
        $position = $classNode->getAttribute('startTokenPos');
        do {
            if (isset($streamMetaData->tokenStream[$position])) {
                $token = $streamMetaData->tokenStream[$position];
                // Remove final and following whitespace from the class, child will be final instead
                if ($token[0] === T_FINAL) {
                    unset($streamMetaData->tokenStream[$position], $streamMetaData->tokenStream[$position + 1]);
                }
                // First string is class/trait name
                if ($token[0] === T_STRING) {
                    $streamMetaData->tokenStream[$position][1] = $newClassName;
                    // We have finished our job, can break this loop
                    break;
                }
            }
            ++$position;
        } while (true);

        foreach ($class->getMethods(ReflectionMethod::IS_FINAL) as $finalMethod) {
            if (!$finalMethod instanceof ReflectionMethod || $finalMethod->getDeclaringClass()->name !== $class->name) {
                continue;
            }
            $hasDynamicAdvice = isset($advices[AspectContainer::METHOD_PREFIX][$finalMethod->name]);
            $hasStaticAdvice = isset($advices[AspectContainer::STATIC_METHOD_PREFIX][$finalMethod->name]);
            if (!$hasDynamicAdvice && !$hasStaticAdvice) {
                continue;
            }
            $methodNode = $finalMethod->getNode();
            $position = $methodNode->getAttribute('startTokenPos');
            do {
                if (isset($streamMetaData->tokenStream[$position])) {
                    $token = $streamMetaData->tokenStream[$position];
                    // Remove final and following whitespace from the method, child will be final instead
                    if ($token[0] === T_FINAL) {
                        unset($streamMetaData->tokenStream[$position], $streamMetaData->tokenStream[$position + 1]);
                        break;
                    }
                }
                ++$position;
            } while (true);
        }
    }

    /**
     * Save AOP proxy to the separate file anr returns the php source code for inclusion
     *
     * @param ReflectionClass $class Original class reflection
     * @param string|ClassProxy $child
     *
     * @return string
     */
    private function saveProxyToCache($class, $child)
    {
        $body = '';
        $namespace = $class->getNamespaceName();
        if (!empty($namespace)) {
            $body .= "namespace {$namespace};" . PHP_EOL . PHP_EOL;
        }

        $refNamespace = new ReflectionFileNamespace($class->getFileName(), $namespace);
        foreach ($refNamespace->getNamespaceAliases() as $fqdn => $alias) {
            $aliasSuffix = ($alias !== null) ? " as {$alias}" : '';
            $body .= "use {$fqdn}{$aliasSuffix};" . PHP_EOL;
        }

        $body .= $child;
        return $body;
    }

    /**
     * Performs weaving of functions in the current namespace
     *
     * @param array|Advisor[] $advisors List of advisors
     * @param StreamMetaData $metadata Source stream information
     * @param ReflectionFileNamespace $namespace Current namespace for file
     *
     * @return boolean True if functions were processed, false otherwise
     */
    private function processFunctions(array $advisors, StreamMetaData $metadata, ReflectionFileNamespace $namespace)
    {
        $wasProcessedFunctions = false;
        $functionAdvices = $this->adviceMatcher->getAdvicesForFunctions($namespace, $advisors);
        if (!empty($functionAdvices)) {
            $source = new FunctionProxy($namespace, $functionAdvices);

            $lastTokenPosition = $namespace->getLastTokenPosition();
            $metadata->tokenStream[$lastTokenPosition][1] .= PHP_EOL . $source;
            $wasProcessedFunctions = true;
        }

        return $wasProcessedFunctions;
    }
}
