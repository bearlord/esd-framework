<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Session;

/**
 * Interface SessionStorage
 * @package ESD\Plugins\Session
 */
interface SessionStorage
{
    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id);

    /**
     * @param string $id
     * @param string $data
     * @return mixed
     */
    public function set(string $id,string $data);

    /**
     * @param string $id
     * @return mixed
     */
    public function remove(string $id);
}