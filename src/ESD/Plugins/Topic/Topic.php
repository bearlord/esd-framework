<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/22
 * Time: 11:17
 */

namespace ESD\Plugins\Topic;


use Ds\Set;
use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Pack\GetBoostSend;
use ESD\Plugins\Uid\GetUid;

class Topic
{
    use GetBoostSend;
    use GetUid;
    use GetLogger;
    protected $subArr = [];
    /**
     * @var Table
     */
    private $topicTable;

    public function __construct(Table $topicTable)
    {
        //先读table，因为进程有可能会重启
        $this->topicTable = $topicTable;
        foreach ($this->topicTable as $value) {
            $this->addSubFormTable($value['topic'], $value['uid']);
        }
    }

    /**
     * @param $topic
     * @param $uid
     */
    private function addSubFormTable($topic, $uid)
    {
        if (empty($uid)) return;
        if (!isset($this->subArr[$topic])) {
            $this->subArr[$topic] = new Set();
        }
        $this->subArr[$topic]->add($uid);
    }

    /**
     * @param $topic
     * @param $uid
     * @return bool
     */
    public function hasTopic($topic, $uid)
    {
        $set = $this->subArr[$topic] ?? null;
        if ($set == null) return false;
        return $set->contains($uid);
    }

    /**
     * 添加订阅
     * @param $topic
     * @param $uid
     * @throws BadUTF8
     */
    public function addSub($topic, $uid)
    {
        Utility::CheckTopicFilter($topic);
        $this->addSubFormTable($topic, $uid);
        $this->topicTable->set($topic . $uid, ["topic" => $topic, "uid" => $uid]);
        $this->debug("$uid Add Sub $topic");
    }

    /**
     * 清除Fd的订阅
     * @param $fd
     */
    public function clearFdSub($fd)
    {
        if (empty($fd)) return;
        $uid = $this->getFdUid($fd);
        $this->clearUidSub($uid);
    }

    /**
     * 清除Uid的订阅
     * @param $uid
     */
    public function clearUidSub($uid)
    {
        if (empty($uid)) return;
        foreach ($this->subArr as $topic => $sub) {
            $this->removeSub($topic, $uid);
        }
    }

    /**
     * 移除订阅
     * @param $topic
     * @param $uid
     */
    public function removeSub($topic, $uid)
    {
        if (empty($uid)) return;
        if (isset($this->subArr[$topic])) {
            $this->subArr[$topic]->remove($uid);
            if ($this->subArr[$topic]->count() == 0) {
                unset($this->subArr[$topic]);
            }
        }
        $this->topicTable->del($topic . $uid);
        $this->debug("$uid Remove Sub $topic");
    }

    /**
     * 删除主题
     * @param $topic
     */
    public function delTopic($topic)
    {
        $uidArr = $this->subArr[$topic] ?? [];
        unset($this->subArr[$topic]);
        foreach ($uidArr as $uid) {
            $this->topicTable->del($topic . $uid);
        }
    }

    /**
     * @param $topic
     * @param $data
     * @param array $excludeUidList
     */
    public function pub($topic, $data, $excludeUidList = [])
    {
        $tree = $this->buildTrees($topic);
        foreach ($tree as $one) {
            if (isset($this->subArr[$one])) {
                foreach ($this->subArr[$one] as $uid) {
                    if (!in_array($uid, $excludeUidList)) {
                        $this->pubToUid($uid, $data, $topic);
                    }
                }
            }
        }
    }

    /**
     * 构建订阅树,只允许5层
     * @param $topic
     * @return Set
     */
    private function buildTrees($topic)
    {
        $isSYS = false;
        if ($topic[0] == "$") {
            $isSYS = true;
        }
        $p = explode("/", $topic);
        $countPlies = count($p);
        $result = new Set();
        if (!$isSYS) {
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
                    $this->help_replace_plus($one, $temp, $result, $complete, $isSYS);
                }
                $arr = $temp;
            }
        }
        return $result;
    }

    private function help_replace_plus($arr, &$temp, &$result, $complete, $isSYS)
    {
        $count = count($arr);
        $m = 0;
        if ($isSYS) $m = 1;
        for ($i = $m; $i < $count; $i++) {
            $new = $arr;
            if ($new[$i] == '+') continue;
            $new[$i] = '+';
            $temp[] = $new;
            $value = implode('/', $new);
            $result->add($value . "/#");
            if ($complete) {
                $result->add($value);
            }
        }
    }

    /**
     * @param $uid
     * @param $data
     * @param $topic
     */
    private function pubToUid($uid, $data, $topic)
    {
        $fd = $this->getUidFd($uid);
        if (empty($uid)) return;
        $this->autoBoostSend($fd, $data, $topic);
    }
}
