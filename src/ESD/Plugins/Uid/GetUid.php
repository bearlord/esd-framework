<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Uid;

use ESD\Core\Memory\CrossProcess\Table;
use ESD\Server\Coroutine\Server;

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
     * @param string $uid
     * @throws \Exception
     */
    public function kickUid(string $uid)
    {
        $this->getUidBean()->kickUid($uid);
    }

    /**
     * @param int $fd
     * @param string $uid
     * @param bool $autoKick
     * @throws \Exception
     */
    public function bindUid(int $fd, string $uid, ?bool $autoKick = true)
    {
        $this->getUidBean()->bindUid($fd, $uid, $autoKick);
    }

    /**
     * @param int $fd
     * @throws \Exception
     */
    public function unBindUid(int $fd)
    {
        $this->getUidBean()->unBindUid($fd);
    }

    /**
     * @param string $uid
     * @return mixed
     */
    public function getUidFd(string $uid)
    {
        return $this->getUidBean()->getUidFd($uid);
    }

    /**
     * @param int $fd
     * @return mixed
     */
    public function getFdUid(int $fd)
    {
        return $this->getUidBean()->getFdUid($fd);
    }

    /**
     * @param $uid
     * @return bool
     */
    public function isOnline($uid): bool
    {
        return $this->getUidBean()->isOnline($uid);
    }

    /**
     * @return int
     */
    public function getUidCount(): int
    {
        return $this->getUidBean()->getUidCount();
    }

    /**
     * @return array
     */
    public function getAllUid(): array
    {
        return $this->getUidBean()->getAllUid();
    }

    /**
     * @return array
     */
    public function getAllFd(): array
    {
        return $this->getUidBean()->getAllFd();
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