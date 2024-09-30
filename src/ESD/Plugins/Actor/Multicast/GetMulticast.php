<?php

namespace ESD\Plugins\Actor\Multicast;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\ProcessRPC\GetProcessRpc;

trait GetMulticast
{
    use GetProcessRpc;
    use GetLogger;

    /**
     * @var MulticastConfig
     */
    protected $multicastConfig;

    /**
     * Has channel
     * @param string $channel
     * @param string $actor
     * @return bool
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function hasChannel(string $channel, string $actor): bool
    {
        if (empty($actor)) {
            $this->warn("Actor is empty");
            return false;
        }

        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class);
        return $rpcProxy->hasChannel($channel, $actor);
    }

    /**
     * Delete channel
     *
     * @param string $channel
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function deleteChannel(string $channel)
    {
        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->deleteChannel($channel);
    }

    /**
     * @return MulticastConfig|mixed
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
     * Subscribe
     *
     * @param string $channel
     * @param string $actor
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function actorSubscribe(string $channel, string $actor)
    {
        if (empty($actor)) {
            $this->warn("Actor is empty");
            return;
        }

        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->subscribe($channel, $actor);
    }

    /**
     * Unsubscribe
     *
     * @param string $channel
     * @param string $actor
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function actorUnsubscribe(string $channel, string $actor)
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
    public function actorUnsubscribeAll(string $actor)
    {
        if (empty($actor)) {
            $this->warn("Actor is empty");
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
     * @param string|null $message
     * @param array|null $excludeActorList
     * @throws \ESD\Plugins\Actor\ActorException
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function actorPublish(string $channel, ?string $message, ?array $excludeActorList = [])
    {
        /** @var Channel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), Channel::class, true);
        $rpcProxy->publish($channel, $message, $excludeActorList);
    }
}
