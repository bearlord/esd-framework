<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Parallel;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * Class Parallel
 * @package ESD\Parallel
 */
class Parallel
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    /**
     * @var null|Channel
     */
    private $concurrentChannel;

    /**
     * @param int $concurrent if $concurrent is equal to 0, that means unlimit
     */
    public function __construct(int $concurrent = 0)
    {
        if ($concurrent > 0) {
            $this->concurrentChannel = new Channel($concurrent);
        }
    }

    /**
     * @param callable $callable
     * @param null $key
     */
    public function add(callable $callable, $key = null)
    {
        if (is_null($key)) {
            $this->callbacks[] = $callable;
        } else {
            $this->callbacks[$key] = $callable;
        }
    }

    /**
     * @param bool $throw
     * @return array
     */
    public function wait(bool $throw = true): array
    {
        $result = $throwables = [];
        $waitGroup = new WaitGroup();
        $waitGroup->add(count($this->callbacks));

        foreach ($this->callbacks as $key => $callback) {
            $this->concurrentChannel && $this->concurrentChannel->push(true);
            Coroutine::create(function () use ($callback, $key, $waitGroup, &$result, &$throwables) {
                try {
                    $result[$key] = call($callback);
                } catch (\Throwable $throwable) {
                    $throwables[$key] = $throwable;
                } finally {
                    $this->concurrentChannel && $this->concurrentChannel->pop();
                    $waitGroup->done();
                }
            });
        }
        $waitGroup->wait();

        if ($throw && ($throwableCount = count($throwables)) > 0) {
            $message = 'Detecting ' . $throwableCount . ' throwable occurred during parallel execution:' . PHP_EOL . $this->formatThrowables($throwables);
            $exception = new Exception($message);
            $exception->setResults($result);
            $exception->setThrowables($throwables);
            throw $exception;
        }
        return $result;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->callbacks);
    }

    /**
     * Clear callbacks
     */
    public function clear(): void
    {
        $this->callbacks = [];
    }

    /**
     * Format throwables into a nice list.
     *
     * @param \Throwable[] $throwables
     */
    private function formatThrowables(array $throwables): string
    {
        $output = '';
        foreach ($throwables as $key => $value) {
            $output .= \sprintf('(%s) %s: %s' . PHP_EOL . '%s' . PHP_EOL, $key, get_class($value), $value->getMessage(), $value->getTraceAsString());
        }
        return $output;
    }
}