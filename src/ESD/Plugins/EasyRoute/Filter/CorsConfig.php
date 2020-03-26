<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;


class CorsConfig
{
    /**
     * 表示的是访问服务端的ip地址及端口号，也可以设置为*表示所有域都可以通过；
     * @var string
     */
    protected $allowOrigin = "*";
    /**
     * 表示的是跨域的ajax中可以携带cookie，此时第一项设置不能为*，需指定域名；
     * @var bool
     */
    protected $allowCredentials = false;
    /**
     * 表示的是允许跨域的请求方法；
     * @var string
     */
    protected $allowMethods = "*";
    /**
     * 表示的是允许跨域请求包含content-type头；
     * @var string
     */
    protected $allowHeaders = "*";
    /**
     * 表示的是在3628800秒内，不需要再发送预检验请求，可以缓存该结果，一般默认。
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