<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Node\Expr\BinaryOp;

use ESD\Nikic\PhpParser\Node\Expr\BinaryOp;

class LogicalAnd extends BinaryOp
{
    public function getOperatorSigil() : string {
        return 'and';
    }
    
    public function getType() : string {
        return 'Expr_BinaryOp_LogicalAnd';
    }
}
