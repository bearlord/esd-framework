<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\GuzzleSaber;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\RedirectMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Swlib\Saber\Response;
use Swlib\SaberGM;

/**
 * Class SaberClient
 * @package ESD\GuzzleSaber
 */
class SaberClient implements ClientInterface
{

    private $config;

    public function __construct(array $config = [])
    {
        // Convert the base_uri to a UriInterface
        if (isset($config['base_uri'])) {
            $config['base_uri'] = Psr7\uri_for($config['base_uri']);
        }

        $this->configureDefaults($config);
    }

    /**
     * Configures the default options for a client.
     *
     * @param array $config
     */
    private function configureDefaults(array $config)
    {
        $defaults = [
            'allow_redirects' => RedirectMiddleware::$defaultSettings,
            'http_errors' => true,
            'verify' => false
        ];
        $this->config = $config + $defaults;
    }

    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array $options Request options to apply to the given request and to the transfer.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $config = ChangeOptions::change($options + $this->config);
        $saberRequest = SaberGM::psr($config);
        if ($request->getRequestTarget() != null) {
            $saberRequest->withRequestTarget($request->getRequestTarget());
        }
        if ($request->getBody() != null) {
            $saberRequest->withBody($request->getBody());
        }
        if ($request->getMethod() != null) {
            $saberRequest->withMethod($request->getMethod());
        }
        if ($request->getProtocolVersion() != null) {
            $saberRequest->withProtocolVersion($request->getProtocolVersion());
        }
        if ($request->getHeaders() != null) {
            $saberRequest->withHeaders($request->getHeaders());
        }
        if ($request->getUri() != null) {
            $saberRequest->withUri($this->buildUri($request->getUri(), $options), $request->hasHeader('Host'));
        }
        return $this->changeResponse($saberRequest->exec()->recv());
    }

    /**
     * Asynchronously send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array $options Request options to apply to the given request and to the transfer.
     *
     * @return void
     * @throws \Exception
     */
    public function sendAsync(RequestInterface $request, array $options = [])
    {
        throw new \Exception("暂不支持");
    }

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string $method HTTP method.
     * @param string|UriInterface $uri URI object or string.
     * @param array $options Request options to apply.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        $config = ChangeOptions::change($options + $this->config);
        if (is_string($uri)) {
            $config['uri'] = $uri;
            return $this->changeResponse(SaberGM::psr($config)->withMethod($method)
                ->exec()->recv());
        } else {
            return $this->changeResponse(SaberGM::psr($config)->withMethod($method)->withUri($uri)
                ->exec()->recv());
        }
    }

    /**
     * Create and send an asynchronous HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well. Use an array to provide a URL
     * template and additional variables to use in the URL template expansion.
     *
     * @param string $method HTTP method
     * @param string|UriInterface $uri URI object or string.
     * @param array $options Request options to apply.
     *
     * @return void
     * @throws \Exception
     */
    public function requestAsync($method, $uri, array $options = [])
    {
        throw new \Exception("暂不支持");
    }

    /**
     * Get a client configuration option.
     *
     * These options include default request options of the client, a "handler"
     * (if utilized by the concrete client), and a "base_uri" if utilized by
     * the concrete client.
     *
     * @param string|null $option The config option to retrieve.
     *
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return $option === null
            ? $this->config
            : (isset($this->config[$option]) ? $this->config[$option] : null);
    }


    /**
     * @param $uri
     * @param array $config
     * @return mixed
     */
    private function buildUri($uri, array $config)
    {
        // for BC we accept null which would otherwise fail in uri_for
        $uri = Psr7\uri_for($uri === null ? '' : $uri);

        if (isset($config['base_uri'])) {
            $uri = Psr7\UriResolver::resolve(Psr7\uri_for($config['base_uri']), $uri);
        }

        return $uri->getScheme() === '' && $uri->getHost() !== '' ? $uri->withScheme('http') : $uri;
    }

    /**
     * @param Response $response
     * @return Psr7\Response
     */
    public function changeResponse(Response $response): Psr7\Response
    {
        return new Psr7\Response($response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase());
    }
}