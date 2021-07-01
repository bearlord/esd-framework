<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc;

use ESD\Plugins\JsonRpc\Packer\JsonEofPacker;
use ESD\Plugins\JsonRpc\Packer\JsonLengthPacker;
use ESD\Plugins\JsonRpc\Packer\JsonPacker;
use ESD\Plugins\JsonRpc\Transporter\JsonRpcHttpTransporter;
use ESD\Plugins\JsonRpc\Transporter\JsonRpcTransporter;

/**
 * Class Protocol
 * @package ESD\Plugins\JsonRpc
 */
class Protocol
{
    const PROTOCOL_JSON_RPC = 'jsonrpc';

    const PROTOCOL_JSON_RPC_TCP_LENGTH_CHECK = 'jsonrpc-tcp-length-check';

    const PROTOCOL_JSON_RPC_HTTP = 'jsonrpc-http';

    /**
     * @var array
     */
    protected $protocols = [
        self::PROTOCOL_JSON_RPC => [
            'packer' => JsonEofPacker::class,
            'transporter' => JsonRpcTransporter::class
        ],
        self::PROTOCOL_JSON_RPC_TCP_LENGTH_CHECK => [
            'packer' => JsonLengthPacker::class,
            'transporter' => JsonRpcTransporter::class
        ],
        self::PROTOCOL_JSON_RPC_HTTP => [
            'packer' => JsonPacker::class,
            'transporter' => JsonRpcHttpTransporter::class
        ]
    ];

    /**
     * @param string $protocol
     * @return string
     */
    public function getPacker(string $protocol): string
    {
        $value = !empty($this->protocols[$protocol]) ? $this->protocols[$protocol] : '';
        if (empty($value)) {
            return false;
        }
        return $value['packer'];
    }

    /**
     * @param string $protocol
     * @return string
     */
    public function getTransporter(string $protocol): string
    {
        $value = !empty($this->protocols[$protocol]) ? $this->protocols[$protocol] : '';
        if (empty($value)) {
            return false;
        }
        return $value['transporter'];
    }
}