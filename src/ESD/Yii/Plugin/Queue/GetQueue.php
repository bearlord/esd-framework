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
        $poolKey = $name;
        $contextKey = sprintf("Queue:%s", $name);

        $queue = getDeepContextValue($contextKey);

        if (empty($queue)) {
            /** @var QueuePools $pools */
            $pools = getDeepContextValueByClassName(QueuePools::class);

            /** @var QueuePool $pool */
            $pool = $pools->getPool($poolKey);

            if ($pool == null) {
                throw new \Exception("No Queue pool named {$poolKey} was found");
            }

            return $pool->handle();
        }

        return $queue;
    }
}