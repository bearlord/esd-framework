<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Core\Server\Beans\Http\HttpStream;
use ESD\Plugins\Pack\ClientData;

class JsonResponseFilter extends AbstractFilter
{

    public function getType()
    {
        return AbstractFilter::FILTER_ROUTE;
    }

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

    public function isEnable(ClientData $clientData)
    {
        return $this->isHttp($clientData);
    }
}