<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;

class ProcessContextBuilder implements ContextBuilder
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Process $process)
    {
        $this->context = new Context();
        $this->context->add("process", $process);
    }

    /**
     * @return Context|null
     */
    public function build(): ?Context
    {
        return $this->context;
    }

    /**
     * @return int
     */
    public function getDeep(): int
    {
        return ContextBuilder::PROCESS_CONTEXT;
    }
}