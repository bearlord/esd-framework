<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

use ESD\Core\Server\Beans\Http\Cookie;
use ESD\Core\Server\Beans\Http\HttpStream;
use ESD\Core\Server\Beans\Http\MessageTrait;

/**
 * Class AbstractResponse
 * @package ESD\Core\Server\Beans
 */
abstract class AbstractResponse implements \Psr\Http\Message\ResponseInterface
{
    use MessageTrait;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @var bool
     */
    protected $isEnd = false;

    /**
     * @var int
     */
    protected $fd = 0;

    /**
     * Retrieve attributes derived from the request.
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     * @return mixed
     *@see getAttributes()
     */
    public function getAttribute(string $name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     * @return static
     *@see getAttributes()
     */
    public function withAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Gets the response status code.
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code         The 3-digit integer result code to set.
     * @param string|null $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus(int $code, ?string $reasonPhrase = ''): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getReasonPhrase(): string
    {
        return '';
    }

    /**
     * Return an instance with the specified charset content type.
     *
     * @param $charset
     * @return static
     * @throws \InvalidArgumentException
     */
    public function withCharset($charset): self
    {
        return $this->withAddedHeader('Content-Type', sprintf('charset=%s', $charset));
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return static
     */
    public function setCharset(string $charset): AbstractResponse
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Return an instance with specified cookies.
     *
     * @param Cookie $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie): AbstractResponse
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function withContent(string $content = ''): self
    {
        if($content == null || !is_string($content)) {
            $content = '';
        }
        $this->stream = new HttpStream($content);
        return $this;
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function appendBody(?string $content): self
    {
        if($content == null) {
            $content = '';
        }
        if(!$this->stream) {
            $this->stream = new HttpStream('');
        }
        $this->stream->write($content);
        return $this;
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function append(?string $content): self
    {
        if($content == null) {
            $content = '';
        }
        if(!$this->stream) {
            $this->stream = new HttpStream('');
        }
        $this->stream->write($content);
        return $this;
    }

    /**
     * Create a new object for use with detach
     * @param $fd
     * @return static
     */
    abstract public static function create($fd): self;

    /**
     * load
     * @param null $realObject
     * @return mixed
     */
    abstract public function load($realObject = null);

    /**
     * End
     * @return mixed
     */
    abstract public function end();

    /**
     * Detach the response object. After using this method, the $response object will not end automatically when it is destroyed.
     * It is used with Http \Response::create and Server::send.
     */
    abstract public function detach();

    /**
     * Is end
     * @return bool
     */
    abstract public function isEnd(): bool;

    /**
     * Send http redirect. Calling this method will automatically send and end the response.
     * @param string $url
     * @param int $http_code
     */
    abstract public function redirect(string $url, int $http_code = 302);
}
