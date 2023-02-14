<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

use ESD\Core\ParamException;
use ESD\Core\Exception;
use ESD\Core\Server\Beans\Http\Cookie;
use ESD\Core\Server\Server;
use ESD\Yii\Yii;

/**
 * Class Request
 * @package ESD\Core\Server\Beans
 */
abstract class Request extends AbstractRequest
{
    /**
     * The name of the HTTP header for sending CSRF token.
     */
    const CSRF_HEADER = 'X-CSRF-Token';
    /**
     * The length of the CSRF token mask.
     * @deprecated 2.0.12 The mask length is now equal to the token length.
     */
    const CSRF_MASK_LENGTH = 8;

    /**
     * @var bool whether to enable CSRF (Cross-Site Request Forgery) validation. Defaults to true.
     * When CSRF validation is enabled, forms submitted to an Yii Web application must be originated
     * from the same application. If not, a 400 HTTP exception will be raised.
     *
     * Note, this feature requires that the user client accepts cookie. Also, to use this feature,
     * forms submitted via POST method must contain a hidden input whose name is specified by [[csrfParam]].
     * You may use [[\yii\helpers\Html::beginForm()]] to generate his hidden input.
     *
     * In JavaScript, you may get the values of [[csrfParam]] and [[csrfToken]] via `yii.getCsrfParam()` and
     * `yii.getCsrfToken()`, respectively. The [[\yii\web\YiiAsset]] asset must be registered.
     * You also need to include CSRF meta tags in your pages by using [[\yii\helpers\Html::csrfMetaTags()]].
     *
     * @see Controller::enableCsrfValidation
     * @see http://en.wikipedia.org/wiki/Cross-site_request_forgery
     */
    public $enableCsrfValidation = true;

    /**
     * @var string the name of the token used to prevent CSRF. Defaults to '_csrf'.
     * This property is used only when [[enableCsrfValidation]] is true.
     */
    public $csrfParam = '_csrf';
    /**
     * @var array the configuration for creating the CSRF [[Cookie|cookie]]. This property is used only when
     * both [[enableCsrfValidation]] and [[enableCsrfCookie]] are true.
     */
    public $csrfCookie = ['httpOnly' => true];
    /**
     * @var bool whether to use cookie to persist CSRF token. If false, CSRF token will be stored
     * in session under the name of [[csrfParam]]. Note that while storing CSRF tokens in session increases
     * security, it requires starting a session for every page, which will degrade your site performance.
     */
    public $enableCsrfCookie = true;
    /**
     * @var bool whether cookies should be validated to ensure they are not tampered. Defaults to true.
     */
    public $enableCookieValidation = true;
    /**
     * @var string a secret key used for cookie validation. This property must be set if [[enableCookieValidation]] is true.
     */
    public $cookieValidationKey;
    /**
     * @var string the name of the POST parameter that is used to indicate if a request is a PUT, PATCH or DELETE
     * request tunneled through POST. Defaults to '_method'.
     * @see getMethod()
     * @see getBodyParams()
     */
    public $methodParam = '_method';

    private $_hostInfo;
    
    private $_hostName;

    private $_scheme;

    /**
     * Get scheme
     * @throws \Exception
     */
    public function getScheme()
    {
        if ($this->_scheme === null) {
            $serverPort = $this->server[self::SERVER_SERVER_PORT];
            $portConfig = Server::$instance->getPortManager()->getPortConfig($serverPort);
            if ($portConfig->isOpenHttpProtocol()) {
                if ($portConfig->isEnableSsl()) {
                    $scheme = "https";
                } else {
                    $scheme = "http";
                }
                $this->_scheme = $scheme;
            } elseif ($portConfig->isOpenWebsocketProtocol()) {
                if ($portConfig->isEnableSsl()) {
                    $scheme = "wss";
                } else {
                    $scheme = "ws";
                }
                $this->_scheme = $scheme;
            }

        }
        return $this->_scheme;
    }

    /**
     * @param mixed $scheme
     */
    public function setScheme($scheme): void
    {
        $this->_scheme = $scheme;
    }



