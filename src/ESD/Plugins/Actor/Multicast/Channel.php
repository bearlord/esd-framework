<?php

namespace ESD\Plugins\Actor\Multicast;

use Ds\Set;
use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Actor\Actor;
use ESD\Plugins\Actor\ActorMessage;
use ESD\Yii\Yii;

class Channel
{
    use GetLogger;

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
     * Channel constructor.
     * @param Table $channelTable
     */
    public function __construct(Table $channelTable)
    {
        //Read the table first, because the process may restart
        $this->channelTable = $channelTable;

        foreach ($this->channelTable as $value) {
            $this->addSubscribeFormTable($value['channel'], $value['actor']);
        }
    }

    /**
     * @param string $channel
     * @param string $actor
     */
    private function addSubscribeFormTable(string $channel, string $actor)
    {
        if (empty($actor)) {
            return;
        }

        if (!isset($this->subscribeArr[$channel])) {
            $this->subscribeArr[$channel] = new Set();
        }

        $this->subscribeArr[$channel]->add($actor);
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
     * Delete channel
     *
     * @param string $channel
     * @return void
     */
    public function deleteChannel(string $channel)
    {
        if (!empty($channel)) {
            unset($this->subscribeArr[$channel]);
        }

        $actorArr = !empty($this->subscribeArr[$channel]) ? $this->subscribeArr[$channel] : [];
        if (!empty($actorArr)) {
            foreach ($actorArr as $actor) {
                $this->channelTable->del($channel . $actor);
            }
        }

        $this->debug(Yii::t('esd', "Channel {channel} deleted", [
            'channel' => $channel
        ]));
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
    public function publish(string $channel, string $message, array $excludeActorList = [], ?string $frome = '')
    {
        $tree = $this->buildTrees($channel);

        foreach ($tree as $item) {
            if (isset($this->subscribeArr[$item])) {
                foreach ($this->subscribeArr[$item] as $actor) {
                    if (!in_array($actor, $excludeActorList)) {
                        $this->publishToActor($channel, $actor, $message, $from);
                    }
                }
            }
        }
    }

    /**
     * Publish to actor
     *
     * @param string $channel
     * @param string $toActor
     * @param $message
     * @return void
     * @throws \ESD\Plugins\Actor\ActorException
     */
    protected function publishToActor(string $channel, string $toActor, $message, ?string $fromActor = '')
    {
        $actorInstance = Actor::getProxy($toActor);

        if (!empty($actorInstance)) {
            $actorMessage = new ActorMessage([
                'channel' => $channel,
                'type' => 'multicast',
                'message' => $message
            ], date("YmdHis").  mt_rand(10000, 99999), $fromActor, $toActor);
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

        $this->channelTable->set($channel . $actor, [
            "channel" => $channel,
            "actor" => $actor
        ]);

        $this->debug(Yii::t('esd', "Actor {actor} subscribe channel {channel}", [
            'actor' => $actor,
            'channel' => $channel
        ]));

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
        if (empty($actor)) {
            return false;
        }

        if (isset($this->subscribeArr[$channel])) {
            $this->subscribeArr[$channel]->remove($actor);

            if ($this->subscribeArr[$channel]->count() == 0) {
                unset($this->subscribeArr[$channel]);
            }
        }

        $this->channelTable->del($channel . $actor);

        $this->debug(Yii::t('esd', "Actor {actor} unsubscribe channel {channel}", [
            'actor' => $actor,
            'channel' => $channel
        ]));

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

    /**
     * @param $arr
     * @param $temp
     * @param $result
     * @param $complete
     * @param $isSys
     */
    protected function helpReplacePlus($arr, &$temp, &$result, $complete, $isSys)
    {
        $count = count($arr);

        $m = 0;
        if ($isSys) {
            $m = 1;
        }

        for ($i = $m; $i < $count; $i++) {
            $new = $arr;
            if ($new[$i] == '+') {
                continue;
            }
            $new[$i] = '+';
            $temp[] = $new;
            $value = implode('/', $new);
            $result->add($value . "/#");
            if ($complete) {
                $result->add($value);
            }
        }
    }

}