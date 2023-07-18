<?php

declare(strict_types=1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2018, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ESD\Goaop\Instrument\Transformer;

use ESD\Nikic\PhpParser\Node;
use ESD\Nikic\PhpParser\Node\Expr\ClassConstFetch;
use ESD\Nikic\PhpParser\Node\Expr\Closure;
use ESD\Nikic\PhpParser\Node\Expr\Instanceof_;
use ESD\Nikic\PhpParser\Node\Expr\New_;
use ESD\Nikic\PhpParser\Node\Expr\StaticCall;
use ESD\Nikic\PhpParser\Node\Identifier;
use ESD\Nikic\PhpParser\Node\UnionType;
use ESD\Nikic\PhpParser\Node\Name;
use ESD\Nikic\PhpParser\Node\Name\FullyQualified;
use ESD\Nikic\PhpParser\Node\NullableType;
use ESD\Nikic\PhpParser\Node\Param;
use ESD\Nikic\PhpParser\Node\Stmt\Catch_;
use ESD\Nikic\PhpParser\Node\Stmt\Class_;
use ESD\Nikic\PhpParser\Node\Stmt\ClassMethod;
use ESD\Nikic\PhpParser\Node\Stmt\Namespace_;
use ESD\Nikic\PhpParser\Node\Stmt\Property;
use ESD\Nikic\PhpParser\NodeVisitorAbstract;
use UnexpectedValueException;

/**
 * Node visitor that resolves class name for `self` nodes with FQN
 */
final class SelfValueVisitor extends NodeVisitorAbstract
{
    /**
     * List of replaced nodes
     *
     * @var Node[]
     */
    protected array $replacedNodes = [];

    /**
     * Current namespace
     */
    protected ?string $namespace = null;

    /**
     * Current class name
     */
    protected ?Name $className = null;

    /**
     * Returns list of changed `self` nodes
     *
     * @return Node[]
     */
    public function getReplacedNodes(): array
    {
        return $this->replacedNodes;
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        $this->namespace     = null;
        $this->className     = null;
        $this->replacedNodes = [];

        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = $node->name->toString();
        } elseif ($node instanceof Class_) {
            if ($node->name !== null) {
                $this->className = new Name($node->name->toString());
            }
        } elseif ($node instanceof ClassMethod || $node instanceof Closure) {
            if (isset($node->returnType)) {
                $node->returnType = $this->resolveType($node->returnType);
            }
        } elseif (($node instanceof Property) && (isset($node->type))) {
            $node->type = $this->resolveType($node->type);
        } elseif (($node instanceof Param) && (isset($node->type))) {
            $node->type = $this->resolveType($node->type);
        } elseif (
            $node instanceof StaticCall
            || $node instanceof ClassConstFetch
            || $node instanceof New_
            || $node instanceof Instanceof_
        ) {
            if ($node->class instanceof Name) {
                $node->class = $this->resolveClassName($node->class);
            }
        } elseif ($node instanceof Catch_) {
            foreach ($node->types as &$type) {
                $type = $this->resolveClassName($type);
            }
        }

        return null;
    }

    /**
     * Resolves `self` class name with value
     *
     * @param Name $name Instance of original node
     *
     * @return Name|FullyQualified
     */
    protected function resolveClassName(Name $name): Name
    {
        // Skip all names except special `self`
        if (strtolower($name->toString()) !== 'self') {
            return $name;
        }

        // Save the original name
        $originalName = $name;
        $name = clone $originalName;
        $name->setAttribute('originalName', $originalName);

        $fullClassName    = Name::concat($this->namespace, $this->className);
        $resolvedSelfName = new FullyQualified('\\' . ltrim($fullClassName->toString(), '\\'), $name->getAttributes());

        $this->replacedNodes[] = $resolvedSelfName;

        return $resolvedSelfName;
    }

    /**
     * Helper method for resolving type nodes
     *
     * @return NullableType|Name|FullyQualified|Identifier
     */
    private function resolveType(Node $node)
    {
        if ($node instanceof NullableType) {
            $node->type = $this->resolveType($node->type);
            return $node;
        }
        if ($node instanceof Name) {
            return $this->resolveClassName($node);
        }
        if ($node instanceof Identifier) {
            return $node;
        }
        if ($node instanceof UnionType) {
            return $node;
        }

        throw new UnexpectedValueException('Unknown node type: ' . get_class($node));
    }
}
