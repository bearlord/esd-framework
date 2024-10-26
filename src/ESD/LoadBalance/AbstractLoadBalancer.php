<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\LoadBalance;

/**
 * Class AbstractLoadBalancer
 * @package ESD\LoadBalance
 */
abstract class AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * AbstractLoadBalancer constructor.
     * @param Node[] $nodes
     */
    public function __construct(?array $nodes = [])
    {
        $this->nodes = $nodes;
    }

    /**
     * @param Node[] $nodes
     * @return $this
     */
    public function setNodes(array $nodes): LoadBalancerInterface
    {
        $this->nodes = $nodes;
        return $this;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Remove a node from the node list.
     */
    public function removeNode(Node $node): bool
    {
        foreach ($this->nodes as $key => $activeNode) {
            if ($activeNode === $node) {
                unset($this->nodes[$key]);
                return true;
            }
        }
        return false;
    }
}
