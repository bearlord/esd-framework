<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Node\Stmt;

use ESD\Nikic\PhpParser\Node;

/** Nop/empty statement (;). */
class Nop extends Node\Stmt
{
    public function getSubNodeNames() : array {
        return [];
    }
    
    public function getType() : string {
        return 'Stmt_Nop';
    }
}
