<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server;

use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;

/**
 * Class ServerContextBuilder
 * @package ESD\Core\Server
 */
class ServerContextBuilder implements ContextBuilder
{
    protected $context;

    /**
     * ServerContextBuilder constructor.
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->context = new Context();
        $this->context->add("server", $server);
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
        return ContextBuilder::SERVER_CONTEXT;
    }
}