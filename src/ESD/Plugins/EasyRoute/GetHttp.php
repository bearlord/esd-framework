<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute;

use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\ParamException;

/**
 * Trait GetHttp
 * @package ESD\Plugins\EasyRoute
 */
trait GetHttp
{
    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return getDeepContextValueByClassName(Request::class);
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return getDeepContextValueByClassName(Response::class);
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed|string
     */
    public function query($key = null, $default = null)
    {
        return $this->getRequest()->query($key, $default);
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed|string
     */
    public function post($key = null, $default = null)
    {
        return $this->getRequest()->post($key, $default);
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed|string
     */
    public function input($key = null, $default = null)
    {
        return $this->getRequest()->input($key, $default);
    }

    /**
     * @param $key
     * @return array|mixed
     * @throws ParamException
     */
    public function postRequire($key)
    {
        return $this->paramsRequire($key, 'post');
    }

    /**
     * @param $key
     * @return array|mixed
     * @throws ParamException
     */
    public function queryRequire($key)
    {
        return $this->paramsRequire($key, 'query');
    }

    /**
     * @param $key
     * @return array|mixed
     * @throws ParamException
     */
    public function inputRequire($key)
    {
        return $this->paramsRequire($key, 'input');
    }

    /**
     * @return mixed
     * @throws RouteException
     */
    public function postRawJson()
    {
        if (!$json = json_decode($this->getRequest()->getBody()->getContents(), true)) {
            $this->warning('postRawJson errror, raw:' . $this->getRequest()->getBody()->getContents());
            throw new RouteException('RawJson Format error');
        }
        return $json;
    }

    /**
     * @return \SimpleXMLElement
     * @throws RouteException
     */
    public function postRawXml()
    {
        $raw = $this->getRequest()->getBody()->getContents();
        if (!$xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS)) {
            $this->warning('RequestRawXml errror, raw:' . $this->getRequest()->getBody()->getContents());
            throw new RouteException('RawXml Format error');
        }
        return $xml;
    }

    /**
     * @param $key
     * @param $method
     * @return array|mixed
     * @throws ParamException
     */
    private function paramsRequire($key, $method)
    {
        if (is_array($key)) {
            $result = [];
            foreach ($key as $k) {
                $ret = call_user_func([$this->getRequest(), $method], $k, null);
                if ($ret == null) {
                    throw new ParamException("require params $k");
                }
                $result[$k] = $ret;
            }
            return $result;
        } else {
            $result = call_user_func([$this->getRequest(), $method], $key, null);
            if ($result == null) {
                throw new ParamException("require params $key");
            }
            return $result;
        }
    }

}