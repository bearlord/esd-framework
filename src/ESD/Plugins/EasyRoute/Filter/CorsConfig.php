<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;

/**
 * Class CorsConfig
 * @package ESD\Plugins\EasyRoute\Filter
 */
class CorsConfig
{
    /**
     * @var bool
     */
    protected $enable = false;

    /**
     * Represents the IP address and port number of the access server,
     * and can also be set to * to indicate that all domains can pass;
     * @var array
     */
    protected $allowOrigins = ["*"];

    /**
     * Represents a request method that allows cross-domain
     * @var array
     */
    protected $allowMethods = ["*"];

    /**
     * Indicates that cross-domain requests are allowed to include content-type headers;
     * @var array
     */
    protected $allowHeaders = ["*"];

    /**
     * @var array exposed headers
     */
    public $exposedHeaders = ["*"];

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * It means that cross-domain Ajax can carry cookies.
     * At this time, the first setting cannot be *, you need to specify the domain name;
     * @var bool
     */
    protected $allowCredentials = false;

    /**
     * It means that within 3628800 seconds, no pre-inspection request needs to be sent again,
     * and the result can be cached, which is generally the default.
     * @var int
     */
    protected $maxAge = 3628800;

    /**
     * @return array|string[]
     */
    public function getAllowOrigins(): array
    {
        return $this->allowOrigins;
    }

    /**
     * @param array|string[] $allowOrigins
     */
    public function setAllowOrigins(array $allowOrigins): void
    {
        $this->allowOrigins = $allowOrigins;
    }

    /**
     * @return array|string[]
     */
    public function getAllowMethods(): array
    {
        return $this->allowMethods;
    }

    /**
     * @param array|string[] $allowMethods
     */
    public function setAllowMethods(array $allowMethods): void
    {
        $this->allowMethods = $allowMethods;
    }

    /**
     * @return array|string[]
     */
    public function getAllowHeaders(): array
    {
        return $this->allowHeaders;
    }

    /**
     * @param array|string[] $allowHeaders
     */
    public function setAllowHeaders(array $allowHeaders): void
    {
        $this->allowHeaders = $allowHeaders;
    }

    /**
     * @return array|string[]
     */
    public function getExposedHeaders(): array
    {
        return $this->exposedHeaders;
    }

    /**
     * @param array|string[] $exposedHeaders
     */
    public function setExposedHeaders(array $exposedHeaders): void
    {
        $this->exposedHeaders = $exposedHeaders;
    }

    /**
     * @return bool
     */
    public function isAllowCredentials(): bool
    {
        return $this->allowCredentials;
    }

    /**
     * @param bool $allowCredentials
     */
    public function setAllowCredentials(bool $allowCredentials): void
    {
        $this->allowCredentials = $allowCredentials;
    }

    /**
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * @param int $maxAge
     */
    public function setMaxAge(int $maxAge): void
    {
        $this->maxAge = $maxAge;
    }
}
