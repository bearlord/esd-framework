<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Transporter;

use ESD\LoadBalance\LoadBalancerInterface;

/**
 * Interface TransporterInterface
 * @package ESD\Plugins\JsonRpc\Transporter
 */
interface TransporterInterface
{
    public function send(string $data);

    public function recv();

    public function getLoadBalancer(): ?LoadBalancerInterface;

    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface;
}