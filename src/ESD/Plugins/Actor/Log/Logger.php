<?php

namespace ESD\Plugins\Actor\Log;

class Logger
{

    /**
     * @var array
     */
    public $messages = [];

    /**
     * @var int 
     */
    public $flushInterval = 1;

    /**
     * @var Dispatcher the message dispatcher
     */
    public $dispatcher;


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