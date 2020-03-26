<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Psr\DB;

/**
 * Interface DBInterface
 * @package ESD\Psr\DB
 */
interface DBInterface
{
    /**
     * @return mixed
     */
    public function getType();

    /**
     * @param $name
     * @param callable|null $call
     * @return mixed
     */
    public function execute($name, callable $call = null);

    /**
     * @return mixed
     */
    public function getLastQuery();
}