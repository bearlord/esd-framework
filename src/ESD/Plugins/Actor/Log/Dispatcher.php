<?php

namespace ESD\Plugins\Actor\Log;

class Dispatcher
{

    /**
     * @var array
     */
    public $targets = [];

    private $_logger;

    /**
     * @param $messages
     * @param $final
     * @return void
     */
    public function dispatch($messages, $final)
    {
        /** @var Target $target */
        foreach ($this->targets as $target) {
            if ($target->enabled) {
                try {
                    $target->collect($messages, $final);
                } catch (\Exception $e) {
                    //do nothing
                }
            }
        }
    }


}