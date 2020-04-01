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
     * Represents the IP address and port number of the access server,
     * and can also be set to * to indicate that all domains can pass;
     * @var string
     */
    protected $allowOrigin = "*";

    /**
     * It means that cross-domain Ajax can carry cookies.
     * At this time, the first setting cannot be *, you need to specify the domain name;
     * @var bool
     */
    protected $allowCredentials = false;

    /**
     * Represents a request method that allows cross-domain
     * @var string
     */
    protected $allowMethods = "*";

    /**
     * Indicates that cross-domain requests are allowed to include content-type headers;
     * @var string
     */
    protected $allowHeaders = "*";

    /**
     * It means that within 3628800 seconds, no pre-inspection request needs to be sent again,
     * and the result can be cached, which is generally the default.
     * @var int
     */
    protected $allowMaxAge = 3628800;

    /**
     * @return string
     */
    public function getAllowOrigin(): string
    {
        return $this->allowOrigin;
    }

    /**
     * @param string $allowOrigin
     */
    public function setAllowOrigin(string $allowOrigin): void
    {
        $this->allowOrigin = $allowOrigin;
    }

    /**
     * @return string
     */
    public function isAllowCredentials(): string
    {
        return $this->allowCredentials ? "true" : "false";
    }

    /**
     * @param bool $allowCredentials
     */
    public function setAllowCredentials(bool $allowCredentials): void
    {
        $this->allowCredentials = $allowCredentials;
    }

    /**
     * @return string
     */
    public function getAllowMethods(): string
    {
        return $this->allowMethods;
    }

    /**
     * @param string $allowMethods
     */
    public function setAllowMethods(string $allowMethods): void
    {
        $this->allowMethods = $allowMethods;
    }

    /**
     * @return string
     */
    public function getAllowHeaders(): string
    {
        return $this->allowHeaders;
    }

    /**
     * @param string $allowHeaders
     */
    public function setAllowHeaders(string $allowHeaders): void
    {
        $this->allowHeaders = $allowHeaders;
    }

    /**
     * @return int
     */
    public function getAllowMaxAge(): int
    {
        return $this->allowMaxAge;
    }

    /**
     * @param int $allowMaxAge
     */
    public function setAllowMaxAge(int $allowMaxAge): void
    {
        $this->allowMaxAge = $allowMaxAge;
    }
}