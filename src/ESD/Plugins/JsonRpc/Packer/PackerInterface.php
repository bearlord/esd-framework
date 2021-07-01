<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Packer;

/**
 * Interface PackerInterface
 * @package ESD\Plugins\JsonRpc
 */
interface PackerInterface
{
    public function pack($data): string;

    public function unpack(string $data);
}