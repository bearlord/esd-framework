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
 * Class RoundRobin
 * @package ESD\LoadBalance\Algorithm
 */
class RoundRobin extends AbstractLoadBalancer
{
    /**
     * @var int
     */
    private static $current = 0;

    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        $count = count($this->nodes);
        if ($count <= 0) {
            throw new RuntimeException('Nodes missing.');
        }
        $item = $this->nodes[self::$current % $count];
        ++self::$current;
        return $item;
    }
}
