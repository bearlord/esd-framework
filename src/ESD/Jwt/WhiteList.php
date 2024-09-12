<?php

namespace ESD\Jwt;

use ESD\Plugins\Redis\GetRedis;

class WhiteList
{
    use GetRedis;

    /**
     * @var string
     */
    protected $loginType;

    /**
     * @var string
     */
    protected $ssoKey;

    /**
     * @var string
     */
    protected $cachePrefix;

    /**
     * @return string
     */
    public function getLoginType(): string
    {
        return $this->loginType;
    }

    /**
     * @param string $loginType
     * @return WhiteList
     */
    public function setLoginType(string $loginType): WhiteList
    {
        $this->loginType = $loginType;
        return $this;
    }

    /**
     * @return string
     */
    public function getSsoKey(): string
    {
        return $this->ssoKey;
    }

    /**
     * @param string $ssoKey
     * @return WhiteList
     */
    public function setSsoKey(string $ssoKey): WhiteList
    {
        $this->ssoKey = $ssoKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }

    /**
     * @param string $cachePrefix
     * @return WhiteList
     */
    public function setCachePrefix(string $cachePrefix): WhiteList
    {
        $this->cachePrefix = $cachePrefix;
        return $this;
    }


    /**
     * @param array $payload
     * @return bool
     */
    public function effective(array $payload): bool
    {
        switch (true) {
            case ($this->loginType == 'mpop'):
                return true;

            case ($this->loginType == 'sso'):
                $val = $this->redis()->get($this->cachePrefix . $payload['scope'] . ":" . $payload['aud']);
                return $payload['jti'] == $val;

            default:
                return false;
        }
    }

    /**
     * @param string $uid
     * @param string $version
     * @param string $type
     * @return bool
     */
    public function add(string $uid,string $version, $type = Jwt::SCOPE_TOKEN): bool
    {
        return $this->redis()->set($this->cachePrefix . $type . ":" . $uid, $version);
    }

    /**
     * @param string $uid
     * @param string $type
     * @return bool
     */
    public function remove(string $uid, string $type = Jwt::SCOPE_TOKEN): bool
    {
        return $this->redis()->set($this->cachePrefix . $type . ":" . $uid, 0, 7200);
    }
}
