<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\LoadBalance;

/**
 * Class Node
 * @package ESD\LoadBalance
 */
class Node
{
    /**
     * @var string
     */
    public $schema;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $path;

    /**
     * @var int
     */
    public $weight;

    /**
     * Node constructor.
     * @param string $host
     * @param int $port
     * @param int $weight
     */
    public function __construct(string $schema, string $host = '127.0.0.1', int $port, ?string $path = null, int $weight = 0)
    {
        $this->setSchema($schema);
        $this->setHost($host);
        $this->setPort($port);
        $this->setPath($path);
        $this->setWeight($weight);
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     */
    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function normalizeSchema()
    {
        $defaultSchema = 'http';

        if (empty($this->schema)) {
            return $defaultSchema;
        }

        return $this->schema;
    }
}