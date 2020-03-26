<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Context;

class RootContextBuilder implements ContextBuilder
{
    private $context;

    /**
     * RootContextBuilder constructor.
     */
    public function __construct()
    {
        $this->context = new Context();
    }

    /**
     * @inheritDoc
     *
     * @return Context|null
     */
    public function build(): ?Context
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     *
     * @return int
     */
    public function getDeep(): int
    {
        return ContextBuilder::ROOT_CONTEXT;
    }
}