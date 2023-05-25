<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Node\Stmt;

use ESD\Nikic\PhpParser\Node;

abstract class TraitUseAdaptation extends Node\Stmt
{
    /** @var Node\Name|null Trait name */
    public $trait;
    /** @var Node\Identifier Method name */
    public $method;
}
