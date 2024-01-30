<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

use ESD\Core\Context\ContextManager;
use ESD\Core\DI\DI;
use ESD\Core\Runtime;
use ESD\Parallel\Parallel;
use Psr\Log\LoggerInterface;

/**
 * Global enable runtime coroutine
 *
 * @param bool $enable
 * @param int $flags
 * @return bool
 * @throws \ErrorException
 */
function enableRuntimeCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL): bool
{
    if (!$enable) {
        \Swoole\Runtime::enableCoroutine($enable);
        return true;
    }

    if (Runtime::$enableCoroutine) {
        \Swoole\Runtime::enableCoroutine($flags);
    }
    return true;
}

/**
 * Server serialize
 *
 * @param $data
 * @return string
 */
function serverSerialize($data): string
{
    return serialize($data);
}

/**
 * Server unserialize
 *
 * @param string $data
 * @return mixed
 */
function serverUnSerialize(string $data)
{
    return unserialize($data);
}

/**
 * Add timer tick
 *
 * @param int $msec
 * @param callable $callback
 * @param array $params
 * @return int
 */
function addTimerTick(int $msec, callable $callback, ... $params): int
{
    return \Swoole\Timer::tick($msec, $callback, ...$params);
}

/**
 * Clear timer tick
 *
 * @param int $timerId
 * @return bool
 */
function clearTimerTick(int $timerId): bool
{
    return \Swoole\Timer::clear($timerId);
}

/**
 * Add timer event
 *
 * @param int $msec
 * @param callable $callback
 * @param array $params
 * @return int
 */
function addTimerAfter(int $msec, callable $callback, ... $params): int
{
    return \Swoole\Timer::after($msec, $callback, ...$params);
}

/**
 * Extent parent's context
 *
 * @param callable $run
 * @return false|int|void
 */
function goWithContext(callable $run)
{
    if (Runtime::$enableCoroutine) {
        $context = getContext();
        return \Swoole\Coroutine::create(function () use ($run, $context) {
            $currentContext = getContext();
            //Reset parent context
            $currentContext->setParentContext($context);
            try {
                $run();
            } catch (Throwable $e) {
                DIGet(LoggerInterface::class)->error($e);
            }
        });
    } else {
        $run();
    }
}

/**
 * Get context
 *
 * @return \ESD\Core\Context\Context|null
 */
function getContext(): ?\ESD\Core\Context\Context
{
    return ContextManager::getInstance()->getContext();
}

/**
 * Set context value
 *
 * @param string $key
 * @param $value
 * @return mixed
 */
function setContextValue(string $key, $value)
{
    getContext()->add($key, $value);
}

/**
 * Get context value
 *
 * @param string $key
 * @return mixed
 */
function getContextValue(string $key)
{
    return getContext()->get($key);
}

/**
 * @param string $key
 * @return bool
 */
function deleteContextValue(string $key): bool
{
    return getContext()->delete($key);
}

/**
 * Set context value with class
 *
 * @param string $key
 * @param $value
 * @param string $class
 * @return mixed
 */
function setContextValueWithClass(string $key, $value, string $class)
{
    getContext()->addWithClass($key, $value, $class);
}

/**
 * Get context value by class name
 *
 * @param $key
 * @return mixed
 */
function getContextValueByClassName($key)
{
    return getContext()->getByClassName($key);
}

/**
 * Get deep context value
 *
 * @param $key
 * @return mixed
 */
function getDeepContextValue($key)
{
    return getContext()->getDeep($key);
}

/**
 * Get deep context value by class name
 * @param $key
 * @return mixed
 */
function getDeepContextValueByClassName($key)
{
    return getContext()->getDeepByClassName($key);
}

/**
 * Clear directory
 *
 * @param string|null $path
 */
function clearDir(?string $path = null)
{
    if (is_dir($path)) {
        $p = scandir($path);
        foreach ($p as $value) {
            if ($value != '.' && $value != '..') {
                if (is_dir($path . '/' . $value)) {
                    clearDir($path . '/' . $value);
                    rmdir($path . '/' . $value);
                } else {
                    unlink($path . '/' . $value);
                }
            }
        }
    }
}

/**
 * DI get
 *
 * @param string $name
 * @param array|null $params
 * @return mixed
 * @throws \Exception
 */
function DIGet(string $name, ?array $params = [])
{
    return DI::getInstance()->get($name, $params);
}

/**
 * DI Set
 *
 * @param string $name
 * @param $value
 * @return void
 * @throws \Exception
 */
function DISet(string $name, $value)
{
    DI::getInstance()->set($name, $value);
}


/**
 * Call a callback with the arguments.
 *
 * @param mixed $callback
 * @return null|mixed
 */
function call($callback, array $args = [])
{
    $result = null;
    if ($callback instanceof \Closure) {
        $result = $callback(...$args);
    } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
        $result = $callback(...$args);
    } elseif (is_array($callback)) {
        [$object, $method] = $callback;
        $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args);
    } else {
        $result = call_user_func_array($callback, $args);
    }
    return $result;
}

if (!function_exists('parallel')) {
    /**
     * @param callable[] $callables
     * @param int $concurrent if $concurrent is equal to 0, that means unlimit
     */
    function parallel(array $callables, int $concurrent = 0): array
    {
        $parallel = new Parallel($concurrent);
        foreach ($callables as $key => $callable) {
            $parallel->add($callable, $key);
        }
        return $parallel->wait();
    }
}
