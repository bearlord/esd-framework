<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Transporter;

/**
 * Class JsonRpcHttpTransporter
 * @package ESD\Plugins\JsonRpc\Transporter
 */
class JsonRpcHttpTransporter implements TransporterInterface
{
    /**
     * @var float
     */
    public $connectTimeout = 5;

    /**
     * @var float
     */
    public $recvTimeout = 5;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var array
     */
    public $nodes = [];



    public function send(string $data)
    {
        // TODO: Implement send() method.
    }

    public function recv()
    {
        // TODO: Implement recv() method.
    }

    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        // TODO: Implement getLoadBalancer() method.
    }

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        // TODO: Implement setLoadBalancer() method.
    }


}