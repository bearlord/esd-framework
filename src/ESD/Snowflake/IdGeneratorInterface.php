<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Snowflake;

/**
 * Class IdGeneratorInterface
 * @package ESD\Snowflake
 */
interface IdGeneratorInterface
{
    /**
     * Generate an ID by meta, if meta is null, then use the default meta.
     */
    public function generate(?Meta $meta = null): int;

    /**
     * Degenerate the meta by ID.
     */
    public function degenerate(int $id): Meta;

}