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
 * Class XmlResponseFilter
 * @package ESD\Plugins\EasyRoute\Filter
 */
class XmlResponseFilter extends AbstractFilter
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
            if ($annotation instanceof ResponseBody && strpos($annotation->value, "application/xml") !== false) {
                $data = $clientData->getResponseRaw();

                $xmlStartElement = $annotation->xmlStartElement;
                if (is_array($data)){
                    $data = (new ArrayToXml())->buildXML($data, $xmlStartElement);
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
        return "XmlResponseFilter";
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