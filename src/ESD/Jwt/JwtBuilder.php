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
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
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
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->container[$offset] ?? null;
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
    public function getScope(): mixed
    {
        return $this->container['scope'] ?? '';
    }

    /**
     * @param $scope
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
    public function setSubject(mixed $subject): void
    {
        $this->container['sub'] = $subject;

    }

    /**
     * @return mixed
     */
    public function getAudience(): mixed
    {
        return $this->container['aud'] ?? '';
    }

    /**
     * @param mixed $audience
     */
    public function setAudience(mixed $audience): void
    {
        $this->container['aud'] = $audience;
    }

    /**
     * @return mixed
     */
    public function getExpiration(): mixed
    {
        return $this->container['exp'] ?? '';
    }

    /**
     * @param mixed $expiration
     */
    public function setExpiration(mixed $expiration): void
    {
        $this->container['exp'] = $expiration;
    }

    /**
     * @return mixed
     */
    public function getNotBefore(): mixed
    {
        return $this->container['nbf'] ?? '';
    }

    /**
     * @param mixed $notBefore
     */
    public function setNotBefore(mixed $notBefore): void
    {
        $this->container['nbf'] = $notBefore;
    }

    /**
     * @return mixed
     */
    public function getIssuedAt(): mixed
    {
        return $this->container['iat'] ?? '';
    }

    /**
     * @param mixed $issuedAt
     */
    public function setIssuedAt(mixed $issuedAt): void
    {
        $this->container['iat'] = $issuedAt;
    }

    /**
     * @return mixed
     */
    public function getJwtId(): mixed
    {
        return $this->container['jti'] ?? '';
    }

    /**
     * @param mixed $jwtId
     */
    public function setJwtId(mixed $jwtId): void
    {
        $this->container['jti'] = $jwtId;
    }

    /**
     * @return mixed
     */
    public function getJwtData(): mixed
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
