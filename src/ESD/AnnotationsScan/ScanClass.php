<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\AnnotationsScan;

use Doctrine\Common\Annotations\CachedReader;
use ReflectionClass;

/**
 * Class ScanClass
 * @package ESD\Plugins\AnnotationsScan
 */
class ScanClass
{
    private $annotationMethod = [];
    private $annotationClass = [];

    /**
     * @var CachedReader
     */
    private $cachedReader;

    /**
     * ScanClass constructor.
     * @param CachedReader $cachedReader
     */
    public function __construct(CachedReader $cachedReader)
    {
        $this->cachedReader = $cachedReader;
    }

    /**
     * Get annotation class
     *
     * @return array
     */
    public function getAnnotationClass(): array
    {
        return $this->annotationClass;
    }

    /**
     * Add annotation class
     *
     * @param $annClass
     * @param ReflectionClass $reflectionClass
     */
    public function addAnnotationClass($annClass, ReflectionClass $reflectionClass)
    {
        if (!array_key_exists($annClass, $this->annotationClass)) {
            $this->annotationClass[$annClass] = [];
        }
        if (!in_array($reflectionClass, $this->annotationClass[$annClass])) {
            $this->annotationClass[$annClass][] = $reflectionClass;
        }
    }

    /**
     * Add annotation method
     * @param string $annClass
     * @param ScanReflectionMethod $reflectionMethod
     */
    public function addAnnotationMethod(string $annClass, ScanReflectionMethod $reflectionMethod)
    {
        if (!array_key_exists($annClass, $this->annotationMethod)) {
            $this->annotationMethod[$annClass] = [];
        }
        if (!in_array($reflectionMethod, $this->annotationMethod[$annClass])) {
            $this->annotationMethod[$annClass][] = $reflectionMethod;
        }
    }

    /**
     * Get related classes by annotating class names
     *
     * @param $annClass
     * @return ReflectionClass[]
     */
    public function findClassesByAnn($annClass)
    {
        return $this->annotationClass[$annClass] ?? [];
    }

    /**
     * Get cache reader
     *
     * @return CachedReader
     */
    public function getCachedReader(): CachedReader
    {
        return $this->cachedReader;
    }

    /**
     * Get related methods by annotating class names
     *
     * @param $annClass
     * @return ScanReflectionMethod[]
     */
    public function findMethodsByAnn($annClass)
    {
        return $this->annotationMethod[$annClass] ?? [];
    }

    /**
     * Get annotation method
     *
     * @return array
     */
    public function getAnnotationMethod(): array
    {
        return $this->annotationMethod;
    }

    /**
     * Get class and interface annotation
     *
     * @param ReflectionClass $class
     * @param $annotationName
     * @return object|null
     */
    public function getClassAndInterfaceAnnotation(ReflectionClass $class, $annotationName)
    {
        $result = $this->cachedReader->getClassAnnotation($class, $annotationName);
        if ($result == null) {
            foreach ($class->getInterfaces() as $reflectionClass) {
                $result = $this->cachedReader->getClassAnnotation($reflectionClass, $annotationName);
                if ($result != null) {
                    return $result;
                }
            }
        }
        return $result;
    }

    /**
     * Get class and interface annotation list
     *
     * @param ReflectionClass $class
     * @return array|mixed
     */
    public function getClassAndInterfaceAnnotations(ReflectionClass $class)
    {
        $result = $this->cachedReader->getClassAnnotations($class);
        foreach ($class->getInterfaces() as $reflectionClass) {
            $result = array_merge($this->cachedReader->getClassAnnotation($reflectionClass), $result);
        }
        return $result;
    }

    /**
     * Get method and interface annotation
     *
     * @param \ReflectionMethod $method
     * @param $annotationName
     * @return mixed
     */
    public function getMethodAndInterfaceAnnotation(\ReflectionMethod $method, $annotationName)
    {
        $result = $this->cachedReader->getMethodAnnotation($method, $annotationName);
        if ($result == null) {
            foreach ($method->getDeclaringClass()->getInterfaces() as $reflectionClass) {
                try {
                    $reflectionMethod = $reflectionClass->getMethod($method->getName());
                } catch (\Throwable $e) {
                    $reflectionMethod = null;
                }
                if ($reflectionMethod != null) {
                    $result = $this->cachedReader->getMethodAnnotation($reflectionMethod, $annotationName);
                    if ($result != null) {
                        return $result;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get method and interface annotation list
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    public function getMethodAndInterfaceAnnotations(\ReflectionMethod $method)
    {
        $result = $this->cachedReader->getMethodAnnotations($method);
        foreach ($method->getDeclaringClass()->getInterfaces() as $reflectionClass) {
            try {
                $reflectionMethod = $reflectionClass->getMethod($method->getName());
            } catch (\Throwable $e) {
                $reflectionMethod = null;
            }
            if ($reflectionMethod != null) {
                $result = array_merge($result, $this->cachedReader->getMethodAnnotations($reflectionMethod));
            }
        }
        return $result;
    }
}