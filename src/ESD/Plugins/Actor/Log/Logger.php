<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor\Log;

class Logger
{

    /**
     * @var array
     */
    public array $messages = [];

    /**
     * @var int 
     */
    public int $flushInterval = 1;

    /**
     * @var Dispatcher|null the message dispatcher
     */
    public ?Dispatcher $dispatcher = null;


    public function log($message)
    {
        $this->messages[] = $message;

        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }


    public function flush(?bool $final = false)
    {
        $messages = $this->messages;

        $this->messages = [];

        if ($this->dispatcher instanceof Dispatcher) {
            $this->dispatcher->dispatch($messages, $final);
        }
    }


}