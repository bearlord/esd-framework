<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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