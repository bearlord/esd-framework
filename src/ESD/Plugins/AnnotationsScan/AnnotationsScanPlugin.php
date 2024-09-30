<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\AnnotationsScan;

use DI\DependencyException;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use ESD\Core\Context\Context;
use ESD\Core\Exception;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\AnnotationsScan\Annotation\Component;
use ESD\Plugins\Aop\AopPlugin;
use ESD\Yii\Helpers\StringHelper;
use ESD\Yii\Yii;
use ReflectionClass;
use ReflectionException;

/**
 * Class AnnotationsScanPlugin
 * @package ESD\Plugins\AnnotationsScan
 */
class AnnotationsScanPlugin extends AbstractPlugin
{
    use GetLogger;

    /**
     * @var AnnotationsScanConfig|null
     */
    private $annotationsScanConfig;

    /**
     * @var CachedReader
     */
    private $cacheReader;
    /**
     * @var ScanClass
     */
    private $scanClass;

    /**
     * AnnotationsScanPlugin constructor.
     * @param AnnotationsScanConfig|null $annotationsScanConfig
     * @throws DependencyException
     * @throws ReflectionException
     * @throws \DI\NotFoundException
     */
    public function __construct(?AnnotationsScanConfig $annotationsScanConfig = null)
    {
        parent::__construct();
        if ($annotationsScanConfig == null) {
            $annotationsScanConfig = new AnnotationsScanConfig();
        }
        $this->annotationsScanConfig = $annotationsScanConfig;
        $this->atAfter(AopPlugin::class);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws DependencyException
     * @throws Exception
     * @throws ReflectionException
     * @throws \DI\NotFoundException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlugin(new AopPlugin());
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "AnnotationsScan";
    }

