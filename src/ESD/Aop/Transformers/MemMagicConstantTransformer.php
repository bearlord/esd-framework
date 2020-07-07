<?php


namespace ESD\Aop\Transformers;

use Go\Instrument\Transformer\NodeFinderVisitor;
use Go\Instrument\Transformer\StreamMetaData;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\NodeTraverser;

/**
 * Class MagicConstantTransformer
 * @package ESD\Aop\Transformers
 */
class MemMagicConstantTransformer extends \Go\Instrument\Transformer\MagicConstantTransformer
{
    /**
     * @param StreamMetaData $metadata
     * @return string
     */
    public function transform(StreamMetaData $metadata)
    {
        $this->replaceMagicDirFileConstants($metadata);
        $this->wrapReflectionGetFileName($metadata);

        // We should always vote abstain, because if there is only changes for magic constants, we can drop them
        return self::RESULT_ABSTAIN;
    }

    /**
     * @param StreamMetaData $metadata
     */
    private function replaceMagicDirFileConstants(StreamMetaData $metadata)
    {
        $magicConstFinder = new NodeFinderVisitor([Dir::class, File::class]);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($magicConstFinder);
        $traverser->traverse($metadata->syntaxTree);

        /** @var MagicConst[] $magicConstants */
        $magicConstants = $magicConstFinder->getFoundNodes();
        $magicFileValue = $metadata->uri;
        $magicDirValue = dirname($magicFileValue);
        foreach ($magicConstants as $magicConstantNode) {
            $tokenPosition = $magicConstantNode->getAttribute('startTokenPos');
            $replacement = $magicConstantNode instanceof Dir ? $magicDirValue : $magicFileValue;

            $metadata->tokenStream[$tokenPosition][1] = "'{$replacement}'";
        }
    }

    /**
     * @param StreamMetaData $metadata
     */
    private function wrapReflectionGetFileName(StreamMetaData $metadata)
    {
        $methodCallFinder = new NodeFinderVisitor([MethodCall::class]);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($methodCallFinder);
        $traverser->traverse($metadata->syntaxTree);

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $methodCallFinder->getFoundNodes();
        foreach ($methodCalls as $methodCallNode) {
            if (($methodCallNode->name instanceof Identifier) && ($methodCallNode->name->toString() === 'getFileName')) {
                $startPosition = $methodCallNode->getAttribute('startTokenPos');
                $endPosition = $methodCallNode->getAttribute('endTokenPos');
                $expressionPrefix = '\\' . __CLASS__ . '::resolveFileName(';
                $metadata->tokenStream[$startPosition][1] = $expressionPrefix . $metadata->tokenStream[$startPosition][1];
                $metadata->tokenStream[$endPosition][1] .= ')';
            }
        }
    }
}