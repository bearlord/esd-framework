<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Context;

/**
 * Class Context
 * @package ESD\Core\Context
 */
class Context
{
    const storageKey = "@context";

    /**
     * @var array
     */
    private $contain = [];

    /**
     * @var array
     */
    private $classContain = [];

    /**
     * @var Context
     */
    private $parentContext;

    /**
     * Context constructor.
     *
     * @param Context|null $parentContext
     */
    public function __construct(?Context $parentContext = null)
    {
        $this->parentContext = $parentContext;
    }

    /**
     * Add
     *
     * @param $name
     * @param $value
     */
    public function add($name, $value)
    {
        if ($value == null) {
            return;
        }
        $this->contain[$name] = $value;
        if (!is_string($value) && !is_int($value) && !is_bool($value) && !is_float($value) && !is_double($value) && !is_array($value) && !is_callable($value) && !is_long($value)) {
            $this->classContain[get_class($value)] = $value;
        }
    }

    /**
     * Add with class
     *
     * @param $name
     * @param $class
     * @param $value
     */
    public function addWithClass($name, $value, $class)
    {
        if ($value == null) {
            return;
        }
        $this->contain[$name] = $value;

        if (class_exists($class)) {
            $this->classContain[$class] = $value;
        } else {
            if (!is_string($value) && !is_int($value) && !is_bool($value) && !is_float($value) && !is_double($value) && !is_array($value) && !is_callable($value) && !is_long($value)) {
                $this->classContain[get_class($value)] = $value;
            }
        }
    }

    /**
     * Get by class name
     *
     * @param $className
     * @return mixed|null
     */
    public function getByClassName($className)
    {
        return $this->classContain[$className] ?? null;
    }

    /**
     * Get deep by class name
     *
     * @param $className
     * @return mixed|null
     */
    public function getDeepByClassName($className)
    {
        $result = $this->classContain[$className] ?? null;
        if ($result == null && $this->parentContext != null) {
            return $this->parentContext->getDeepByClassName($className);
        }
        return $result;
    }

    /**
     * Get
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        return $this->contain[$name] ?? null;
    }

    /**
     * Get deep
     *
     * @param $name
     * @return null
     */
    public function getDeep($name)
    {
        $result = $this->contain[$name] ?? null;
        if ($result == null && $this->parentContext != null) {
            return $this->parentContext->getDeep($name);
        }
        return $result;
    }

    /**
     * @param Context $parentContext
     */
    public function setParentContext(?Context $parentContext): void
    {
        if ($parentContext === $this) {
            return;
        }
        $this->parentContext = $parentContext;
    }

    /**
     * @return Context|null
     */
    public function getParentContext()
    {
        return $this->parentContext;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->contain);
    }
}