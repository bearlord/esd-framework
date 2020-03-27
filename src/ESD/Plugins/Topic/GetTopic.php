<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/22
 * Time: 11:52
 */

namespace ESD\Plugins\Topic;

use ESD\Plugins\ProcessRPC\GetProcessRpc;

trait GetTopic
{
    use GetProcessRpc;
    /**
     * @var TopicConfig
     */
    protected $topicConfig;

    /**
     * @param $topic
     * @param $uid
     * @return bool
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function hasTopic($topic, $uid)
    {
        if (empty($uid)) {
            $this->warn("uid is empty");
            return false;
        }
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class);
        return $rpcProxy->hasTopic($topic, $uid);
    }

    /**
     * @param $topic
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function delTopic($topic)
    {
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->delTopic($topic);
    }

    /**
     * @return TopicConfig|mixed
     */
    protected function getTopicConfig()
    {
        if ($this->topicConfig == null) {
            $this->topicConfig = DIGet(TopicConfig::class);
        }
        return $this->topicConfig;
    }

    /**
     * 添加订阅
     * @param $topic
     * @param $uid
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function addSub($topic, $uid)
    {
        if (empty($uid)) {
            $this->warn("uid is empty");
            return;
        }
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->addSub($topic, $uid);
    }

    /**
     * 移除订阅
     * @param $topic
     * @param $uid
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function removeSub($topic, $uid)
    {
        if (empty($uid)) {
            $this->warn("uid is empty");
            return;
        }
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->removeSub($topic, $uid);
    }

    /**
     * 清除Fd的订阅
     * @param $fd
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function clearFdSub($fd)
    {
        if (empty($fd)) {
            $this->warn("fd is empty");
            return;
        }
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->clearFdSub($fd);
    }

    /**
     * 清除Uid的订阅
     * @param $uid
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function clearUidSub($uid)
    {
        if (empty($uid)) {
            $this->warn("uid is empty");
            return;
        }
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->clearUidSub($uid);
    }

    /**
     * @param $topic
     * @param $data
     * @param array $excludeUidList
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function pub($topic, $data, $excludeUidList = [])
    {
        $rpcProxy = $this->callProcessName($this->getTopicConfig()->getProcessName(), Topic::class, true);
        $rpcProxy->pub($topic, $data, $excludeUidList);
    }
}
