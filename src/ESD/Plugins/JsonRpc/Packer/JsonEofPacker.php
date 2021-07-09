<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Packer;

use ESD\Yii\Base\Component;

/**
 * Class JsonEofPacker
 * @package ESD\Plugins\JsonRpc
 */
class JsonEofPacker extends Component implements PackerInterface
{
    /**
     * @var string
     */
    protected $eof;

    /**
     * JsonEofPacker constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
//        $this->eof = $options['settings']['package_eof'] ?? '\r\n';
        $this->eof = $options['settings']['package_eof'] ?? "\r\n";
    }

    /**
     * @param $data
     * @return string
     */
    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $data . $this->eof;
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function unpack(string $data)
    {
        $data = rtrim($data, $this->eof);
        return json_decode($data, true);
    }

}