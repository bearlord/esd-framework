<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Node\Expr\AssignOp;

use ESD\Nikic\PhpParser\Node\Expr\AssignOp;

class BitwiseAnd extends AssignOp
{
    public function getType() : string {
        return 'Expr_AssignOp_BitwiseAnd';
    }
}