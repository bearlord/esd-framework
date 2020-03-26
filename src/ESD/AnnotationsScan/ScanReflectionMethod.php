<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\AnnotationsScan;

/**
 * Class ScanReflectionMethod
 * @package ESD\Plugins\AnnotationsScan
 */
class ScanReflectionMethod
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var \ReflectionMethod
     */
    protected $reflectionMethod;

    /**
     * @var \ReflectionClass
     */
    protected $parentReflectClass;

    /**
     * ScanReflectionMethod constructor.
     * @param \ReflectionClass $parentReflectClass
     * @param \ReflectionMethod $reflectionMethod
     */
    public function __construct(\ReflectionClass $parentReflectClass, \ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->parentReflectClass = $parentReflectClass;
        $this->name = $reflectionMethod->name;
    }

    /**
     * Get reflection method
     *
     * @return \ReflectionMethod
     */
    public function getReflectionMethod(): \ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * Get parent reflect class
     *
     * @return \ReflectionClass
     */
    public function getParentReflectClass(): \ReflectionClass
    {
        return $this->parentReflectClass;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->reflectionMethod->getName();
    }
}