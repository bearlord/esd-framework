<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

use ESD\Core\ParamException;
use ESD\Core\Exception;
use ESD\Core\Server\Server;

/**
 * Class Request
 * @package ESD\Core\Server\Beans
 */
abstract class Request extends AbstractRequest
{
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

}
