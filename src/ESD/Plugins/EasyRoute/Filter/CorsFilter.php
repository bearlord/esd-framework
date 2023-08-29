<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;

use ESD\Core\Server\Beans\Http\HttpStream;
use ESD\Plugins\EasyRoute\Annotation\CrossOrigin;
use ESD\Plugins\EasyRoute\Annotation\ResponseBody;
use ESD\Plugins\Pack\ClientData;

/**
 * Class CorsFilter
 * @package ESD\Plugins\EasyRoute\Filter
 */
class CorsFilter extends AbstractFilter
{
    /**
     * @var CorsConfig|null
     */
    private $corsConfig;

    /**
     * CorsFilter constructor.
     * @param CorsConfig|null $corsConfig
     */
    public function __construct(?CorsConfig $corsConfig = null)
    {
        if ($corsConfig == null) {
            $corsConfig = new CorsConfig();
        }
        $this->corsConfig = $corsConfig;
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return [
            AbstractFilter::FILTER_PRE,
            AbstractFilter::FILTER_PRO,
        ];
    }

    /**
     * @param ClientData $clientData
     * @return int
     */
    public function filter(ClientData $clientData): int
    {
        $annotations = $clientData->getAnnotations();
        if (empty($annotations) || !$this->corsConfig->isEnable()) {
            return AbstractFilter::RETURN_NEXT;
        }

        $allowedOrigins = $this->corsConfig->getAllowOrigins();
        $allowedMethods = $this->corsConfig->getAllowMethods();
        $allowedHeaders = $this->corsConfig->getAllowHeaders();
        $allowCredentials = $this->corsConfig->isAllowCredentials() ? "true" : "false";
        $maxAge = $this->corsConfig->getMaxAge();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof CrossOrigin) {
                $allowedOrigins = $annotation->allowedOrigins;
                $allowedMethods = $annotation->allowedMethods;
                $allowedHeaders = $annotation->allowedHeaders;
                $allowCredentials = $annotation->allowCredentials ? "true" : "false";
                $maxAge = $annotation->maxAge;
            }

            if ($this->corsConfig->getAllowOrigins() == ["*"]) {
                $clientData->getResponse()->withHeader('Access-Control-Allow-Origin', $allowedOrigins);
            } else {
                $origin = $clientData->getRequest()->getHeader('origin');
                if (!empty($origin)) {
                    $originBlackList = explode(',', $allowedOrigins);
                    if (in_array($origin[0], $originBlackList)) {
                        $clientData->getResponse()->withHeader('Access-Control-Allow-Origin', $origin[0]);
                    }
                }
            }

            $clientData->getResponse()->withHeader('Access-Control-Allow-Credentials', $allowCredentials);
            $clientData->getResponse()->withHeader('Access-Control-Allow-Methods', $allowedMethods);
            $clientData->getResponse()->withHeader('Access-Control-Allow-Headers', $allowedHeaders);
            $clientData->getResponse()->withHeader('Access-Control-Max-Age', $maxAge);
        }

        return AbstractFilter::RETURN_NEXT;


    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "CorsFilter";
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
