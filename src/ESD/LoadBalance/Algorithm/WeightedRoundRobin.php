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
 * Class WeightedRoundRobin
 * @package ESD\LoadBalance\Algorithm
 */
class WeightedRoundRobin extends AbstractLoadBalancer
{
    /**
     * @var int
     */
    private $lastNode = 0;

    /**
     * @var int
     */
    private $currentWeight = 0;

    /**
     * @var int
     */
    private $maxWeight = 0;

    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        $count = count($this->nodes);
        if ($count <= 0) {
            throw new RuntimeException('Nodes missing.');
        }
        $this->maxWeight = $this->maxWeight($this->nodes);
        while (true) {
            $this->lastNode = ($this->lastNode + 1) % $count;
            if ($this->lastNode === 0) {
                $this->currentWeight = $this->currentWeight - $this->gcd($this->nodes);
                if ($this->currentWeight <= 0) {
                    $this->currentWeight = $this->maxWeight;
                    if ($this->currentWeight == 0) {
                        // Degrade to random algorithm.
                        return $this->nodes[array_rand($this->nodes)];
                    }
                }
            }
            /** @var Node $node */
            $node = $this->nodes[$this->lastNode];
            if ($node->weight >= $this->currentWeight) {
                return $node;
            }
        }
    }

    /**
     * Calculate the max weight of nodes.
     */
    private function maxWeight(iterable $nodes): int
    {
        $max = null;
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }
            if ($max === null) {
                $max = $node->weight;
            } else {
                $max = max($max, $node->weight);
            }
        }
        return $max;
    }

    /**
     * Calculate the gcd of nodes.
     */
    private function gcd(iterable $nodes): int
    {
        $x = $y = null;
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }
            if ($x === null) {
                $x = $node->weight;
                continue;
            }
            $y = $node->weight;
            $x = self::_gcd($x, $y);
        }
        return $x;
    }

    /**
     * Greatest common divisor - recursive Euclid's algorithm
     * The largest positive integer that divides the numbers without a remainder.
     * For example, the GCD of 8 and 12 is 4.
     * https://en.wikipedia.org/wiki/Greatest_common_divisor
     *
     * gcd(a, 0) = a
     * gcd(a, b) = gcd(b, a mod b)
     *
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    private static function _gcd(int $a, int $b): int
    {
        // Base cases
        if ($a == 0) {
            return $b;
        }
        if ($b == 0) {
            return $a;
        }

        // Recursive case
        return self::_gcd($b, $a % $b);
    }
}
