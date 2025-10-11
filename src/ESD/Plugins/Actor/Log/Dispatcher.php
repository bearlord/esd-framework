<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor\Log;

use ESD\Yii\Base\Component;

class Dispatcher extends Component
{

    /**
     * @var array
     */
    public array $targets = [];

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