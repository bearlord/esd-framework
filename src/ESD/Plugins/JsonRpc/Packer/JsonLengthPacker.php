<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Packer;

use ESD\Yii\Base\Component;

/**
 * Class JsonLengthPacker
 * @package ESD\Plugins\JsonRpc
 */
class JsonLengthPacker extends Component implements PackerInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var array
     */
    protected $defaultOptions = [
        'package_length_type' => 'N',
        'package_body_offset' => 4,
    ];

    /**
     * JsonLengthPacker constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options['settings'] ?? []);

        $this->type = $options['package_length_type'];
        $this->length = $options['package_body_offset'];
    }

    /**
     * @param $data
     * @return string
     */
    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return pack($this->type, strlen($data)) . $data;
    }

    /**
     * @param string $data
     * @return mixed|null
     */
    public function unpack(string $data)
    {
        $data = substr($data, $this->length);
        if (!$data) {
            return null;
        }
        return json_decode($data, true);
    }
}