    /**
     * Scan PHP
     *
     * @param $dir
     * @param null $files
     * @return array|null
     */
    private function scanPhp($dir, &$files = null)
    {
        if ($files == null) {
            $files = array();
        }
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if ($file != "." && $file != "..") {
                        if (is_dir($dir . "/" . $file)) {
                            $this->scanPhp($dir . "/" . $file, $files);
                        } else {
                            if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
                                $files[] = $dir . "/" . $file;
                            }
                        }
                    }
                }
                closedir($handle);
                return $files;
            }
        } else {
            return $files;
        }
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context)
    {
        return;
    }

    /**
     * Get fully qualified class name from file content in PHP
     *
     * @param $path_to_file
     * @return mixed|string|null
     */
    public function getClassFromFile($path_to_file)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);

        //Start with a blank namespace and class
        $namespace = $class = "";

        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_class = false;

        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {

            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }

            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && ($token[0] == T_CLASS || $token[0] == T_INTERFACE)) {
                $getting_class = true;
            }

            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {
                //If the token is a string or the namespace separator...

                if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED])) {
                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } else if ($token === ';') {
                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }

            //While we're grabbing the class name...
            if ($getting_class === true) {
                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {
                    //Store the token's value as the class name
                    $class = $token[1];
                    //Got what we need, stope here
                    break;
                }
            }
        }
        if (empty($class)) {
            return null;
        }

        //Build the fully-qualified class name and return it
        return $namespace ? $namespace . '\\' . $class : $class;
    }

    /**
     * @param Context $context
     * @throws DependencyException
     * @throws Exception
     * @throws ReflectionException
     * @throws \DI\NotFoundException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeProcessStart(Context $context)
    {
        Server::$instance->getLog()->info("hello world");

        //Add src directory by default
        $this->annotationsScanConfig->addIncludePath(Server::$instance->getServerConfig()->getSrcDir());

        $this->annotationsScanConfig->merge();
        if ($this->annotationsScanConfig->isFileCache()) {
            $cache = new FilesystemCache(
                Server::$instance->getServerConfig()->getCacheDir() . DIRECTORY_SEPARATOR . '_annotations_scan' . DIRECTORY_SEPARATOR,
                '.annotations.cache');
        } else {
            $cache = new ArrayCache();
        }
        $this->cacheReader = new CachedReader(new AnnotationReader(), $cache);
        $this->scanClass = new ScanClass($this->cacheReader);
        $this->setToDIContainer(CachedReader::class, $this->cacheReader);
        $this->setToDIContainer(ScanClass::class, $this->scanClass);
        $paths = array_unique($this->annotationsScanConfig->getIncludePaths());

        foreach ($paths as $path) {
            $files = $this->scanPhp($path);
            foreach ($files as $file) {
                $class = $this->getClassFromFile($file);
                if ($class != null) {
                    if (interface_exists($class) || class_exists($class)) {
                        $reflectionClass = new ReflectionClass($class);
                        $has = $this->cacheReader->getClassAnnotation($reflectionClass, Component::class);

                        //Only those that inherit Component annotations will be scanned
                        if ($has != null) {
                            //View annotations on classes
                            $annotations = $this->cacheReader->getClassAnnotations($reflectionClass);
                            foreach ($annotations as $annotation) {
                                $annotationClass = get_class($annotation);
                                if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                    $this->debug(Yii::t('esd', 'Class annotation {annotationClass} in {class}', [
                                        'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                        'class' => $class
                                    ]));
                                }
                                $this->scanClass->addAnnotationClass($annotationClass, $reflectionClass);
                                $annotationClass = get_parent_class($annotation);
                                if ($annotationClass != Annotation::class) {
                                    if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                        $this->debug(Yii::t('esd', 'Class annotation {annotationClass} in {class}', [
                                            'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                            'class' => $class
                                        ]));
                                    }
                                    $this->scanClass->addAnnotationClass($annotationClass, $reflectionClass);
                                }
                            }

                            //Add annotations in class interfaces
                            $reflectionInterfaces = $reflectionClass->getInterfaces();
                            foreach ($reflectionInterfaces as $reflectionInterface) {
                                $annotations = $this->cacheReader->getClassAnnotations($reflectionInterface);
                                foreach ($annotations as $annotation) {
                                    $annotationClass = get_class($annotation);
                                    if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                        $this->debug(Yii::t('esd', 'Class annotation {annotationClass} in {class}', [
                                            'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                            'class' => $class
                                        ]));
                                    }
                                    $this->scanClass->addAnnotationClass($annotationClass, $reflectionClass);
                                    $annotationClass = get_parent_class($annotation);
                                    if ($annotationClass != Annotation::class) {
                                        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                            $this->debug(Yii::t('esd', 'Class annotation {annotationClass} in {class}', [
                                                'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                                'class' => $class
                                            ]));
                                        }
                                        $this->scanClass->addAnnotationClass($annotationClass, $reflectionClass);
                                    }
                                }
                            }

                            //View method annotations
                            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                                $scanReflectionMethod = new ScanReflectionMethod($reflectionClass, $reflectionMethod);

                                foreach ($reflectionMethod->getDeclaringClass()->getInterfaces() as $reflectionInterface) {
                                    try {
                                        $reflectionInterfaceMethod = $reflectionInterface->getMethod($reflectionMethod->getName());
                                    } catch (\Throwable $e) {
                                        $reflectionInterfaceMethod = null;
                                    }
                                    if ($reflectionInterfaceMethod != null) {
                                        $annotations = $this->cacheReader->getMethodAnnotations($reflectionInterfaceMethod);
                                        foreach ($annotations as $annotation) {
                                            $annotationClass = get_class($annotation);
                                            if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                                $this->debug(Yii::t('esd', 'Method annotation {annotationClass} in {method}', [
                                                    'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                                    'method' => sprintf("%s::%s", $class, $reflectionMethod->name)
                                                ]));
                                            }
                                            $this->scanClass->addAnnotationMethod($annotationClass, $scanReflectionMethod);
                                            $annotationClass = get_parent_class($annotation);
                                            if ($annotationClass != Annotation::class) {
                                                if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                                    $this->debug(Yii::t('esd', 'Method annotation {annotationClass} in {method}', [
                                                        'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                                        'method' => sprintf("%s::%s", $class, $reflectionMethod->name)
                                                    ]));
                                                }
                                                $this->scanClass->addAnnotationMethod($annotationClass, $scanReflectionMethod);
                                            }
                                        }
                                    }
                                }

                                $annotations = $this->cacheReader->getMethodAnnotations($reflectionMethod);
                                foreach ($annotations as $annotation) {
                                    $annotationClass = get_class($annotation);
                                    if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                        $this->debug(Yii::t('esd', 'Method annotation {annotationClass} in {method}', [
                                            'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                            'method' => sprintf("%s::%s", $class, $reflectionMethod->name)
                                        ]));
                                    }
                                    $this->scanClass->addAnnotationMethod($annotationClass, $scanReflectionMethod);
                                    $annotationClass = get_parent_class($annotation);
                                    if ($annotationClass != Annotation::class) {
                                        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessId() == 0) {
                                            $this->debug(Yii::t('esd', 'Method annotation {annotationClass} in {method}', [
                                                'annotationClass' => sprintf("@%s", StringHelper::basename($annotationClass)),
                                                'method' => sprintf("%s::%s", $class, $reflectionMethod->name)
                                            ]));
                                        }
                                        $this->scanClass->addAnnotationMethod($annotationClass, $scanReflectionMethod);
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }
        $this->ready();
    }
}
