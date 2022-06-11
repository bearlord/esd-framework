<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\LoadBalance\Algorithm;
use ESD\LoadBalance\AbstractLoadBalancer;
use ESD\LoadBalance\Node;

/**
 * Class WeightedRandom
 * @package ESD\LoadBalance\Algorithm
 */
class WeightedRandom extends AbstractLoadBalancer
{
    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        $totalWeight = 0;
        $isSameWeight = true;
        $lastWeight = null;
        $nodes = $this->nodes ?? [];
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }
            $weight = $node->weight;
            $totalWeight += $weight;
            if ($lastWeight !== null && $isSameWeight && $weight !== $lastWeight) {
                $isSameWeight = false;
            }
            $lastWeight = $weight;
        }
        if ($totalWeight > 0 && !$isSameWeight) {
            $offset = mt_rand(0, $totalWeight - 1);
            foreach ($nodes as $node) {
                $offset -= $node->weight;
                if ($offset < 0) {
                    return $node;
                }
            }
        }
        return $nodes[array_rand($nodes)];
    }
}
