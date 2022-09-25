<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Filter;

use ESD\Core\Server\Beans\Http\HttpStream;
use ESD\Plugins\Pack\ClientData;

/**
 * Class JsonResponseFilter
 * @package ESD\Plugins\EasyRoute\Filter
 */
class JsonResponseFilter extends AbstractFilter
{
    /**
     * @return mixed|string
     */
    public function getType()
    {
        return AbstractFilter::FILTER_ROUTE;
    }

    /**
     * @param ClientData $clientData
     * @return int
     */
    public function filter(ClientData $clientData): int
    {
        $data = $clientData->getResponseRaw();
        if (!is_string($data)) {
            if ($data instanceof HttpStream) {
                $data = $data->__toString();
            } else {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $clientData->setResponseRaw($data);
        }
        return AbstractFilter::RETURN_NEXT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "JsonResponseFilter";
    }

    /**
     * @param ClientData $clientData
     * @return bool|mixed
     */
    public function isEnable(ClientData $clientData)
    {
        return $this->isHttp($clientData);
    }
}