<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/23
 * Time: 10:49
 */

namespace ESD\Plugins\Uid;

use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;

class UidBean
{
    use GetLogger;
    /**
     * @var Table
     */
    protected $uidFdTable;
    /**
     * @var Table
     */
    protected $fdUidTable;
    /**
     * @var UidConfig
     */
    protected $uidConfig;

    /**
     * @param int $getMaxCoroutine
     * @param UidConfig $uidConfig
     */
    public function __construct(int $getMaxCoroutine, UidConfig $uidConfig)
    {
        $this->uidConfig = $uidConfig;
        $this->uidFdTable = new Table($getMaxCoroutine);
        $this->uidFdTable->column("fd", Table::TYPE_INT);
        $this->uidFdTable->create();
        $this->fdUidTable = new Table($getMaxCoroutine);
        $this->fdUidTable->column("uid", Table::TYPE_STRING, $uidConfig->getUidMaxLength());
        $this->fdUidTable->create();
    }

    /**
     * @return Table
     */
    public function getUidFdTable(): Table
    {
        return $this->uidFdTable;
    }

    /**
     * @return Table
     */
    public function getFdUidTable(): Table
    {
        return $this->fdUidTable;
    }

    /**
     * @param $uid
     */
    public function kickUid($uid)
    {
        $fd = $this->getUidFd($uid);
        if ($fd != null) {
            $this->unBindUid($fd);
            Server::$instance->closeFd($fd);
        }
        $this->debug("Kick uid: $uid");
    }

    /**
     * @param $fd
     * @param $uid
     * @param bool $autoKick
 */
    public function bindUid($fd, $uid, $autoKick = true)
    {
        if ($autoKick) {
            $this->kickUid($uid);
        }
        $this->fdUidTable->set($fd, ["uid" => $uid]);
        $this->uidFdTable->set($uid, ["fd" => $fd]);
        $this->debug("$fd Bind uid: $uid");
    }

    /**
     * @param $fd

     */
    public function unBindUid($fd)
    {
        $uid = $this->fdUidTable->get($fd, "uid");
        $this->fdUidTable->del($fd);
        if ($uid != null) {
            $this->uidFdTable->del($uid);
            $this->debug("$fd UnBind uid: $uid");
        }
    }

    /**
     * @param $uid
     * @return mixed
     */
    public function getUidFd($uid)
    {
        return $this->uidFdTable->get($uid, "fd");
    }

    /**
     * @param $fd
     * @return mixed
     */
    public function getFdUid($fd)
    {
        return $this->fdUidTable->get($fd, "uid");
    }

    public function isOnline($uid)
    {
        $fd = $this->getUidFd($uid);
        if ($fd != null) return true;
        return false;
    }

    public function getUidCount()
    {
        return $this->fdUidTable->count();
    }

    public function getAllUid()
    {
        $result = [];
        foreach ($this->uidFdTable as $key => $value) {
            $result[] = $key;
        }
        return $result;
    }
}