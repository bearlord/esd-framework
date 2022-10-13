<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Jwt;

/**
 * Class JwtBuilder
 * @package ESD\Jwt
 */
class JwtBuilder implements \ArrayAccess
{

    protected $container = [];

    /**
     * @var string token
     */
    protected $token = "";

    /**
     * @var string refresh token
     */
    protected $refreshToken = "";


    public function __construct($container = [])
    {
        $this->container = $container;
    }

    /**
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->container[$key];
    }

    /**
     *
     * @param $key
     * @param $val
     */
    public function __set($key, $val)
    {
        $this->container[$key] = $val;
    }

    /**
     *
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->container[$key]);
    }

    /**
     *
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->container[$key]);
    }

    /**
     * @param $offset
     * @param $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * @return array|mixed
     */
    public function toArray()
    {
        return $this->container;
    }

    /**
     * Issuer
     * @return mixed
     */
    public function getIssuer()
    {
        return $this->container['iss'] ?? '';
    }

    /**
     * Issuer
     * @param mixed $issuer
     */
    public function setIssuer($issuer): void
    {
        $this->container['iss'] = $issuer;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->container['scope'] ?? '';
    }

    /**
     * @param mixed $issuer
     */
    public function setScope($scope): void
    {
        $this->container['scope'] = $scope;
    }


    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->container['sub'] ?? '';
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void
    {
        $this->container['sub'] = $subject;

    }

    /**
     * @return mixed
     */
    public function getAudience()
    {
        return $this->container['aud'] ?? '';
    }

    /**
     * @param mixed $audience
     */
    public function setAudience($audience): void
    {
        $this->container['aud'] = $audience;
    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->container['exp'] ?? '';
    }

    /**
     * @param mixed $expiration
     */
    public function setExpiration($expiration): void
    {
        $this->container['exp'] = $expiration;
    }

    /**
     * @return mixed
     */
    public function getNotBefore()
    {
        return $this->container['nbf'] ?? '';
    }

    /**
     * @param mixed $notBefore
     */
    public function setNotBefore($notBefore): void
    {
        $this->container['nbf'] = $notBefore;
    }

    /**
     * @return mixed
     */
    public function getIssuedAt()
    {
        return $this->container['iat'] ?? '';
    }

    /**
     * @param mixed $issuedAt
     */
    public function setIssuedAt($issuedAt): void
    {
        $this->container['iat'] = $issuedAt;
    }

    /**
     * @return mixed
     */
    public function getJwtId()
    {
        return $this->container['jti'] ?? '';
    }

    /**
     * @param mixed $jwtId
     */
    public function setJwtId($jwtId): void
    {
        $this->container['jti'] = $jwtId;
    }

    /**
     * @return mixed
     */
    public function getJwtData()
    {
        return $this->container['data'] ?? [];
    }

    /**
     * @param mixed $jwtData
     */
    public function setJwtData(array $jwtData): void
    {
        $this->container['data'] = $jwtData;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return JwtBuilder
     */
    public function setToken(string $token): JwtBuilder
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }
}