<?php
/**
 * Parser Reflection API
 *
 * @copyright Copyright 2015, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ESD\Goaop\ParserReflection\Locator;

use Composer\Autoload\ClassLoader;
use ESD\Goaop\ParserReflection\Instrument\PathResolver;
use ESD\Goaop\ParserReflection\LocatorInterface;
use ESD\Goaop\ParserReflection\ReflectionException;

/**
 * Locator, that can find a file for the given class name by asking composer
 */
class ComposerLocator implements LocatorInterface
{
    /**
     * @var ClassLoader
     */
    private $loader;

    public function __construct(ClassLoader $loader = null)
    {
        if (!$loader) {
            $loaders = spl_autoload_functions();
            foreach ($loaders as $loader) {
                if (is_array($loader) && $loader[0] instanceof ClassLoader) {
                    $loader = $loader[0];
                    break;
                }
            }
            if (!$loader) {
                throw new ReflectionException("Can not found a correct composer loader");
            }
        }
        $this->loader = $loader;
    }

    /**
     * Returns a path to the file for given class name
     *
     * @param string $className Name of the class
     *
     * @return string|false Path to the file with given class or false if not found
     */
    public function locateClass($className)
    {
        $filePath = $this->loader->findFile(ltrim($className, '\\'));
        if (!empty($filePath)) {
            $filePath = PathResolver::realpath($filePath);
        }

        return $filePath;
    }
}
