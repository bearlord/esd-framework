<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;

use ESD\Core\Server\Beans\Http\HttpStream;
use ESD\Plugins\EasyRoute\Annotation\ResponseBody;
use ESD\Plugins\Pack\ClientData;
use ESD\Utils\ArrayToXml;

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
        $annotations = $clientData->getAnnotations();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResponseBody && strpos($annotation->value, "application/json") !== false) {
                $data = $clientData->getResponseRaw();

                if (!is_string($data)) {
                    if ($data instanceof HttpStream) {
                        $data = $data->__toString();
                    } else {
                        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                    $clientData->setResponseRaw($data);
                }

                $clientData->getResponse()->withHeader("Content-type", $annotation->value);
                return AbstractFilter::RETURN_NEXT;
            }

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