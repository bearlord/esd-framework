<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser;

interface Builder
{
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : Node;
}
