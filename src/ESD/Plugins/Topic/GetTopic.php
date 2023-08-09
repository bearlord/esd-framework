<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Topic;

use ESD\Plugins\ProcessRPC\GetProcessRpc;
use ESD\Plugins\ProcessRPC\ProcessRPCException;

/**
 * Trait GetTopic
 * @package ESD\Plugins\Topic
 */
trait GetTopic
{
    use GetProcessRpc;
    
    /**
     * @var TopicConfig
     */
    protected $topicConfig;

    /**
     * @param string $topic
     * @param string $uid
     * @return bool
     * @throws ProcessRPCException
     */
    public function hasTopic(string $topic, string $uid): bool
    {
        if (empty($uid)) {
            $this->warn("Uid is empty");
            return false;
        }

        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class);
        return $rpcProxy->hasTopic($topic, $uid);
    }

    /**
     * @param string $topic
     * @throws ProcessRPCException
     */
    public function delTopic(string $topic)
    {
        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->delTopic($topic);
    }

    /**
     * @return TopicConfig|mixed
     * @throws \Exception
     */
    protected function getTopicConfig()
    {
        if ($this->topicConfig == null) {
            $this->topicConfig = DIGet(TopicConfig::class);
        }
        return $this->topicConfig;
    }

    /**
     * Add subscription
     *
     * @param string $topic
     * @param string $uid
     * @throws ProcessRPCException
     */
    public function addSub(string $topic, string $uid)
    {
        if (empty($uid)) {
            $this->warn("Uid is empty");
            return;
        }

        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->addSub($topic, $uid);
    }

    /**
     * Clear uid's subscription
     *
     * @param string $topic
     * @param string $uid
     * @throws ProcessRPCException
     */
    public function removeSub(string $topic, string $uid)
    {
        if (empty($uid)) {
            $this->warn("Uid is empty");
            return;
        }

        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->removeSub($topic, $uid);
    }

    /**
     * Clear fd's subscription
     *
     * @param int $fd
     * @throws ProcessRPCException
     */
    public function clearFdSub(int $fd)
    {
        if (empty($fd)) {
            $this->warn("Fd is empty");
            return;
        }
        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->clearFdSub($fd);
    }

    /**
     * Clear uid's subscription
     * @param string $uid
     * @throws ProcessRPCException
     */
    public function clearUidSub(string $uid)
    {
        if (empty($uid)) {
            $this->warn("Uid is empty");
            return;
        }

        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->clearUidSub($uid);
    }

    /**
     * Publish subscription
     *
     * @param string $topic
     * @param $data
     * @param array|null $excludeUidList
     * @throws ProcessRPCException
     */
    public function pub(string $topic, $data, ?array $excludeUidList = [])
    {
        /** @var Topic $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->pub($topic, $data, $excludeUidList);
    }
}
