<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Go\Core\AspectContainer;
use Go\Instrument\PathResolver;
use ESD\Plugins\Aop\FileSystem\Enumerator;
use ESD\Plugins\Aop\Transformers\FilterInjectorTransformer;

/**
 * Class AopComposerLoader
 * @package rabbit\aop
 */
class AopComposerLoader extends \Go\Instrument\ClassLoading\AopComposerLoader
{
    /** @var bool */
    private static $wasInitialized = false;

    /**
     * AopComposerLoader constructor.
     * @param ClassLoader $original
     * @param AspectContainer $container
     * @param array $options
     */
    public function __construct(ClassLoader $original, AspectContainer $container, array $options = [])
    {
        $this->options = $options;
        $this->original = $original;

        $prefixes = $original->getPrefixes();
        $excludePaths = $options['excludePaths'];

        if (!empty($prefixes)) {
            // Let's exclude core dependencies from that list
            if (isset($prefixes['Dissect'])) {
                $excludePaths[] = $prefixes['Dissect'][0];
            }
            if (isset($prefixes['Doctrine\\Common\\Annotations\\'])) {
                $excludePaths[] = substr($prefixes['Doctrine\\Common\\Annotations\\'][0], 0, -16);
            }
        }

        $fileEnumerator = new Enumerator($options['appDir'], $options['includePaths'], $excludePaths);
        $this->fileEnumerator = $fileEnumerator;
    }

    /**
     * @return array|null
     */
    public function getIncludePath(): ?array
    {
        return $this->options['includePaths'];
    }

    /**
     * @param array $options
     * @param AspectContainer $container
     * @return bool
     */
    public static function init(array $options, AspectContainer $container)
    {
        $loaders = spl_autoload_functions();

        foreach ($loaders as &$loader) {
            $loaderToUnregister = $loader;
            if (is_array($loader) && ($loader[0] instanceof ClassLoader)) {
                $originalLoader = $loader[0];
                // Configure library loader for doctrine annotation loader
                AnnotationRegistry::registerLoader(function ($class) use ($originalLoader) {
                    $originalLoader->loadClass($class);

                    return class_exists($class, false);
                });
                $loader[0] = new AopComposerLoader($loader[0], $container, $options);
                self::$wasInitialized = true;
            }
            spl_autoload_unregister($loaderToUnregister);
        }
        unset($loader);

        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }

        return self::$wasInitialized;
    }

    public static function wasInitialized()
    {
        return self::$wasInitialized;
    }

    /**
     * @param string $class
     */
    public function loadClass($class)
    {
        $file = $this->findFile($class);

        if ($file !== false) {
            if (strpos($file, 'php://') === 0) {
                if (preg_match('/resource=(.+)$/', $file, $matches)) {
                    $file = PathResolver::realpath($matches[1]);
                }
                $aopFile = $this->options['cacheDir'] . '/' . $file;
                if (file_exists($aopFile)) {
                    $file = $aopFile;
                }
            }
            include_once $file;
        }
    }

    /**
     * @param string $class
     * @return false|string
     */
    public function findFile($class)
    {
        static $isAllowedFilter = null, $isProduction = false;
        if (!$isAllowedFilter) {
            $isAllowedFilter = $this->fileEnumerator->getFilter();
            $isProduction = !$this->options['debug'];
        }

        $file = $this->original->findFile($class);

        if ($file !== false) {
            $file = PathResolver::realpath($file) ?: $file;
            if ($isAllowedFilter(new \SplFileInfo($file))) {
                // can be optimized here with $cacheState even for debug mode, but no needed right now
                $file = FilterInjectorTransformer::rewrite($file);
            }
        }

        return $file;
    }

    /**
     * @return Enumerator
     */
    public function getEnumerator(): Enumerator
    {
        return $this->fileEnumerator;
    }
}
