<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Jwt;

use DI\Annotation\Inject;
use ESD\Jwt\Exception\JWTException;
use ESD\Jwt\Exception\TokenValidException;

/**
 * Class Jwt
 * @package ESD\Jwt
 */
class Jwt
{
    const SCOPE_TOKEN = "access";

    const SCOPE_REFRESH = "refresh";

    protected static $header = [
        "alg" => "HS256",
        "typ" => "JWT"
    ];

    /**
     * @Value("jwt.alg")
     * @var string
     */
    protected $alg;

    /**
     * @Value("jwt.login_type")
     * @var string
     */
    protected $loginType;

    /**
     * @Value("jwt.sso_key")
     * @var string
     */
    protected $ssoKey;

    /**
     * @Value("jwt.ttl")
     * @var int
     */
    protected $ttl = 3600;

    /**
     * @Value("jwt.refresh_ttl")
     * @var int
     */
    protected $refreshTtl = 7200;

    /**
     * @Value("jwt.secret")
     * @var string
     */
    protected $secret;


    public function __construct()
    {
        $this->whiteList = new WhiteList();
    }


    /**
     * @return string[]
     */
    public static function getHeader(): array
    {
        return self::$header;
    }

    /**
     * @param string[] $header
     */
    public static function setHeader(array $header): void
    {
        self::$header = $header;
    }

    /**
     * @return WhiteList
     */
    public function getWhiteList(): WhiteList
    {
        return $this->whiteList;
    }

