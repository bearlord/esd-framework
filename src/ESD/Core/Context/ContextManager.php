<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Context;

class ContextManager
{
    /**
     * @var ContextManager
     */
    protected static $instance;

    /**
     * @var ContextBuilder[]
     */
    protected $contextStack = [];

    /**
     * ContextManager constructor.
     */
    public function __construct()
    {
        $this->registerContext(new RootContextBuilder());
    }

    /**
     * @param ContextBuilder $contextBuilder
     */
    public function registerContext(ContextBuilder $contextBuilder)
    {
        $this->contextStack[$contextBuilder->getDeep()] = $contextBuilder;
        krsort($this->contextStack);
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ContextManager();
        }
        return self::$instance;
    }

    /**
     * @param $deep
     * @param callable|null $register
     * @return ContextBuilder
     */
    public function getContextBuilder($deep, ?callable $register = null)
    {
        $result = $this->contextStack[$deep] ?? null;
        if ($result == null && $register != null) {
            $result = $register();
            $this->registerContext($result);
        }
        return $result;
    }

    /**
     * 获取当前上下文
     * @return Context|null
     */
    public function getContext()
    {
        $context = null;
        $parentContext = null;
        foreach ($this->contextStack as $contextBuilder) {
            if ($context != null) {
                $parentContext = $contextBuilder->build();
                if ($parentContext != null) {
                    break;
                }
            } else {
                $context = $contextBuilder->build();
            }
        }
        if ($context != null && $context->getParentContext() == null) {
            $context->setParentContext($parentContext);
        }
        return $context ?? new Context($parentContext);
    }
}