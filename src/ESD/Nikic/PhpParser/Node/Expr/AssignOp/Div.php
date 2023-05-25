<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Node\Expr\AssignOp;

use ESD\Nikic\PhpParser\Node\Expr\AssignOp;

class Div extends AssignOp
{
    public function getType() : string {
        return 'Expr_AssignOp_Div';
    }
}
