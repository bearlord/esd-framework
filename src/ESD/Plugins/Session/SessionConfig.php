<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Session;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class SessionConfig
 * @package ESD\Plugins\Session
 */
class SessionConfig extends BaseConfig
{
    /**
     * Key
     */
    const KEY = "session";

    /**
     * Usage cookie
     */
    const USAGE_COOKIE = 'cookie';

    /**
     * Usage head
     */
    const USEAGE_HEADER = 'header';

    /**
     * Usage token
     */
    const USAGE_TOKEN = 'token';

    /**
     * Header identify to identify session
     */
    const HEADER_IDENTIFY = 'SESSIONID';

    /**
     * @var string
     */
    protected $sessionStorageClass = RedisSessionStorage::class;

    /**
     * Redis name
     * @var string
     */
    protected $redisName = 'default';

    /**
     * @var int
     */
    protected $database = 0;

    /**
     * @var string
     */
    protected $sessionUsage = SessionConfig::USAGE_COOKIE;

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var string
     */
    protected $path = '/';

    /**
     * @var string
     */
    protected $sessionName = 'SESSIONID';

    /**
     * @var int
     */
    protected $timeout = 30 * 60;

    /**
     * @var bool
     */
    protected $httpOnly = true;

    /**
     * Whether only ssl
     * @var bool
     */
    protected $secure = false;

    /**
     * SessionConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return string
     */
    public function getSessionStorageClass(): string
    {
        return $this->sessionStorageClass;
    }

    /**
     * @param string $sessionStorageClass
     */
    public function setSessionStorageClass(string $sessionStorageClass): void
    {
        $this->sessionStorageClass = $sessionStorageClass;
    }

    /**
     * @return string
     */
    public function getRedisName(): string
    {
        return $this->redisName;
    }

    /**
     * @param string $redisName
     */
    public function setRedisName(string $redisName): void
    {
        $this->redisName = $redisName;
    }

    /**
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->database;
    }

    /**
     * @param int $database
     */
    public function setDatabase(int $database): void
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getSessionUsage(): string
    {
        return $this->sessionUsage;
    }

    /**
     * @param string $sessionUsage
     */
    public function setSessionUsage(string $sessionUsage): void
    {
        $this->sessionUsage = $sessionUsage;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getSessionName(): string
    {
        return $this->sessionName;
    }

    /**
     * @param string $sessionName
     */
    public function setSessionName(string $sessionName): void
    {
        $this->sessionName = $sessionName;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return bool
     */
    public function getHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param bool $httpOnly
     */
    public function setHttpOnly(bool $httpOnly): void
    {
        $this->httpOnly = $httpOnly;
    }

    /**
     * @return bool
     */
    public function getSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }
}