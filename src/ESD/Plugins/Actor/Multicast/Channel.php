<?php

namespace ESD\Plugins\Actor\Multicast;

use Ds\Set;
use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Actor\Actor;
use ESD\Plugins\Actor\ActorMessage;

class Channel
{

    protected $subscribeArr = [];

    /**
     * @var
     */
    protected $channel;

    /**
     * @var Table
     */
    protected $channelTable;

    /**
     * Topic constructor.
     * @param Table $topicTable
     */
    public function __construct(Table $topicTable)
    {
        //Read the table first, because the process may restart
        $this->channelTable = $topicTable;

        foreach ($this->channelTable as $value) {
            $this->addSubscribeFormTable($value['channel'], $value['actor']);
        }
    }

    /**
     * @param $topic
     * @param $actor
     */
    private function addSubscribeFormTable(string $topic, string $actor)
    {
        if (empty($actor)) {
            return;
        }

        if (!isset($this->subscribeArr[$topic])) {
            $this->subscribeArr[$topic] = new Set();
        }

        $this->subscribeArr[$topic]->add($actor);
    }

    /**
     * Has channel
     *
     * @param string $channel
     * @param string $actor
     * @return bool
     */
    public function hasChannel(string $channel, string $actor)
    {
        $set = !empty($this->subscribeArr[$channel]) ? $this->subscribeArr : null;
        if ($set == null) {
            return false;
        }

        return $set->contains($actor);
    }

    /**
     * Publish
     *
     * @param string $channel
     * @param string $message
     * @param $excludeActorList
     * @return void
     * @throws \ESD\Plugins\Actor\ActorException
     */
    public function publish(string $channel, string $message, $excludeActorList = [])
    {
        $tree = $this->buildTrees($channel);

        foreach ($tree as $item) {
            if (isset($this->subscribeArr[$item])) {
                foreach ($this->subscribeArr[$item] as $actor) {
                    if (!in_array($actor, $excludeActorList)) {
                        $this->publishToActor($channel, $actor, $message);
                    }
                }
            }
        }
    }

    /**
     * Publish to actor
     *
     * @param string $channel
     * @param string $actor
     * @param $message
     * @return void
     * @throws \ESD\Plugins\Actor\ActorException
     */
    protected function publishToActor(string $channel, string $actor, $message)
    {
        $actorInstance = Actor::getProxy($actor);

        if (!empty($actorInstance)) {
            $actorMessage = new ActorMessage([
                'channel' => $channel,
                'message' => $message
            ], date("YmdHis").  mt_rand(10000, 99999));
            $actorInstance->sendMessage($actorMessage);
        }
    }

    /**
     * Subscribe
     *
     * @param string $channel
     * @param string $actor
     * @return bool
     * @throws \ESD\Core\Exception
     */
    public function subscribe(string $channel, string $actor)
    {
        Helper::checkChannelFilter($channel);

        $this->addSubscribeFormTable($channel, $actor);

        $this->topicTable->set($channel . $actor, [
            "channel" => $channel,
            "actor" => $actor
        ]);

        $this->debug("{$actor} subscribe $channel");

        return true;
    }

    /**
     * Unsubscribe
     * @param string $channel
     * @param string $actor
     * @return bool
     */
    public function unsubscribe(string $channel, string $actor)
    {
        if (!empty($actor)) {
            return false;
        }

        if (isset($this->subscribeArr[$channel])) {
            $this->subscribeArr[$channel]->remove($actor);

            if ($this->subscribeArr[$channel]->count() == 0) {
                unset($this->subscribeArr[$channel]);
            }
        }

        $this->channelTable->del($channel . $actor);

        $this->debug("{$actor} unsubscribe $channel");

        return true;
    }

    /**
     * Unsubscribe all
     * @param string $actor
     * @return bool
     */
    public function unsubscribeAll(string $actor)
    {
        if (empty($actor)) {
            return false;
        }

        if (!empty($this->subscribeArr)) {
            foreach ($this->subscribeArr as $channel => $subscribe) {
                $this->unsubscribe($channel, $actor);
            }
        }

        return true;
    }

    /**
     * Build a subscription tree, allowing only 5 layers
     *
     * @param $channel
     * @return Set
     */
    protected function buildTrees(string $channel)
    {
        $isSys = false;
        if ($channel[0] == "$") {
            $isSys = true;
        }

        $p = explode("/", $channel);
        $countPlies = count($p);
        $result = new Set();
        if (!$isSys) {
            $result->add("#");
        }

        for ($j = 0; $j < $countPlies; $j++) {
            $a = array_slice($p, 0, $j + 1);
            $arr = [$a];
            $count_a = count($a);
            $value = implode('/', $a);
            $result->add($value . "/#");
            $complete = false;
            if ($count_a == $countPlies) {
                $complete = true;
                $result->add($value);
            }

            for ($i = 0; $i < $count_a; $i++) {
                $temp = [];
                foreach ($arr as $one) {
                    $this->helpReplacePlus($one, $temp, $result, $complete, $isSys);
                }
                $arr = $temp;
            }
        }

        return $result;
    }

}