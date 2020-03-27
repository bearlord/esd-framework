<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Pool;

/**
 * Interface Executor
 * @package ESD\Coroutine\Pool
 */
interface Executor
{
    /**
     * Execute task
     *
     * @param $runnable
     * @return mixed
     */
    public function execute($runnable);
}