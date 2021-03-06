<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

use ESD\Core\Context\ContextManager;
use ESD\Core\DI\DI;
use ESD\Core\Runtime;
use Psr\Log\LoggerInterface;

/**
 * TCP Socket Stream
 */
const HOOK_TCP = SWOOLE_HOOK_TCP;

/**
 * UDP Socket Stream
 */
const HOOK_UDP = SWOOLE_HOOK_UDP;

/**
 * Unix Stream Socket Stream
 */
const HOOK_UNIX = SWOOLE_HOOK_UNIX;

/**
 * Unix Dgram Socket Stream
 */
const HOOK_UDG = SWOOLE_HOOK_UDG;

/**
 * SSL Socket Stream
 */
const HOOK_SSL = SWOOLE_HOOK_SSL;

/**
 * TLS Socket Stream
 */
const HOOK_TLS = SWOOLE_HOOK_TLS;

/**
 * Hook sleep
 */
const HOOK_SLEEP = SWOOLE_HOOK_SLEEP;

/**
 * Hook file
 */
const HOOK_FILE = SWOOLE_HOOK_FILE;

/**
 * Hook blocking function
 */
const HOOK_BLOCKING_FUNCTION = SWOOLE_HOOK_BLOCKING_FUNCTION;

/**
 * Hook all
 */
const HOOK_ALL = SWOOLE_HOOK_ALL;

/**
 * Global enable runtime coroutine
 *
 * @param bool $enable
 * @param int $flags
 */
function enableRuntimeCoroutine(bool $enable = true, int $flags = HOOK_ALL ^ HOOK_FILE)
{
    if (Runtime::$enableCoroutine) {
        if (version_compare(swoole_version(), "4.6.0", "ge")) {
            $flags = SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_FILE ^ SWOOLE_HOOK_SOCKETS;
        }
        \Swoole\Runtime::enableCoroutine($enable, $flags);
    }
}

/**
 * Server serialize
 *
 * @param $data
 * @return string
 */
function serverSerialize($data)
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
function addTimerTick(int $msec, callable $callback, ... $params)
{
    return \Swoole\Timer::tick($msec, $callback, ...$params);
}

/**
 * Clear timer tick
 *
 * @param int $timerId
 * @return bool
 */
function clearTimerTick(int $timerId)
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
function addTimerAfter(int $msec, callable $callback, ... $params)
{
    return \Swoole\Timer::after($msec, $callback, ...$params);
}

/**
 * Extent parent's context
 *
 * @param callable $run
 * @return int
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
 * @return \ESD\Core\Context\Context
 */
function getContext()
{
    return ContextManager::getInstance()->getContext();
}

/**
 * Get context value
 *
 * @param $key
 * @return mixed
 */
function getContextValue($key)
{
    return getContext()->get($key);
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
 * Set context value
 *
 * @param $key
 * @param $value
 * @return mixed
 */
function setContextValue($key, $value)
{
    getContext()->add($key, $value);
}

/**
 * Set context value with class
 *
 * @param $key
 * @param $value
 * @param $class
 * @return mixed
 */
function setContextValueWithClass($key, $value, $class)
{
    getContext()->addWithClass($key, $value, $class);
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
 * @param null $path
 */
function clearDir($path = null)
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
 * @param $name
 * @param array $params
 * @return mixed
 * @throws Exception
 */
function DIGet($name, $params = [])
{
    return DI::getInstance()->get($name, $params);
}

/**
 * DI Set
 *
 * @param $name
 * @param $value
 * @return mixed
 * @throws Exception
 */
function DISet($name, $value)
{
    DI::getInstance()->set($name, $value);
}