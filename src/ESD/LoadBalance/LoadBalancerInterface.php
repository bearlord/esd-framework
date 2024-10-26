<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\LoadBalance;

/**
 * Interface LoadBalancerInterface
 * @package ESD\LoadBalance
 */
interface LoadBalancerInterface
{
    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node;

    /**
     * @param Node[] $nodes
     * @return $this
     */
    public function setNodes(array $nodes): LoadBalancerInterface;

    /**
     * @return Node[] $nodes
     */
    public function getNodes(): array;

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool;
}
