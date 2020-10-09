<?php
/**
 * ESD Yii Queue plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Queue;

use ESD\Yii\Queue\Cli\Queue;
use ESD\Yii\Yii;

/**
 * Trait GetQueue
 * @package ESD\Yii\Plugin\Queue
 */
trait GetQueue
{

    /**
     * @param string $name
     * @return Queue
     */
    public function queue($name = "default")
    {
        if ($name === "default") {
            $poolKey = "default";
            $contextKey = "Queue:default";
        }
        $queue = getContextValue($contextKey);

        if (empty($queue)) {
            //Dev not fishied
            $queue = Yii::createObject([
                'class' => 'ESD\Yii\Queue\Drivers\Redis\Queue'
            ]);
            setContextValue($contextKey, $queue);
        }

        var_dump(get_class($queue));
        return $queue;
    }
}