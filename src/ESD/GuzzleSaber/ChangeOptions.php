<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/25
 * Time: 11:12
 */

namespace ESD\GuzzleSaber;


use GuzzleHttp\Cookie\CookieJarInterface;
use Swlib\Http\ContentType;
use Swlib\Http\Exception\HttpExceptionMask;
use Swlib\Saber\Request;

class ChangeOptions
{
    public static function change($options)
    {
        $saberConfig = [];
        //base_uri
        $base_uri = $options['base_uri'] ?? null;
        if ($base_uri != null) {
            $saberConfig['base_uri'] = $base_uri;
        }
        //描述请求的重定向行为
        $allow_redirects = $options['allow_redirects'] ?? null;
        if ($allow_redirects != null) {
            $saberConfig['redirect'] = $allow_redirects['max'] ?? null;
        }
        //传入HTTP认证参数的数组来使用请求，该数组索引[0]为用户名、索引[1]为密码，索引[2]为可选的内置认证类型。传入 null 进入请求认证。
        $auth = $options['auth'] ?? null;
        if ($auth != null) {
            $saberConfig['before']['auth'] = function (Request $request) use ($auth) {
                $request->withBasicAuth($auth[0], $auth[1]);
            };
        }
        //body 选项用来控制一个请求(比如：PUT, POST, PATCH)的主体部分。
        $body = $options['body'] ?? null;
        if ($body != null) {
            $saberConfig['data'] = $body;
        }
        //cert 设置成指定PEM格式认证文件的路径的字符串，如果需要密码，需要设置成一个数组，其中PEM文件在第一个元素，密码在第二个元素。
        $cert = $options['cert'] ?? null;
        if ($cert != null) {
            if (is_string($cert)) {
                $saberConfig['cafile'] = $cert;
            } elseif (is_array($cert)) {
                $saberConfig['cafile'] = $cert[0];
            }
        }
        //cookies 声明是否在请求中使用cookie，或者要使用的cookie jar，或者要发送的cookie。
        $cookies = $options['cookies'] ?? null;
        if ($cookies != null) {
            if ($cookies instanceof CookieJarInterface) {
                $saberConfig['cookies'] = $cookies->toArray();
            } else {
                $saberConfig['cookies'] = $cookies;
            }
        }
        //form_params 用来发送一个 application/x-www-form-urlencoded POST请求.
        $form_params = $options['form_params'] ?? null;
        if ($form_params != null) {
            $saberConfig['content_type'] = ContentType::URLENCODE;
            $saberConfig['data'] = $form_params;
        }
        //headers 要添加到请求的报文头的关联数组，每个键名是header的名称，每个键值是一个字符串或包含代表头字段字符串的数组。
        $headers = $options['headers'] ?? null;
        if ($headers != null) {
            $saberConfig['headers'] = $headers;
        }
        //http_errors 设置成 false 来禁用HTTP协议抛出的异常(如 4xx 和 5xx 响应)，默认情况下HTPP协议出错时会抛出异常。
        $http_errors = $options['http_errors'] ?? null;
        if ($http_errors === false) {
            $saberConfig['exception_report'] = HttpExceptionMask::E_NONE;
        }
        //json 选项用来轻松将JSON数据当成主体上传， 如果没有设置Content-Type头信息的时候会设置成 application/json 。
        $json = $options['json'] ?? null;
        if ($json != null) {
            $saberConfig['content_type'] = ContentType::JSON;
            $saberConfig['data'] = $json;
        }
        //multipart 设置请求的主体为 multipart/form-data 表单。
        $multipart = $options['multipart'] ?? null;
        if ($multipart != null) {
            $saberConfig['content_type'] = ContentType::MULTIPART;
            $saberConfig['data'] = $multipart;
        }
        //query 要添加到请求的查询字符串的关联数组或查询字符串。
        $query = $options['query'] ?? null;
        if ($query != null) {
            $saberConfig['uri_query'] = $query;
        }
        //verify 请求时验证SSL证书行为。
        $verify = $options['verify'] ?? null;
        if ($verify != null) {
            if (is_bool($verify)) {
                $saberConfig['ssl_verify_peer'] = $verify;
            } elseif (is_string($verify)) {
                $saberConfig['ssl_verify_peer'] = true;
                $saberConfig['cafile'] = $verify;
            }
        }
        //timeout 请求超时的秒数。使用 0 无限期的等待(默认行为)。
        $timeout = $options['timeout'] ?? null;
        if ($timeout != null) {
            if ($timeout == 0) $timeout = -1;
            $saberConfig['timeout'] = $timeout;
        }
        return $saberConfig;
    }
}