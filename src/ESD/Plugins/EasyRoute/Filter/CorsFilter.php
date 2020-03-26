<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Plugins\Pack\ClientData;

class CorsFilter extends AbstractFilter
{

    /**
     * @var CorsConfig|null
     */
    private $corsConfig;

    public function __construct(?CorsConfig $corsConfig = null)
    {
        if ($corsConfig == null) {
            $corsConfig = new CorsConfig();
        }
        $this->corsConfig = $corsConfig;
    }

    public function getType()
    {
        return AbstractFilter::FILTER_PRE;
    }

    public function filter(ClientData $clientData): int
    {
        if($this->corsConfig->getAllowOrigin() == '*'){
            $clientData->getResponse()->withHeader('Access-Control-Allow-Origin', $this->corsConfig->getAllowOrigin());
        }else{
            $origin = $clientData->getRequest()->getHeader('origin');
            if(!empty($origin)){
                $originBlackList = explode(',',$this->corsConfig->getAllowOrigin());
                if(in_array($origin[0],$originBlackList)){
                    $clientData->getResponse()->withHeader('Access-Control-Allow-Origin', $origin[0]);
                }
            }
        }
        $clientData->getResponse()->withHeader('Access-Control-Allow-Credentials', $this->corsConfig->isAllowCredentials());
        $clientData->getResponse()->withHeader('Access-Control-Allow-Methods', $this->corsConfig->getAllowMethods());
        $clientData->getResponse()->withHeader('Access-Control-Allow-Headers', $this->corsConfig->getAllowHeaders());
        $clientData->getResponse()->withHeader('Access-Control-Max-Age', $this->corsConfig->getAllowMaxAge());
        return AbstractFilter::RETURN_NEXT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "CorsFilter";
    }

    public function isEnable(ClientData $clientData)
    {
        return $this->isHttp($clientData);
    }
}