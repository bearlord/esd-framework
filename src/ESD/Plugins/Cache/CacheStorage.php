<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache;

/**
 * Interface CacheStorage
 * @package ESD\Plugins\Cache
 */
interface CacheStorage
{
    /**
     * @param string $nameSpace
     * @param string $id
     * @return mixed
     */
    public function getFromNameSpace(string $nameSpace, string $id);

    /**
     * @param string $nameSpace
     * @param string $id
     * @param string $data
     * @return mixed
     */
    public function setFromNameSpace(string $nameSpace, string $id, string $data);

    /**
     * @param string $nameSpace
     * @param string $id
     * @return mixed
     */
    public function removeFromNameSpace(string $nameSpace, string $id);

    /**
     * @param string $nameSpace
     * @return mixed
     */
    public function removeNameSpace(string $nameSpace);

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id);

    /**
     * @param string $id
     * @param string $data
     * @param int $time
     * @return mixed
     */
    public function set(string $id, string $data, int $time);

    /**
     * @param string $id
     * @return mixed
     */
    public function remove(string $id);

    /**
     * @param string $id
     * @param int $ttl
     * @return mixed
     */
    public function lock(string $id, int $ttl);

    /**
     * @param string $id
     * @param string $token
     * @return mixed
     */
    public function unlock(string $id, string $token);
}