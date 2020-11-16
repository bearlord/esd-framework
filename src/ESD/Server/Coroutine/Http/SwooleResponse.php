<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Server\Coroutine\Http;

use ESD\Core\Exception;
use ESD\Core\Server\Beans\Http\Cookie;
use ESD\Core\Server\Beans\Response;

/**
 * Class SwooleResponse
 * @package ESD\Server\Coroutine\Http
 */
class SwooleResponse extends Response
{

    /**
     * @var \Swoole\Http\Response
     */
    protected $swooleResponse;

    /**
     * Load object
     * @param null $realObject
     * @throws Exception
     */
    public function load($realObject = null)
    {
        if (! $realObject instanceof \Swoole\Http\Response) {
            throw new Exception("object must be instance of Swoole\\Response");
        }

        $this->swooleResponse = $realObject;
    }

    /**
     * Send data and end
     */
    public function end()
    {
        if ($this->isEnd) {
            return;
        }

        /**
         * Headers
         */
        // Write Headers to swoole response
        foreach ($this->headers as $key => $value) {
            $this->swooleResponse->header($key, implode(';', $value));
        }

        /**
         * Cookies
         */
        foreach ((array)$this->cookies as $domain => $paths) {
            foreach ($paths ?? [] as $path => $item) {
                foreach ($item ?? [] as $name => $cookie) {
                    if ($cookie instanceof Cookie) {
                        $this->swooleResponse->cookie($cookie->getName(), $cookie->getValue() ? : 1, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
                    }
                }
            }
        }

        /**
         * Status code
         */
        $this->swooleResponse->status($this->statusCode);

        /**
         * Body
         */
        $this->swooleResponse->end($this->getBody()->getContents());

        $this->isEnd = true;
    }

    /**
     * Separate the response object. After using this method, the $response object will not automatically end when it is destroyed.
     * It is used in conjunction with Http\Response::create and Server::send.
     */
    public function detach()
    {
        $this->swooleResponse->detach();
    }

    /**
     * Is end
     * @return bool
     */
    public function isEnd()
    {
        return $this->isEnd;
    }

    /**
     * Send Http redirect. Calling this method will automatically end send and end the response.
     * @param string $url
     * @param int $http_code
     */
    public function redirect(string $url, int $http_code = 302)
    {
        $this->swooleResponse->redirect($url, $http_code);
        $this->isEnd = true;
    }

    /**
     * Create a new object and use it with detach
     * @param $fd
     * @return static
     * @throws Exception
     */
    public static function create($fd)
    {
        $obj = new SwooleResponse();
        $obj->load(Response::create($fd));
        return $obj;
    }
}