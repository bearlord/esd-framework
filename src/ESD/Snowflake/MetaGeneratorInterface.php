<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Snowflake;

/**
 * Class MetaGeneratorInterface
 */
interface MetaGeneratorInterface
{
    public function generate(): Meta;

    public function getBeginTimestamp(): int;

    public function getConfiguration(): ConfigurationInterface;
}
