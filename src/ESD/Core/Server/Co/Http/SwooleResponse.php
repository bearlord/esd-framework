<?php


namespace ESD\Server\Co\Http;


use ESD\Core\Exception;
use ESD\Core\Server\Beans\Http\Cookie;
use ESD\Core\Server\Beans\Response;

class SwooleResponse extends Response
{

    /**
     * @var \Swoole\Http\Response
     */
    protected $swooleResponse;

    /**
     * 加载
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
     * 发送数据
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
     * 分离响应对象。使用此方法后，$response对象销毁时不会自动end，与Http\Response::create和Server::send配合使用。
     */
    public function detach()
    {
        $this->swooleResponse->detach();
    }

    /**
     * 是否已经发送
     * @return bool
     */
    public function isEnd()
    {
        return $this->isEnd;
    }

    /**
     * 发送Http跳转。调用此方法会自动end发送并结束响应。
     * @param string $url
     * @param int $http_code
     */
    public function redirect(string $url, int $http_code = 302)
    {
        $this->swooleResponse->redirect($url, $http_code);
        $this->isEnd = true;
    }

    /**
     * 创建一个新对象，配合detach使用
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