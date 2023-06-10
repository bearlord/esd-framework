<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\DI;

/**
 * Interface Factory
 * @package ESD\Core\DI
 */
interface Factory
{
    /**
     * @param $params
     * @return mixed
     */
    public function create(?array $params);
}