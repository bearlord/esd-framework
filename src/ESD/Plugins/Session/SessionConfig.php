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
    const key = "session";

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
    const HEADER_IDENTIFY = 'sessionId';


    /**
     * 销毁时间s
     * @var int
     */
    protected $timeout = 30 * 60;

    /**
     * @var string
     */
    protected $database = "default";

    /**
     * @var string
     */
    protected $sessionStorageClass = RedisSessionStorage::class;

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
        parent::__construct(self::key);
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getSessionUsage(): string
    {
        return $this->sessionUsage;
    }

    /**
     * @param $usage
     */
    public function setSessionUsage( $usage ): void {
        $this->sessionUsage = $usage;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
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
     * @return bool
     */
    public function getSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure): void {
        $this->secure = $secure;
    }


    /**
     * @return string
     */
    public function getDomain():string
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
    public function setPath(string $path) :void
    {
        $this->path = $path;
    }


    /**
     * @return bool
     */
    public function getHttpOnly():bool
    {
        return $this->httpOnly;
    }


    /**
     * @param bool $bool
     */
    public function setHttpOnly(bool $bool): void
    {
        $this->httpOnly = $bool;
    }


    /**
     * @return string
     */
    public function getSessionName(): string
    {
        return $this->sessionName;
    }

    /**
     * @param string $name
     */
    public function setSessionName(string $name): void
    {
        $this->sessionName = $name;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }
}