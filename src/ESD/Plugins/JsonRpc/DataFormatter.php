<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc;

/**
 * Class DataFormatter
 * @package ESD\Plugins\JsonRpc
 */
class DataFormatter
{
    /**
     * @param array $data
     * @return array
     */
    public function formatRequest(array $data): array
    {
        [$path, $params, $id] = $data;
        return [
            'jsonrpc' => '2.0',
            'method' => $path,
            'params' => $params,
            'id' => $id,
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function formatResponse(array $data): array
    {
        [$id, $result] = $data;
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function formatErrorResponse(array $data): array
    {
        [$id, $code, $message, $data] = $data;

        if (isset($data) && $data instanceof \Throwable) {
            $data = [
                'class' => get_class($data),
                'code' => $data->getCode(),
                'message' => $data->getMessage(),
            ];
        }
        return [
            'jsonrpc' => '2.0',
            'id' => $id ?? null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ]
        ];
    }
}