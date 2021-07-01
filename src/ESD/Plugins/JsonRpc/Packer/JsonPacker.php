<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Packer;

use ESD\Yii\Base\Component;

/**
 * Class JsonPacker
 * @package ESD\Plugins\JsonRpc
 */
class JsonPacker extends Component implements PackerInterface
{
    /**
     * @param $data
     * @return string
     */
    public function pack($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function unpack(string $data)
    {
        return json_decode($data, true);
    }
}