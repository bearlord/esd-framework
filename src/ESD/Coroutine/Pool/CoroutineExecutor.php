<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Pool;

/**
 * Class CoroutineExecutor
 * @package ESD\Coroutine\Pool
 */
class CoroutineExecutor implements Executor
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @return CoroutineExecutor
     */
    public static function getInstance(){
        if(self::$instance==null){
            self::$instance = new CoroutineExecutor();
        }
        return self::$instance;
    }

    /**
     * @param $runnable
     */
    public function execute($runnable)
    {
        goWithContext(function ()use ($runnable) {
            if ($runnable instanceof Runnable) {
                $result = $runnable->run();
                $runnable->sendResult($result);
            }
            if (is_callable($runnable)) {
                $runnable();
            }
        });
    }
}