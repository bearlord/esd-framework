<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Context;

/**
 * Interface ContextBuilder
 * @package ESD\Core\Context
 */
interface ContextBuilder
{
    const ROOT_CONTEXT = 0;

    const SERVER_CONTEXT = 1;

    const PROCESS_CONTEXT = 2;

    const CO_CONTEXT = 3;

    /**
     * Build
     *
     * @return Context|null
     */
    public function build(): ?Context;

    /**
     * Get Deep
     * @return int
     */
    public function getDeep(): int;
}