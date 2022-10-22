<?php

namespace ESD\Plugins\Actor\Multicast;

use ESD\Plugins\ProcessRPC\GetProcessRpc;
use ESD\Plugins\Topic\Topic;
use ESD\Plugins\Topic\TopicConfig;

trait GetMulticast
{
    use GetProcessRpc;

    /**
     * @var MulticastConfig
     */
    protected $multicastConfig;

    /**
     * @param string $channel
     * @param string $actor
     * @return bool
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function hasTopic(string $channel, string $actor)
    {
        if (empty($actor)) {
            $this->warn("Actor is empty");
            return false;
        }
        
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Topic::class);
        return $rpcProxy->hasTopic($channel, $actor);
    }

    /**
     * @param $channel
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function delTopic($channel)
    {
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->delTopic($channel);
    }

    /**
     * @return TopicConfig|mixed
     * @throws \Exception
     */
    protected function getMulticastConfig()
    {
        if ($this->multicastConfig == null) {
            $this->multicastConfig = DIGet(MulticastConfig::class);
        }

        return $this->multicastConfig;
    }

    /**
     * Add subscription
     *
     * @param string $channel
     * @param string $actor
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function subscribe($channel, $actor)
    {
        if (empty($actor)) {
            $this->warn("Uid is empty");
            return;
        }

        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->subscribe($channel, $actor);
    }

    /**
     * Unsubscribe
     *
     * @param $channel
     * @param $actor
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function unsubscribe($channel, $actor)
    {
        if (empty($actor)) {
            $this->warn("Actor is empty");
            return;
        }

        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->unsubscribe($channel, $actor);
    }

    /**
     * Unsubscribe all
     * @param $actor
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function unsubscribeAll(string $actor)
    {
        if (empty($actor)) {
            $this->warn("Uid is empty");
            return;
        }

        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->unsubscribeAll($actor);
    }

    /**
     * Publish subscription
     *
     * @param string $channel
     * @param $message
     * @param array $excludeUidList
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function publish(string $channel, $message, $excludeUidList = [])
    {
        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->publish($channel, $message, $excludeUidList);
    }
}