    /**
     * @param WhiteList $whiteList
     * @return Jwt
     */
    public function setWhiteList(WhiteList $whiteList): Jwt
    {
        $this->whiteList = $whiteList;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlg(): string
    {
        return $this->alg;
    }

    /**
     * @param string $alg
     * @return Jwt
     */
    public function setAlg(string $alg): Jwt
    {
        $this->alg = $alg;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoginType(): string
    {
        return $this->loginType;
    }

    /**
     * @param string $loginType
     * @return Jwt
     */
    public function setLoginType(string $loginType): Jwt
    {
        $this->loginType = $loginType;

        $this->whiteList->setLoginType($loginType);

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
     * @return Jwt
     */
    public function setSsoKey(string $ssoKey): Jwt
    {
        $this->ssoKey = $ssoKey;
        return $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     * @return Jwt
     */
    public function setTtl(int $ttl): Jwt
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @return int
     */
    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }

    /**
     * @param int $refreshTtl
     * @return Jwt
     */
    public function setRefreshTtl(int $refreshTtl): Jwt
    {
        $this->refreshTtl = $refreshTtl;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return Jwt
     */
    public function setSecret(string $secret): Jwt
    {
        $this->secret = $secret;
        return $this;
    }


    /**
     * Create token
     *
     * @param array|JwtBuilder $payload
     * @param string $type
     * @return JwtBuilder
     */
    public function createToken($payload, $type = null)
    {
        if ($payload instanceof JwtBuilder) {
            $jwtObj = $payload;
        } else {
            $time = time();
            $jwtObj = new JwtBuilder();

            if (isset($payload[$this->ssoKey])) {
                $jwtObj->setAudience($payload[$this->ssoKey]);
            }

            if ($jwtObj->getScope() == "") {
                $jwtObj->setScope($type == null ? Jwt::SCOPE_TOKEN : $type);
            }

            $jwtObj->setIssuedAt($time);
            $jwtObj->setNotBefore($time);
            $jwtObj->setJwtData($payload);
        }

        if ($type != null) {
            switch ($type) {
                case self::SCOPE_TOKEN:
                    $jwtObj->setScope(self::SCOPE_TOKEN);
                    break;

                case self::SCOPE_REFRESH:
                    $jwtObj->setScope(self::SCOPE_REFRESH);
                    break;

                default:
                    throw new JwtException("Not supported type:" . $type);
                    break;
            }
        }

        switch ($jwtObj->getScope()) {
            case self::SCOPE_TOKEN:
                $jwtObj->setExpiration(time() + $this->ttl);
                break;

            case self::SCOPE_REFRESH:
                $jwtObj->setExpiration(time() + $this->refreshTtl);
                break;
        }

        $version = uniqid();
        $jwtObj->setJwtId($version);

        if ($jwtObj->getScope() == "") {
            $jwtObj->setScope($type);
        }

        if ($this->loginType == "sso" && $jwtObj->getAudience() == "") {
            throw new JWTException("There is no Audience key in the claims", 500);
        }

        if ($this->loginType == "sso") {
            $this->whiteList->add($jwtObj->getAudience(), $version, $jwtObj->getScope());
        }

        $base64header = self::base64UrlEncode(json_encode([
            "alg" => $this->alg,
            "typ" => "JWT"
        ], JSON_UNESCAPED_UNICODE));

        $base64payload = self::base64UrlEncode(json_encode($jwtObj->toArray(), JSON_UNESCAPED_UNICODE));

        switch ($jwtObj->getScope()) {
            case self::SCOPE_TOKEN:
                $jwtObj->setToken(
                    $base64header . "."
                    . $base64payload . "."
                    . self::signature($base64header . "." . $base64payload, $this->secret, $this->alg)
                );
                break;

            case self::SCOPE_REFRESH:
                $jwtObj->setRefreshToken(
                    $base64header . "."
                    . $base64payload . "."
                    . self::signature($base64header . "." . $base64payload, $this->secret, $this->alg)
                );
                break;
        }

        return $jwtObj;
    }

    /**
     * Verify token
     *
     * @param string $token
     * @return JwtBuilder
     * @throws TokenValidException
     */
    public function verifyToken(string $token)
    {
        $tokenArray = explode(".", $token);
        if (3 != count($tokenArray)) {
            throw new TokenValidException("Token not exists", 401);
        }

        list($base64header, $base64payload, $sign) = $tokenArray;

        try {
            $base64deadheaded = json_decode(self::base64UrlDecode($base64header), true);
            if (empty($base64deadheaded["alg"])) {
                throw new TokenValidException("Token error", 401);
            }

            if (self::signature($base64header . "." . $base64payload, $this->secret, $base64deadheaded["alg"]) !== $sign) {
                throw new TokenValidException("Token signature error", 500);
            }

            $payload = json_decode(self::base64UrlDecode($base64payload), true);
        } catch (\Throwable $e) {
            throw new TokenValidException("Token parse invalid", 500);
        }

        switch (true) {
            case isset($payload["scope"]) && $payload["scope"] != Jwt::SCOPE_TOKEN;
                throw new TokenValidException("Token type is invalid", 401);
                break;

            case isset($payload["iat"]) && $payload["iat"] > time():
            case isset($payload["exp"]) && $payload["exp"] < time():
            case !$this->whiteList->effective($payload):
                throw new TokenValidException("Token is invalid", 401);
                break;

            case isset($payload["nbf"]) && $payload["nbf"] > time(): //检查是否生效
                throw new TokenValidException("Token is not effective", 401);
        }

        return new JwtBuilder($payload);
    }


    /**
     * Verify refresh token
     *
     * @param string $token
     * @return JwtBuilder
     */
    public function verifyRefreshToken(string $token)
    {
        $tokenArray = explode(".", $token);
        if (3 != count($tokenArray)){
            throw new TokenValidException("Token not exists", 401);
        }

        list($base64header, $base64payload, $sign) = $tokenArray;

        try {
            $base64deadheaded = json_decode(self::base64UrlDecode($base64header), true);
            if (empty($base64deadheaded["alg"])) {
                throw new TokenValidException("Token error", 401);
            }

            if (self::signature($base64header . "." . $base64payload, $this->secret, $base64deadheaded["alg"]) !== $sign) {
                throw new TokenValidException("Token signature error", 500);
            }

            $payload = json_decode(self::base64UrlDecode($base64payload), true);
        } catch (\Throwable $e) {
            throw new TokenValidException("Token parse invalid", 500);
        }

        switch (true) {
            case isset($payload["scope"]) && $payload["scope"] != Jwt::SCOPE_REFRESH;
                throw new TokenValidException("Token type is invalid", 401);
                break;

            case isset($payload["iat"]) && $payload["iat"] > time():
            case isset($payload["exp"]) && $payload["exp"] < time():
            case !$this->whiteList->effective($payload):
                throw new TokenValidException("Token is invalid", 401);
                break;

            case isset($payload["nbf"]) && $payload["nbf"] > time():
                throw new TokenValidException("Token is not effective", 401);
        }

        return new JwtBuilder($payload);
    }

    /**
     * Base64UrlEncode
     *
     * @param string $input
     * @return string
     */
    private static function base64UrlEncode(string $input)
    {
        return str_replace("=", "", strtr(base64_encode($input), "+/", "-_"));
    }

    /**
     * Base64UrlDecode
     *
     * @param string $input
     * @return bool|string
     */
    private static function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat("=", $addlen);
        }

        return base64_decode(strtr($input, "-_", "+/"));
    }

    /**
     * signature
     *
     * @param string $input
     * @param string $key
     * @param string $alg
     * @return mixed
     */
    private static function signature(string $input, string $key, string $alg = "HS256")
    {
        $alg_config = array(
            "HS256" => "sha256",
            "HS384" => "sha384",
            "HS512" => "sha512"
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }
}
