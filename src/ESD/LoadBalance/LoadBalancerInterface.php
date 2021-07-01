<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
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
    public function setNodes(array $nodes);

    /**
     * @return Node[] $nodes
     */
    public function getNodes(): array;

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool;
}
