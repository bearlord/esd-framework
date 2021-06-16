<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop\Transformers;

use Go\Core\AspectKernel;
use Go\Instrument\PathResolver;
use Go\Instrument\Transformer\NodeFinderVisitor;
use Go\Instrument\Transformer\SourceTransformer;
use Go\Instrument\Transformer\StreamMetaData;
use PhpParser\Node\Expr\Include_;
use PhpParser\NodeTraverser;

/**
 * Class FilterInjectorTransformer
 * @package ESD\Plugins\Aop\Transformers
 */
class FilterInjectorTransformer implements SourceTransformer
{
    /**
     * Php filter definition
     */
    const PHP_FILTER_READ = 'php://filter/read=';

    /**
     * Name of the filter to inject
     *
     * @var string
     */
    protected static $filterName;

    /**
     * Kernel options
     *
     * @var array
     */
    protected static $options = [];

    /**
     * @var AspectKernel|null
     */
    protected static $kernel;

    /**
     * FilterInjectorTransformer constructor.
     * @param AspectKernel $kernel
     * @param $filterName
     */
    public function __construct(AspectKernel $kernel, $filterName)
    {
        self::configure($kernel, $filterName);
    }

    /**
     * @param AspectKernel $kernel
     * @param $filterName
     */
    protected static function configure(AspectKernel $kernel, $filterName)
    {
        if (self::$kernel) {
            throw new \RuntimeException('Filter injector can be configured only once.');
        }
        self::$kernel = $kernel;
        self::$options = $kernel->getOptions();
        self::$filterName = $filterName;
    }

    /**
     * Replace source path with correct one
     *
     * This operation can check for cache, can rewrite paths, add additional filters and much more
     *
     * @param string $originalResource Initial resource to include
     * @param string $originalDir Path to the directory from where include was called for resolving relative resources
     *
     * @return string Transformed path to the resource
     */
    public static function rewrite($originalResource, $originalDir = '')
    {
        static $appDir, $debug;
        if (!$appDir) {
            extract(self::$options, EXTR_IF_EXISTS);
        }

        $resource = (string)$originalResource;
        if ($resource[0] !== '/') {
            $shouldCheckExistence = true;
            $resource
                = PathResolver::realpath($resource, $shouldCheckExistence)
                ?: PathResolver::realpath("{$originalDir}/{$resource}", $shouldCheckExistence)
                    ?: $originalResource;
        }
        $cachedResource = str_replace($appDir, '', $resource);

        // If the cache is disabled or no cache yet, then use on-fly method
        if ($debug || !file_exists($cachedResource)) {
            return self::PHP_FILTER_READ . self::$filterName . '/resource=' . $resource;
        }

        return $cachedResource;
    }

    /**
     * Wrap all includes into rewrite filter
     *
     * @param StreamMetaData $metadata Metadata for source
     * @return string See RESULT_XXX constants in the interface
     */
    public function transform(StreamMetaData $metadata)
    {
        $includeExpressionFinder = new NodeFinderVisitor([Include_::class]);

        // TODO: move this logic into walkSyntaxTree(Visitor $nodeVistor) method
        $traverser = new NodeTraverser();
        $traverser->addVisitor($includeExpressionFinder);
        $traverser->traverse($metadata->syntaxTree);

        /** @var Include_[] $includeExpressions */
        $includeExpressions = $includeExpressionFinder->getFoundNodes();

        if (empty($includeExpressions)) {
            return self::RESULT_ABSTAIN;
        }

        foreach ($includeExpressions as $includeExpression) {
            $startPosition = $includeExpression->getAttribute('startTokenPos');
            $endPosition = $includeExpression->getAttribute('endTokenPos');

            $metadata->tokenStream[$startPosition][1] .= ' \\' . __CLASS__ . '::rewrite(';
            if ($metadata->tokenStream[$startPosition + 1][0] === T_WHITESPACE) {
                unset($metadata->tokenStream[$startPosition + 1]);
            }

            $metadata->tokenStream[$endPosition][1] .= ', __DIR__)';
        }

        return self::RESULT_TRANSFORMED;
    }
}
