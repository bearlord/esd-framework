<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Uid;

use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Server\Server;

/**
 * Trait GetUid
 * @package ESD\Plugins\Uid
 */
trait GetUid
{
    /**
     * @var UidBean
     */
    protected $uidBean;

    /**
     * @return UidBean
     */
    protected function getUidBean(): UidBean
    {
        if ($this->uidBean == null) {
            $this->uidBean = Server::$instance->getContainer()->get(UidBean::class);
        }
        return $this->uidBean;
    }

    /**
     * @param $uid
     */
    public function kickUid($uid)
    {
        $this->getUidBean()->kickUid($uid);
    }

    /**
     * @param $fd
     * @param $uid
     * @param bool $autoKick
     */
    public function bindUid($fd, $uid, $autoKick = true)
    {
        $this->getUidBean()->bindUid($fd, $uid, $autoKick);
    }

    /**
     * @param $fd
     */
    public function unBindUid($fd)
    {
        $this->getUidBean()->unBindUid($fd);
    }

    /**
     * @param $uid
     * @return mixed
     */
    public function getUidFd($uid)
    {
        return $this->getUidBean()->getUidFd($uid);
    }

    /**
     * @param $fd
     * @return mixed
     */
    public function getFdUid($fd)
    {
        return $this->getUidBean()->getFdUid($fd);
    }

    /**
     * @param $uid
     * @return bool
     */
    public function isOnline($uid)
    {
        return $this->getUidBean()->isOnline($uid);
    }

    /**
     * @return int
     */
    public function getUidCount()
    {
        return $this->getUidBean()->getUidCount();
    }

    /**
     * @return array
     */
    public function getAllUid()
    {
        return $this->getUidBean()->getAllUid();
    }

    /**
     * @return Table
     */
    public function getUidFdTable(): Table
    {
        return $this->getUidBean()->getUidFdTable();
    }

    /**
     * @return Table
     */
    public function getFdUidTable(): Table
    {
        return $this->getUidBean()->getFdUidTable();
    }
}