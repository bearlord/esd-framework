<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Uid;

use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Server\Coroutine\Server;

/**
 * Class UidBean
 * @package ESD\Plugins\Uid
 */
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
     * @param string $uid
     * @throws \Exception
     */
    public function kickUid(string $uid)
    {
        $fd = $this->getUidFd($uid);
        if ($fd != null) {
            $this->unBindUid($fd);
            Server::$instance->closeFd($fd);
        }

        $this->debug("Kick uid: $uid");
    }

    /**
     * @param int $fd
     * @param string $uid
     * @param bool $autoKick
     * @throws \Exception
     */
    public function bindUid(int $fd, string $uid, ?bool $autoKick = true)
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
     * @throws \Exception
     */
    public function unBindUid(int $fd)
    {
        $fd = (string)$fd;

        $uid = $this->fdUidTable->get($fd, "uid");
        $this->fdUidTable->del($fd);
        if ($uid != null) {
            $this->uidFdTable->del($uid);
            $this->debug("$fd UnBind uid: $uid");
        }
    }

    /**
     * @param string $uid
     * @return mixed
     */
    public function getUidFd(string $uid)
    {
        return $this->uidFdTable->get($uid, "fd");
    }

    /**
     * @param int $fd
     * @return mixed
     */
    public function getFdUid(int $fd)
    {
        $fd = (string)$fd;

        return $this->fdUidTable->get($fd, "uid");
    }

    /**
     * @param string $uid
     * @return bool
     */
    public function isOnline(string $uid): bool
    {
        $fd = $this->getUidFd($uid);
        if ($fd != null) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getUidCount(): int
    {
        return $this->fdUidTable->count();
    }

    /**
     * @return array
     */
    public function getAllUid(): array
    {
        $result = [];
        foreach ($this->uidFdTable as $key => $value) {
            $result[] = $key;
        }
        return $result;
    }

    public function getAllFd(): array
    {
        $result = [];
        foreach ($this->fdUidTable as $key => $value) {
            $result[] = $key;
        }
        return $result;
    }
}