<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Aop;

use Go\Instrument\Transformer\StreamMetaData;

/**
 * Class Aop
 * @package ESD\Aop
 */
class Aop
{
    /**
     * Aop constructor.
     * @param string $kernel
     * @param array $aspects
     * @param array $options
     */
    public function __construct(string $kernel, array $aspects, array $options)
    {
        /** @var AbstractAopKernel $kernelInstance */
        $kernelInstance = $kernel::getInstance();

        $kernelInstance->setAspects($aspects);
        if (!isset($options['cacheDir'])) {
            $options['cacheDir'] = sys_get_temp_dir();
        }
        $kernelInstance->init($options);

        $this->bootStrap($options['cacheDir']);
    }

    /**
     * BootStrap
     * @param string $cacheDir
     * @throws \Exception
     */
    private function bootStrap(string $cacheDir): void
    {
        return;
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            /** @var AopComposerLoader $item */
            foreach ($loader as $item) {
                if (($item instanceof AopComposerLoader) === false) {
                    continue;
                }

                if (empty($item->getIncludePath())) {
                    continue;
                }

                foreach ($item->getEnumerator()->enumerate() as $file) {
                    $contents = file_get_contents($file);
                    $class = $this->getClassByString($contents);
                    if (empty($class)) {
                        continue;
                    }

                    $aopFile = $item->findFile($class);
                    if (strpos($aopFile, 'php://') === 0) {
                        if (($fp = fopen($file, 'r')) === false) {
                            throw new \InvalidArgumentException("Unable to open file: {$fileName}");
                        }
                        $context = fread($fp, filesize($file));
                        $metadata = new StreamMetaData($fp, $context);
                        fclose($fp);
                        SourceTransformingLoader::transformCode($metadata);
                        $context = $metadata->source;
                        $aopClass = $this->getClassByString($context);
                        if (strpos($aopClass, '__AopProxied') !== false) {
                            $cacheFile = $cacheDir . '/' . $file->getPathname();
                            self::createDirectory(dirname($cacheFile), 0777);

                            if (!file_put_contents($cacheFile, $context)) {
                                new \InvalidArgumentException("Unable to write file: {$cacheFile}");
                            }
                        }
                    }
                }

            }
        }
    }

    /**
     * @param string $contents
     * @return string
     */
    public function getClassByString(string $contents): string
    {
        //Start with a blank namespace and class
        $namespace = $class = "";

        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $gettingNamespace = $gettingClass = false;

        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {
            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $gettingNamespace = true;
            }

            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $gettingClass = true;
            }

            //While we're grabbing the namespace name...
            if ($gettingNamespace === true) {
                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } else {
                    if ($token === ';') {
                        //If the token is the semicolon, then we're done with the namespace declaration
                        $gettingNamespace = false;
                    }
                }
            }

            //While we're grabbing the class name...
            if ($gettingClass === true) {
                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {
                    //Store the token's value as the class name
                    $class = $token[1];
                    //Got what we need, stope here
                    break;
                }
            }
        }

        //Build the fully-qualified class name and return it
        return $namespace ? $namespace . '\\' . $class : $class;
    }

    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     * @throws \Exception
     */
    public static function createDirectory(string $path, int $mode = 0775, bool $recursive = true): bool
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {
                throw new \Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new \Exception(
                "Failed to change permissions for directory \"$path\": " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}