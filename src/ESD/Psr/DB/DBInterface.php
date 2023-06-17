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
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $name
     * @param callable|null $call
     * @return mixed
     */
    public function execute(string $name, callable $call = null): mixed;

    /**
     * @return string
     */
    public function getLastQuery(): string;
}