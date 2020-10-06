<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Plugins\Saber\Interceptors\Interceptor;
use ESD\Server\Co\Server;
use Swlib\Http\ContentType;
use Swlib\Http\Exception\HttpExceptionMask;

class SaberConfig extends BaseConfig
{
    const KEY = "saber";
    /**
     * Base url
     * @var string|null
     */
    protected $baseUri;

    /**
     * User agent
     * @var string|null
     */
    protected $useragent;

    /**
     * Referer
     * @var string|null
     */
    protected $referer;

    /**
     * Redirect count
     * @var int
     */
    protected $redirect = 3;

    /**
     * Content type
     * @var string
     */
    protected $contentType = ContentType::JSON;

    /**
     * Whether to keep alive
     * @var bool
     */
    protected $keepAlive = true;

    /**
     * Time out, default is 5s, support millisecond
     * @var float
     */
    protected $timeout = 5;

    /**
     * Proxy, support http and sockes5
     * @var string
     */
    protected $proxy;

    /**
     * Verify server's ssl certificate
     * @var bool
     */
    protected $sslVerifyPeer = false;

    /**
     * Allow self-signed certificates
     * @var bool
     */
    protected $sslAllowSelfSigned = true;

    /**
     * Exception reporting level
     * @var int
     */
    protected $exceptionReport = HttpExceptionMask::E_NONE;

    /**
     * Retry time
     * @var int
     */
    protected $retryTime = 3;

    /**
     * Interceptor
     * @var string[]
     */
    protected $interceptors = [];

    /**
     * Whether to use pool
     * @var bool
     */
    protected $usePool = true;

    /**
     * SaberConfig constructor.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * Add Saber global interceptor
     * @param string $interceptor
     */
    public function addInterceptorClass(string $interceptor)
    {
        $this->interceptors[] = $interceptor;
    }

    /**
     * @return array
     */
    public function getInterceptors()
    {
        return $this->interceptors;
    }

    /**
     * @return string|null
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @param string|null $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @return string|null
     */
    public function getUseragent()
    {
        return $this->useragent;
    }

    /**
     * @param string|null $useragent
     */
    public function setUseragent($useragent)
    {
        $this->useragent = $useragent;
    }

    /**
     * @return string|null
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param string|null $referer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * @return int
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param int $redirect
     */
    public function setRedirect(int $redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return bool
     */
    public function isKeepAlive()
    {
        return $this->keepAlive;
    }

    /**
     * @param bool $keepAlive
     */
    public function setKeepAlive(bool $keepAlive)
    {
        $this->keepAlive = $keepAlive;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @return bool
     */
    public function isSslVerifyPeer()
    {
        return $this->sslVerifyPeer;
    }

    /**
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer)
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    /**
     * @return bool
     */
    public function isSslAllowSelfSigned()
    {
        return $this->sslAllowSelfSigned;
    }

    /**
     * @param bool $sslAllowSelfSigned
     */
    public function setSslAllowSelfSigned(bool $sslAllowSelfSigned)
    {
        $this->sslAllowSelfSigned = $sslAllowSelfSigned;
    }

    /**
     * @return int
     */
    public function getExceptionReport()
    {
        return $this->exceptionReport;
    }

    /**
     * @param int $exceptionReport
     */
    public function setExceptionReport(int $exceptionReport)
    {
        $this->exceptionReport = $exceptionReport;
    }

    /**
     * @return int
     */
    public function getRetryTime()
    {
        return $this->retryTime;
    }

    /**
     * @param int $retryTime
     */
    public function setRetryTime(int $retryTime)
    {
        $this->retryTime = $retryTime;
    }

    /**
     * @return bool
     */
    public function isUsePool()
    {
        return $this->usePool;
    }

    /**
     * @param bool $usePool
     */
    public function setUsePool(bool $usePool)
    {
        $this->usePool = $usePool;
    }

    /**
     * 异常处理回调
     * @param \Throwable $e
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function exceptionHandle(\Throwable $e)
    {
        Server::$instance->getLog()->error($e);
        return true;
    }

    /**
     * 构建配置
     * @return array
     */
    public function buildConfig()
    {
        $map = [];
        foreach ($this->interceptors as $interceptorClass) {
            $interceptor = new $interceptorClass();
            if ($interceptor instanceof Interceptor) {
                if (!isset($map[$interceptor->getType()])) {
                    $map[$interceptor->getType()] = [];
                }
                $map[$interceptor->getType()][$interceptor->getName()] = [$interceptor, "handle"];
            }
        }
        return [
            'base_uri' => $this->baseUri,
            'useragent' => $this->useragent,
            'referer' => $this->referer,
            'redirect' => $this->redirect,
            'keep_alive' => $this->keepAlive,
            'content_type' => $this->contentType,
            'timeout' => $this->timeout,
            'proxy' => $this->proxy,
            'ssl_verify_peer' => $this->sslVerifyPeer,
            'ssl_allow_self_signed' => $this->isSslAllowSelfSigned(),
            'exception_report' => $this->exceptionReport,
            'exception_handle' => [[$this, "exceptionHandle"]],
            'retry_time' => $this->retryTime,
            'use_pool' => $this->usePool,
            "before" => $map["before"] ?? null,
            "after" => $map["after"] ?? null,
            "before_redirect" => $map["before_redirect"] ?? null,
            "retry" => $map["before_redirect"] ?? null,
        ];
    }
}