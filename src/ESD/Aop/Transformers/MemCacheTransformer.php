<?php

namespace ESD\Aop\Transformers;

use Go\Core\AspectKernel;
use Go\Instrument\Transformer\BaseSourceTransformer;
use Go\Instrument\Transformer\SourceTransformer;
use Go\Instrument\Transformer\StreamMetaData;

class MemCacheTransformer extends BaseSourceTransformer
{
    /**
     * @var array|callable|SourceTransformer[]
     */
    protected $transformers = [];

    /**
     * MemCacheTransformer constructor.
     * @param AspectKernel $kernel
     * @param $transformers
     */
    public function __construct(AspectKernel $kernel, $transformers)
    {
        parent::__construct($kernel);
        $this->transformers = $transformers;
    }

    /**
     * This method may transform the supplied source and return a new replacement for it
     *
     * @param StreamMetaData $metadata Metadata for source
     * @return string See RESULT_XXX constants in the interface
     */
    public function transform(StreamMetaData $metadata)
    {
        return $this->processTransformers($metadata);
    }

    /**
     * Iterates over transformers
     *
     * @param StreamMetaData $metadata Metadata for source code
     * @return string See RESULT_XXX constants in the interface
     */
    private function processTransformers(StreamMetaData $metadata)
    {
        $overallResult = self::RESULT_ABSTAIN;
        if (is_callable($this->transformers)) {
            $delayedTransformers = $this->transformers;
            $this->transformers = $delayedTransformers();
        }
        foreach ($this->transformers as $transformer) {
            $transformationResult = $transformer->transform($metadata);
            if ($overallResult === self::RESULT_ABSTAIN && $transformationResult === self::RESULT_TRANSFORMED) {
                $overallResult = self::RESULT_TRANSFORMED;
            }
            // transformer reported about termination, next transformers will be skipped
            if ($transformationResult === self::RESULT_ABORTED) {
                $overallResult = self::RESULT_ABORTED;
                break;
            }
        }

        return $overallResult;
    }
}