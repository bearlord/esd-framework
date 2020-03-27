<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine;

use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;

/**
 * Class CoroutineContextBuilder
 * @package ESD\Coroutine
 */
class CoroutineContextBuilder implements ContextBuilder
{
    /**
     * @return Context|null
     */
    public function build(): ?Context
    {
        if (Co::getCid() > 0) {
            return Co::getContext();
        } else {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getDeep(): int
    {
        return ContextBuilder::CO_CONTEXT;
    }
}