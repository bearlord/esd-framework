<?php
/**
 * ESD Yii Queue plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Queue;

/**
 * Trait GetQueue
 * @package ESD\Yii\Plugin\Queue
 */
trait GetQueue
{

    /**
     * @param string $name
     */
    public function queue($name = "default")
    {
        if ($name === "default") {
            $poolKey = "default";
            $contextKey = "Queue:default";
        }
        $queue = getContextValue($contextKey);


        return $queue;
    }
}