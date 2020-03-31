<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\GuzzleSaber;

use GuzzleHttp\Cookie\CookieJarInterface;
use Swlib\Http\ContentType;
use Swlib\Http\Exception\HttpExceptionMask;
use Swlib\Saber\Request;

/**
 * Class ChangeOptions
 * @package ESD\GuzzleSaber
 */
class ChangeOptions
{
    public static function change($options)
    {
        $saberConfig = [];

        //base uri
        $base_uri = $options['base_uri'] ?? null;
        if ($base_uri != null) {
            $saberConfig['base_uri'] = $base_uri;
        }

        //Describe request redirection behavior
        $allow_redirects = $options['allow_redirects'] ?? null;
        if ($allow_redirects != null) {
            $saberConfig['redirect'] = $allow_redirects['max'] ?? null;
        }

        //Pass in an array of HTTP authentication parameters to use the request. The array index [0] is the username,
        // index[1] is the password, and index[2] is an optional built-in authentication type.
        //Pass in null to request authentication.
        $auth = $options['auth'] ?? null;
        if ($auth != null) {
            $saberConfig['before']['auth'] = function (Request $request) use ($auth) {
                $request->withBasicAuth($auth[0], $auth[1]);
            };
        }

        //The body option is used to control the body part of a request (for example: PUT, POST, PATCH).
        $body = $options['body'] ?? null;
        if ($body != null) {
            $saberConfig['data'] = $body;
        }

        //Cert is set to a string specifying the path of the PEM format authentication file.
        //If a password is required, it needs to be set to an array, where the PEM file
        //is on the first element and the password is on the second element.
        $cert = $options['cert'] ?? null;
        if ($cert != null) {
            if (is_string($cert)) {
                $saberConfig['cafile'] = $cert;
            } elseif (is_array($cert)) {
                $saberConfig['cafile'] = $cert[0];
            }
        }

        //Cookies Specifies whether cookies are used in the request,
        //or the cookie jar to be used, or the cookie to be sent.
        $cookies = $options['cookies'] ?? null;
        if ($cookies != null) {
            if ($cookies instanceof CookieJarInterface) {
                $saberConfig['cookies'] = $cookies->toArray();
            } else {
                $saberConfig['cookies'] = $cookies;
            }
        }

        //Form_params is used to send an application/x-www-form-urlencoded POST request.
        $form_params = $options['form_params'] ?? null;
        if ($form_params != null) {
            $saberConfig['content_type'] = ContentType::URLENCODE;
            $saberConfig['data'] = $form_params;
        }

        //Headers An associative array to add to the header of the requested message.
        //Each key is the name of the header, and each key is a string or an array
        //containing a string representing the header fields.
        $headers = $options['headers'] ?? null;
        if ($headers != null) {
            $saberConfig['headers'] = $headers;
        }


        //Http errors is set to false to disable exceptions thrown by the HTTP protocol (such as 4xx and 5xx responses).
        //By default, exceptions are thrown when the HTPP protocol fails.
        $http_errors = $options['http_errors'] ?? null;
        if ($http_errors === false) {
            $saberConfig['exception_report'] = HttpExceptionMask::E_NONE;
        }

        //The Json option is used to easily upload JSON data as the body.
        //If the Content-Type header is not set, it will be set to application/json.
        $json = $options['json'] ?? null;
        if ($json != null) {
            $saberConfig['content_type'] = ContentType::JSON;
            $saberConfig['data'] = $json;
        }

        //Multipart sets the body of the request to a multipart / form-data form.
        $multipart = $options['multipart'] ?? null;
        if ($multipart != null) {
            $saberConfig['content_type'] = ContentType::MULTIPART;
            $saberConfig['data'] = $multipart;
        }

        //Query An associative array or query string to be added to the requested query string.
        $query = $options['query'] ?? null;
        if ($query != null) {
            $saberConfig['uri_query'] = $query;
        }

        //Verify Verify SSL certificate behavior when requested.
        $verify = $options['verify'] ?? null;
        if ($verify != null) {
            if (is_bool($verify)) {
                $saberConfig['ssl_verify_peer'] = $verify;
            } elseif (is_string($verify)) {
                $saberConfig['ssl_verify_peer'] = true;
                $saberConfig['cafile'] = $verify;
            }
        }

        //Timeout The number of seconds that the request timed out. Use 0 to wait indefinitely (default behavior).
        $timeout = $options['timeout'] ?? null;
        if ($timeout != null) {
            if ($timeout == 0) $timeout = -1;
            $saberConfig['timeout'] = $timeout;
        }
        return $saberConfig;
    }
}