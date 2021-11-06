<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
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
