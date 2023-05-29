<?php declare(strict_types=1);

namespace ESD\Nikic\PhpParser\Node\Expr;

use ESD\Nikic\PhpParser\Node\Arg;
use ESD\Nikic\PhpParser\Node\Expr;
use ESD\Nikic\PhpParser\Node\VariadicPlaceholder;

abstract class CallLike extends Expr {
    /**
     * Return raw arguments, which may be actual Args, or VariadicPlaceholders for first-class
     * callables.
     *
     * @return array<Arg|VariadicPlaceholder>
     */
    abstract public function getRawArgs(): array;

    /**
     * Returns whether this call expression is actually a first class callable.
     */
    public function isFirstClassCallable(): bool {
        foreach ($this->getRawArgs() as $arg) {
            if ($arg instanceof VariadicPlaceholder) {
                return true;
            }
        }
        return false;
    }

    /**
     * Assert that this is not a first-class callable and return only ordinary Args.
     *
     * @return Arg[]
     */
    public function getArgs(): array {
        assert(!$this->isFirstClassCallable());
        return $this->getRawArgs();
    }
}