    /**
     * Returns the schema and host part of the current request URL.
     *
     * The returned URL does not have an ending slash.
     *
     * By default this value is based on the user request information. This method will
     * return the value of `$_SERVER['HTTP_HOST']` if it is available or `$_SERVER['SERVER_NAME']` if not.
     * You may want to check out the [PHP documentation](https://secure.php.net/manual/en/reserved.variables.server.php)
     * for more information on these variables.
     *
     * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
     *
     * > Warning: Dependent on the server configuration this information may not be
     * > reliable and [may be faked by the user sending the HTTP request](https://www.acunetix.com/vulnerabilities/web/host-header-attack).
     * > If the webserver is configured to serve the same site independent of the value of
     * > the `Host` header, this value is not reliable. In such situations you should either
     * > fix your webserver configuration or explicitly set the value by setting the [[setHostInfo()|hostInfo]] property.
     * > If you don't have access to the server configuration, you can setup [[\yii\filters\HostControl]] filter at
     * > application level in order to protect against such kind of attack.
     *
     * @property string|null schema and hostname part (with port number if needed) of the request URL
     * (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set.
     * See [[getHostInfo()]] for security related notes on this property.
     * @return string|null schema and hostname part (with port number if needed) of the request URL
     * (e.g. `http://www.yiiframework.com`), null if can't be obtained from `$_SERVER` and wasn't set.
     * @see setHostInfo()
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $http = $this->getScheme();

            if ($this->getHeader('X-Forwarded-Host')) {
                $this->_hostInfo = $http . '://' . trim(explode(',', $this->getHeader('X-Forwarded-Host'))[0]);
            } elseif ($this->getHeader('Host')) {
                $this->_hostInfo = $http . '://' . $this->getHeader('Host')[0];
            }
        }

        return $this->_hostInfo;
    }

    /**
     * Sets the schema and host part of the application URL.
     * This setter is provided in case the schema and hostname cannot be determined
     * on certain Web servers.
     * @param string|null $value the schema and host part of the application URL. The trailing slashes will be removed.
     * @see getHostInfo() for security related notes on this property.
     */
    public function setHostInfo($value)
    {
        $this->_hostName = null;
        $this->_hostInfo = $value === null ? null : rtrim($value, '/');
    }

    private $_csrfToken;

    /**
     * Returns the token used to perform CSRF validation.
     *
     * This token is generated in a way to prevent [BREACH attacks](http://breachattack.com/). It may be passed
     * along via a hidden field of an HTML form or an HTTP header value to support CSRF validation.
     * @param bool $regenerate whether to regenerate CSRF token. When this parameter is true, each time
     * this method is called, a new CSRF token will be generated and persisted (in session or cookie).
     * @return string the token used to perform CSRF validation.
     */
    public function getCsrfToken($regenerate = false)
    {
        if ($this->_csrfToken === null || $regenerate) {
            if ($regenerate || ($token = $this->loadCsrfToken()) === null) {
                $token = $this->generateCsrfToken();
            }
            $this->_csrfToken = Yii::$app->security->maskToken($token);
        }

        return $this->_csrfToken;
    }

    /**
     * Loads the CSRF token from cookie or session.
     * @return string the CSRF token loaded from cookie or session. Null is returned if the cookie or session
     * does not have CSRF token.
     */
    protected function loadCsrfToken()
    {
        if ($this->enableCsrfCookie) {
            return $this->cookie($this->csrfParam);
        }
        return Yii::$app->getSession()->getAttribute($this->csrfParam);
    }

    /**
     * Generates an unmasked random token used to perform CSRF validation.
     * @return string the random token for CSRF validation.
     */
    protected function generateCsrfToken()
    {
        $token = Yii::$app->getSecurity()->generateRandomKey();
        if ($this->enableCsrfCookie) {
            Yii::$app->getResponse()->withCookie(new Cookie($this->csrfParam, $token));
        } else {
            Yii::$app->getSession()->setAttribute($this->csrfParam, $token);
        }
        return $token;
    }

    /**
     * @return string the CSRF token sent via [[CSRF_HEADER]] by browser. Null is returned if no such header is sent.
     */
    public function getCsrfTokenFromHeader()
    {
        return $this->getHeader(static::CSRF_HEADER)[0];
    }

    /**
     * Performs the CSRF validation.
     *
     * This method will validate the user-provided CSRF token by comparing it with the one stored in cookie or session.
     * This method is mainly called in [[Controller::beforeAction()]].
     *
     * Note that the method will NOT perform CSRF validation if [[enableCsrfValidation]] is false or the HTTP method
     * is among GET, HEAD or OPTIONS.
     *
     * @param string $clientSuppliedToken the user-provided CSRF token to be validated. If null, the token will be retrieved from
     * the [[csrfParam]] POST field or HTTP header.
     * This parameter is available since version 2.0.4.
     * @return bool whether CSRF token is valid. If [[enableCsrfValidation]] is false, this method will return true.
     */
    public function validateCsrfToken($clientSuppliedToken = null)
    {
        $method = $this->getMethod();
        // only validate CSRF token on non-"safe" methods http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.1.1
        if (!$this->enableCsrfValidation || in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $trueToken = $this->getCsrfToken();

        if ($clientSuppliedToken !== null) {
            return $this->validateCsrfTokenInternal($clientSuppliedToken, $trueToken);
        }

        return $this->validateCsrfTokenInternal($this->getBodyParam($this->csrfParam), $trueToken)
            || $this->validateCsrfTokenInternal($this->getCsrfTokenFromHeader(), $trueToken);
    }

    /**
     * Validates CSRF token
     *
     * @param string $clientSuppliedToken The masked client-supplied token.
     * @param string $trueToken The masked true token.
     * @return bool
     */
    private function validateCsrfTokenInternal($clientSuppliedToken, $trueToken)
    {
        if (!is_string($clientSuppliedToken)) {
            return false;
        }

        $security = Yii::$app->security;

        return $security->unmaskToken($clientSuppliedToken) === $security->unmaskToken($trueToken);
    }

}
