<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;

use ESD\Core\Server\Beans\Http\HttpStream;
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
        $contentType = $clientData->getResponse()->getContentType();
        if (strpos($contentType, "application/xml") === false) {
            return AbstractFilter::RETURN_NEXT;
        }

        $xmlStartElement = $clientData->getResponse()->getHeaderLine('Xml-Start-Element');

        $data = $clientData->getResponseRaw();
        if (is_array($data)){
            $data = (new ArrayToXml())->buildXML($data, $xmlStartElement);
            $clientData->setResponseRaw($data);
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